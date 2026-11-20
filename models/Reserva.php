<?php
/**
 * ---------------------------------------------------------------------
 * models/Reserva.php
 * ---------------------------------------------------------------------
 * Acceso a la tabla `reservas`. Un miembro solo puede tener una
 * reserva por clase (índice UNIQUE). Si cancela y vuelve a reservar,
 * se reactiva la misma fila.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Reserva extends Model
{
    protected static string $tabla = 'reservas';

    /** Reserva de un miembro en una clase (o null). */
    public static function deMiembroEnClase(int $miembro, int $clase): ?array
    {
        return self::query('SELECT * FROM reservas WHERE id_miembro = ? AND id_clase = ?', [$miembro, $clase])->fetch() ?: null;
    }

    /**
     * Reserva un cupo dentro de una TRANSACCIÓN: bloquea la fila de la
     * clase (FOR UPDATE) para que dos miembros no tomen el último cupo
     * al mismo tiempo. Devuelve null si todo salió bien o el mensaje de
     * error.
     */
    public static function reservar(int $miembro, int $clase): ?string
    {
        $db = self::db();
        $db->beginTransaction();
        try {
            $c = self::query("SELECT * FROM clases WHERE id = ? FOR UPDATE", [$clase])->fetch();
            if (!$c || $c['estado'] !== 'Programada') {
                $db->rollBack();
                return 'La clase no está disponible.';
            }
            if (strtotime($c['fecha_hora']) < time()) {
                $db->rollBack();
                return 'La clase ya comenzó.';
            }
            $existente = self::deMiembroEnClase($miembro, $clase);
            if ($existente && $existente['estado'] !== 'Cancelada') {
                $db->rollBack();
                return 'Ya tienes una reserva en esta clase.';
            }
            $ocupados = (int) self::query("SELECT COUNT(*) FROM reservas WHERE id_clase = ? AND estado <> 'Cancelada'", [$clase])->fetchColumn();
            if ($ocupados >= (int) $c['cupo']) {
                $db->rollBack();
                return 'No quedan cupos en esta clase.';
            }
            if ($existente) {
                self::update((int) $existente['id'], ['estado' => 'Reservada', 'fecha_reserva' => date('Y-m-d H:i:s')]);
            } else {
                self::create(['id_miembro' => $miembro, 'id_clase' => $clase]);
            }
            $db->commit();
            return null;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Reservas de un miembro con datos de la clase (próximas o historial). */
    public static function deMiembro(int $miembro, bool $proximas): array
    {
        $sql = "SELECT r.*, c.nombre, c.tipo, c.fecha_hora, c.duracion_min, c.estado AS estado_clase,
                       CONCAT(u.nombre, ' ', u.apellido) AS instructor, i.nombre AS instalacion
                  FROM reservas r
                  JOIN clases c        ON c.id = r.id_clase
                  JOIN usuarios u      ON u.id = c.id_instructor
                  JOIN instalaciones i ON i.id = c.id_instalacion
                 WHERE r.id_miembro = ? AND c.fecha_hora " . ($proximas ? '>= NOW()' : '< NOW()') . '
                 ORDER BY c.fecha_hora ' . ($proximas ? 'ASC' : 'DESC');
        return self::query($sql, [$miembro])->fetchAll();
    }

    /** Miembros inscritos en una clase (para tomar asistencia). */
    public static function deClase(int $clase): array
    {
        $sql = "SELECT r.*, u.nombre, u.apellido, u.correo, u.telefono
                  FROM reservas r JOIN usuarios u ON u.id = r.id_miembro
                 WHERE r.id_clase = ? AND r.estado <> 'Cancelada'
                 ORDER BY u.nombre";
        return self::query($sql, [$clase])->fetchAll();
    }

    /** Todas las reservas (vista del administrador), opcionalmente filtradas por estado. */
    public static function todas(string $estado = ''): array
    {
        $sql = "SELECT r.*, c.nombre AS clase, c.fecha_hora,
                       CONCAT(m.nombre, ' ', m.apellido) AS miembro,
                       CONCAT(u.nombre, ' ', u.apellido) AS instructor
                  FROM reservas r
                  JOIN clases c   ON c.id = r.id_clase
                  JOIN usuarios m ON m.id = r.id_miembro
                  JOIN usuarios u ON u.id = c.id_instructor";
        $p = [];
        if ($estado !== '') {
            $sql .= ' WHERE r.estado = ?';
            $p[] = $estado;
        }
        return self::query($sql . ' ORDER BY c.fecha_hora DESC LIMIT 300', $p)->fetchAll();
    }

    /** Total de clases a las que asistió un miembro. */
    public static function asistencias(int $miembro): int
    {
        return (int) self::query("SELECT COUNT(*) FROM reservas WHERE id_miembro = ? AND estado = 'Asistio'", [$miembro])->fetchColumn();
    }

    /** Reservas activas hechas hoy o futuras (panel de administrador). */
    public static function contarActivas(): int
    {
        return (int) self::query("SELECT COUNT(*) FROM reservas r JOIN clases c ON c.id = r.id_clase
                                   WHERE r.estado = 'Reservada' AND c.fecha_hora >= NOW()")->fetchColumn();
    }
}

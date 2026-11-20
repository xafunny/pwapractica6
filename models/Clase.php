<?php
/**
 * ---------------------------------------------------------------------
 * models/Clase.php
 * ---------------------------------------------------------------------
 * Acceso a la tabla `clases`. Las consultas incluyen el nombre del
 * instructor, la instalación y cuántos cupos están ocupados
 * (reservas que no están canceladas).
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Clase extends Model
{
    protected static string $tabla = 'clases';

    /** Columnas comunes de los listados de clases. */
    private const COLUMNAS = "c.*,
               CONCAT(u.nombre, ' ', u.apellido) AS instructor,
               i.nombre AS instalacion, i.capacidad AS capacidad_sala,
               (SELECT COUNT(*) FROM reservas r WHERE r.id_clase = c.id AND r.estado <> 'Cancelada') AS ocupados";

    /** Tablas unidas (JOIN) comunes de los listados de clases. */
    private const DESDE = ' FROM clases c
          JOIN usuarios u      ON u.id = c.id_instructor
          JOIN instalaciones i ON i.id = c.id_instalacion';

    /**
     * Listado para administrador / instructor.
     *   $instructor -> filtra las clases de un instructor
     *   $periodo    -> 'proximas' | 'pasadas' | 'todas'
     */
    public static function listar(?int $instructor = null, string $periodo = 'proximas'): array
    {
        $sql = 'SELECT ' . self::COLUMNAS . self::DESDE . ' WHERE 1=1';
        $p = [];
        if ($instructor) {
            $sql .= ' AND c.id_instructor = ?';
            $p[] = $instructor;
        }
        if ($periodo === 'proximas') $sql .= ' AND c.fecha_hora >= NOW()';
        if ($periodo === 'pasadas')  $sql .= ' AND c.fecha_hora < NOW()';
        $sql .= $periodo === 'pasadas' ? ' ORDER BY c.fecha_hora DESC' : ' ORDER BY c.fecha_hora';
        return self::query($sql, $p)->fetchAll();
    }

    /**
     * Clases futuras y programadas para que el miembro reserve.
     * La columna mi_reserva trae el estado de SU reserva (o null).
     */
    public static function disponibles(int $miembro, string $tipo = ''): array
    {
        $sql = 'SELECT ' . self::COLUMNAS . ',
                   (SELECT r2.estado FROM reservas r2 WHERE r2.id_clase = c.id AND r2.id_miembro = ?) AS mi_reserva'
             . self::DESDE
             . " WHERE c.fecha_hora >= NOW() AND c.estado = 'Programada'";
        $p = [$miembro];
        if ($tipo !== '') {
            $sql .= ' AND c.tipo = ?';
            $p[] = $tipo;
        }
        return self::query($sql . ' ORDER BY c.fecha_hora', $p)->fetchAll();
    }

    /** Una clase con sus datos completos. */
    public static function detalle(int $id): ?array
    {
        return self::query('SELECT ' . self::COLUMNAS . self::DESDE . ' WHERE c.id = ?', [$id])->fetch() ?: null;
    }

    /** Tipos de clase distintos (para el filtro del miembro). */
    public static function tipos(): array
    {
        return self::query('SELECT DISTINCT tipo FROM clases ORDER BY tipo')->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * ¿La instalación ya está ocupada en ese horario? Compara el rango
     * [inicio, inicio + duración) con las demás clases programadas.
     */
    public static function choqueHorario(int $instalacion, string $inicio, int $duracion, int $exceptoId = 0): bool
    {
        $sql = "SELECT 1 FROM clases
                 WHERE id_instalacion = ? AND id <> ? AND estado = 'Programada'
                   AND fecha_hora < DATE_ADD(?, INTERVAL ? MINUTE)
                   AND DATE_ADD(fecha_hora, INTERVAL duracion_min MINUTE) > ?
                 LIMIT 1";
        return (bool) self::query($sql, [$instalacion, $exceptoId, $inicio, $duracion, $inicio])->fetch();
    }

    /** Número de clases futuras programadas (opcionalmente de un instructor). */
    public static function contarProximas(?int $instructor = null): int
    {
        $sql = "SELECT COUNT(*) FROM clases WHERE fecha_hora >= NOW() AND estado = 'Programada'";
        $p = [];
        if ($instructor) {
            $sql .= ' AND id_instructor = ?';
            $p[] = $instructor;
        }
        return (int) self::query($sql, $p)->fetchColumn();
    }
}

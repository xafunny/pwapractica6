<?php
/**
 * ---------------------------------------------------------------------
 * models/Usuario.php
 * ---------------------------------------------------------------------
 * Acceso a la tabla `usuarios` (administradores, instructores y
 * miembros). Las contraseñas se guardan con password_hash() y se
 * validan con password_verify().
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Usuario extends Model
{
    protected static string $tabla = 'usuarios';

    /** Busca por correo incluyendo el nombre del rol (para el login). */
    public static function porCorreo(string $correo): ?array
    {
        $sql = 'SELECT u.*, r.nombre AS rol
                  FROM usuarios u JOIN roles r ON r.id = u.id_rol
                 WHERE u.correo = ?';
        return self::query($sql, [$correo])->fetch() ?: null;
    }

    /** ¿Ya existe ese correo? (opcionalmente ignorando un id al editar). */
    public static function correoExiste(string $correo, int $exceptoId = 0): bool
    {
        return (bool) self::query('SELECT 1 FROM usuarios WHERE correo = ? AND id <> ?', [$correo, $exceptoId])->fetch();
    }

    /**
     * Lista usuarios con el nombre de su rol, filtrando opcionalmente
     * por rol y por texto (nombre, apellido o correo).
     */
    public static function listar(?int $rol = null, string $buscar = ''): array
    {
        $sql = 'SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id = u.id_rol WHERE 1=1';
        $p = [];
        if ($rol) {
            $sql .= ' AND u.id_rol = ?';
            $p[] = $rol;
        }
        if ($buscar !== '') {
            $sql .= ' AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.correo LIKE ?)';
            array_push($p, "%$buscar%", "%$buscar%", "%$buscar%");
        }
        return self::query($sql . ' ORDER BY u.id_rol, u.nombre', $p)->fetchAll();
    }

    /** Lista de instructores activos (para los selects de clases). */
    public static function instructores(): array
    {
        return self::query('SELECT id, nombre, apellido, especialidad FROM usuarios
                             WHERE id_rol = ? AND activo = 1 ORDER BY nombre', [Auth::INSTRUCTOR])->fetchAll();
    }

    /**
     * Miembros con datos resumidos para el panel del instructor:
     * nº de ejercicios en su rutina y fecha de su última medición.
     */
    public static function miembrosResumen(string $buscar = ''): array
    {
        $sql = 'SELECT u.*,
                       (SELECT COUNT(*) FROM ejercicios_rutina e WHERE e.id_miembro = u.id) AS total_ejercicios,
                       (SELECT MAX(fecha) FROM mediciones m WHERE m.id_miembro = u.id)     AS ultima_medicion
                  FROM usuarios u
                 WHERE u.id_rol = ?';
        $p = [Auth::MIEMBRO];
        if ($buscar !== '') {
            $sql .= ' AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.correo LIKE ?)';
            array_push($p, "%$buscar%", "%$buscar%", "%$buscar%");
        }
        return self::query($sql . ' ORDER BY u.nombre', $p)->fetchAll();
    }

    /** Cantidad de usuarios por rol (para el panel de administrador). */
    public static function contarPorRol(): array
    {
        $filas = self::query('SELECT id_rol, COUNT(*) total FROM usuarios GROUP BY id_rol')->fetchAll();
        return array_column($filas, 'total', 'id_rol');
    }

    /** Cambia la contraseña guardando su hash. */
    public static function cambiarContrasena(int $id, string $nueva): void
    {
        self::update($id, ['contrasena' => password_hash($nueva, PASSWORD_DEFAULT)]);
    }

    /** ¿El usuario tiene clases o rutinas asociadas que impiden borrarlo? */
    public static function tieneClases(int $id): bool
    {
        return (bool) self::query('SELECT 1 FROM clases WHERE id_instructor = ? LIMIT 1', [$id])->fetch();
    }
}

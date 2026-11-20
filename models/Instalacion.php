<?php
/**
 * ---------------------------------------------------------------------
 * models/Instalacion.php
 * ---------------------------------------------------------------------
 * Acceso a la tabla `instalaciones` (salas del gimnasio).
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Instalacion extends Model
{
    protected static string $tabla = 'instalaciones';

    /** Instalaciones con la cantidad de clases futuras programadas en cada una. */
    public static function conUso(): array
    {
        return self::query("SELECT i.*,
                   (SELECT COUNT(*) FROM clases c
                     WHERE c.id_instalacion = i.id AND c.fecha_hora >= NOW() AND c.estado = 'Programada') AS clases_futuras
              FROM instalaciones i ORDER BY i.nombre")->fetchAll();
    }

    /** ¿Alguna clase usa esta instalación? (no se puede eliminar si es así). */
    public static function enUso(int $id): bool
    {
        return (bool) self::query('SELECT 1 FROM clases WHERE id_instalacion = ? LIMIT 1', [$id])->fetch();
    }
}

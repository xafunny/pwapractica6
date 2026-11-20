<?php
/**
 * ---------------------------------------------------------------------
 * models/Medicion.php
 * ---------------------------------------------------------------------
 * Acceso a la tabla `mediciones` (composición corporal del miembro).
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Medicion extends Model
{
    protected static string $tabla = 'mediciones';

    /** Mediciones del miembro ordenadas por fecha (más antigua primero). */
    public static function deMiembro(int $miembro): array
    {
        return self::query('SELECT * FROM mediciones WHERE id_miembro = ? ORDER BY fecha, id', [$miembro])->fetchAll();
    }

    /**
     * Calcula el IMC (índice de masa corporal) = peso / estatura(m)^2.
     * Devuelve null si falta la estatura.
     */
    public static function calcularImc(float $peso, ?float $estaturaCm): ?float
    {
        if (!$estaturaCm) return null;
        return round($peso / (($estaturaCm / 100) ** 2), 1);
    }
}

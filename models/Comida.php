<?php
/**
 * ---------------------------------------------------------------------
 * models/Comida.php
 * ---------------------------------------------------------------------
 * Acceso a la tabla `comidas` (plan de alimentación semanal).
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Comida extends Model
{
    protected static string $tabla = 'comidas';

    /**
     * Plan de comidas del miembro agrupado por día y momento:
     *   ['Lunes' => ['Desayuno' => [comida, ...], ...], ...]
     */
    public static function planSemanal(int $miembro): array
    {
        $plan = [];
        foreach (DIAS as $d) {
            $plan[$d] = array_fill_keys(MOMENTOS, []);
        }
        $filas = self::query('SELECT * FROM comidas WHERE id_miembro = ? ORDER BY id', [$miembro])->fetchAll();
        foreach ($filas as $f) {
            $plan[$f['dia']][$f['momento']][] = $f;
        }
        return $plan;
    }
}

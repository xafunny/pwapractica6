<?php
/**
 * ---------------------------------------------------------------------
 * models/Ejercicio.php
 * ---------------------------------------------------------------------
 * Acceso a `ejercicios_rutina` (rutina semanal de cada miembro) y a
 * `registros_entrenamiento` (cada vez que un ejercicio se marca como
 * hecho en una fecha). "Hecho esta semana" = existe un registro con
 * fecha >= lunes de la semana actual; así la semana se reinicia sola.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Ejercicio extends Model
{
    protected static string $tabla = 'ejercicios_rutina';

    /**
     * Rutina completa del miembro agrupada por día:
     *   ['Lunes' => [ejercicio, ...], 'Martes' => [...], ...]
     * Cada ejercicio trae 'hecho' = 1 si se completó esta semana.
     */
    public static function rutinaSemanal(int $miembro): array
    {
        $sql = 'SELECT e.*,
                       EXISTS(SELECT 1 FROM registros_entrenamiento r
                               WHERE r.id_ejercicio = e.id AND r.fecha >= ?) AS hecho
                  FROM ejercicios_rutina e
                 WHERE e.id_miembro = ?
                 ORDER BY FIELD(e.dia, ' . implode(',', array_map(fn($d) => "'$d'", DIAS)) . '), e.orden, e.id';
        $porDia = array_fill_keys(DIAS, []);
        foreach (self::query($sql, [inicio_semana(), $miembro])->fetchAll() as $fila) {
            $porDia[$fila['dia']][] = $fila;
        }
        return $porDia;
    }

    /** Siguiente número de orden dentro de un día. */
    public static function siguienteOrden(int $miembro, string $dia): int
    {
        return 1 + (int) self::query('SELECT COALESCE(MAX(orden),0) FROM ejercicios_rutina WHERE id_miembro = ? AND dia = ?', [$miembro, $dia])->fetchColumn();
    }

    /**
     * Marca/desmarca un ejercicio como hecho esta semana.
     * Devuelve true si quedó marcado.
     */
    public static function alternarHecho(int $id): bool
    {
        $hecho = self::query('SELECT id FROM registros_entrenamiento WHERE id_ejercicio = ? AND fecha >= ?', [$id, inicio_semana()])->fetch();
        if ($hecho) {
            self::query('DELETE FROM registros_entrenamiento WHERE id_ejercicio = ? AND fecha >= ?', [$id, inicio_semana()]);
            return false;
        }
        self::query('INSERT INTO registros_entrenamiento (id_ejercicio, fecha) VALUES (?, ?)', [$id, date('Y-m-d')]);
        return true;
    }

    /** Progreso semanal: [hechos, total] de toda la rutina del miembro. */
    public static function progresoSemana(int $miembro): array
    {
        $sql = 'SELECT COUNT(*) total,
                       SUM(EXISTS(SELECT 1 FROM registros_entrenamiento r WHERE r.id_ejercicio = e.id AND r.fecha >= ?)) hechos
                  FROM ejercicios_rutina e WHERE e.id_miembro = ?';
        $f = self::query($sql, [inicio_semana(), $miembro])->fetch();
        return [(int) $f['hechos'], (int) $f['total']];
    }

    /** Historial de entrenamientos (últimos registros) del miembro. */
    public static function historial(int $miembro, int $limite = 30): array
    {
        $sql = 'SELECT r.fecha, e.nombre, e.dia, e.series, e.repeticiones, e.peso
                  FROM registros_entrenamiento r
                  JOIN ejercicios_rutina e ON e.id = r.id_ejercicio
                 WHERE e.id_miembro = ?
                 ORDER BY r.fecha DESC, e.orden
                 LIMIT ' . (int) $limite;
        return self::query($sql, [$miembro])->fetchAll();
    }
}

<?php
/**
 * ---------------------------------------------------------------------
 * core/Controller.php
 * ---------------------------------------------------------------------
 * Clase base de los controladores. Ofrece:
 *   - view()          renderiza una vista dentro del layout (header/footer)
 *   - json()          responde JSON (para las peticiones fetch de JS)
 *   - soloRoles()     restringe la acción a ciertos roles
 *   - soloPost()      exige método POST + token CSRF válido
 *   - miembroObjetivo() resuelve sobre qué miembro se trabaja (rutina,
 *                     dieta, progreso): el propio miembro o el que elige
 *                     el instructor/administrador.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

abstract class Controller
{
    /**
     * Renderiza views/{$vista}.php. Las claves de $datos se convierten en
     * variables dentro de la vista (extract). Con $layout = true se
     * incluyen el encabezado (navbar) y el pie de página.
     */
    protected function view(string $vista, array $datos = [], bool $layout = true): void
    {
        extract($datos);
        $titulo = $datos['titulo'] ?? APP_NAME;
        if ($layout) require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/' . $vista . '.php';
        if ($layout) require __DIR__ . '/../views/layout/footer.php';
    }

    /** Envía una respuesta JSON y termina. */
    protected function json(array $datos, int $codigo = 200): never
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** ¿La petición viene de fetch() (espera JSON)? */
    protected function esAjax(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    /**
     * Permite continuar solo si hay sesión y el rol es uno de los
     * indicados. Sin roles => basta con estar autenticado.
     */
    protected function soloRoles(int ...$roles): void
    {
        if (!Auth::check()) {
            if ($this->esAjax()) $this->json(['ok' => false, 'error' => 'Sesión expirada'], 401);
            flash('warning', 'Inicia sesión para continuar.');
            redirect('auth/login');
        }
        if ($roles && !Auth::es(...$roles)) {
            if ($this->esAjax()) $this->json(['ok' => false, 'error' => 'No autorizado'], 403);
            http_response_code(403);
            flash('danger', 'No tienes permiso para acceder a esa sección.');
            redirect('inicio/index');
        }
    }

    /** Exige POST con token CSRF válido (protege acciones que modifican datos). */
    protected function soloPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
            if ($this->esAjax()) $this->json(['ok' => false, 'error' => 'Solicitud no válida'], 400);
            flash('danger', 'Solicitud no válida. Vuelve a intentarlo.');
            redirect('inicio/index');
        }
    }

    /**
     * Devuelve el miembro sobre el que se trabaja:
     *  - Si el usuario es Miembro => él mismo.
     *  - Si es Instructor/Admin => el indicado en ?miembro= o POST miembro
     *    (o null si todavía no eligió ninguno).
     */
    protected function miembroObjetivo(): ?array
    {
        if (Auth::es(Auth::MIEMBRO)) {
            return Usuario::find(Auth::id());
        }
        $id = (int) ($_POST['miembro'] ?? $_GET['miembro'] ?? 0);
        if (!$id) return null;
        $m = Usuario::find($id);
        return ($m && (int) $m['id_rol'] === Auth::MIEMBRO) ? $m : null;
    }

    /**
     * Igual que miembroObjetivo(), pero si el instructor/admin aún no
     * eligió un miembro muestra la pantalla para elegirlo y termina.
     * $modulo es la ruta a la que se vuelve (ej. 'rutina/index').
     */
    protected function exigirMiembro(string $modulo, string $tituloModulo): array
    {
        $m = $this->miembroObjetivo();
        if ($m) return $m;
        if ($this->esAjax() || $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->json(['ok' => false, 'error' => 'Miembro no válido'], 400);
        }
        $this->view('miembros/elegir', [
            'titulo'   => $tituloModulo . ': elegir miembro',
            'modulo'   => $modulo,
            'miembros' => Usuario::miembrosResumen(),
        ]);
        exit;
    }

    /** Parámetros de URL para volver a la pantalla del mismo miembro. */
    protected function paramsMiembro(array $m, array $extra = []): array
    {
        return Auth::es(Auth::MIEMBRO) ? $extra : ['miembro' => $m['id']] + $extra;
    }
}

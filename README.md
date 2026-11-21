# Fuerza Pro

Sistema web para la gestión de un gimnasio. Proyecto final de la materia Programación Web Avanzada (Ejercicio 10).

**Integrantes:** Jhoao Guala, Keyla Duran
**Sitio publicado:** https://fuerzapro.infinityfreeapp.com

## Descripción

Fuerza Pro permite administrar los miembros, instructores, clases e instalaciones de un gimnasio. Cada usuario entra con su cuenta y ve opciones distintas según su rol:

- **Administrador:** gestiona usuarios, instalaciones y clases, revisa las reservas y ve un resumen general del gimnasio.
- **Instructor:** programa sus clases, ve quiénes están inscritos, toma asistencia y arma la rutina y la dieta de los miembros.
- **Miembro:** reserva clases, revisa su historial, sigue su rutina semanal, consulta su dieta y registra sus medidas para ver su progreso en gráficas.

## Tecnologías

- HTML, CSS y Bootstrap 5
- JavaScript (fetch, validación de formularios, Chart.js)
- PHP 8 sin frameworks
- MySQL / MariaDB con phpMyAdmin

## Estructura del proyecto

El proyecto sigue una estructura MVC sencilla hecha por nosotros. Todas las peticiones entran por `index.php`, que según el parámetro `r` (por ejemplo `index.php?r=clases/index`) llama al controlador y a la acción correspondiente.

```
gimnasio.sql     base de datos
index.php        punto de entrada
config/          datos de conexión
core/            conexión PDO, router, sesión y funciones generales
models/          consultas a la base de datos
controllers/     lógica de cada módulo
views/           páginas (HTML + Bootstrap)
assets/          css, js e imágenes
```

## Base de datos

El archivo `gimnasio.sql` crea las tablas y carga datos de ejemplo. Partimos de la base sugerida en el enunciado y le hicimos algunos cambios:

- Una sola tabla `usuarios` relacionada con `roles`, en lugar de tablas separadas para miembros e instructores.
- Llaves foráneas entre las tablas.
- Tablas adicionales para rutinas, registro de entrenamientos, mediciones y dieta.

Tablas: `roles`, `usuarios`, `instalaciones`, `clases`, `reservas`, `ejercicios_rutina`, `registros_entrenamiento`, `mediciones` y `comidas`.

## Instalación local

1. Copiar la carpeta del proyecto en `htdocs` (XAMPP) o en `www` (Laragon).
2. Iniciar Apache y MySQL.
3. En phpMyAdmin crear una base de datos llamada `gimnasio` (cotejamiento `utf8mb4_unicode_ci`) e importar `gimnasio.sql`.
4. Abrir `http://localhost/pwapractica6/` (o el nombre que tenga la carpeta).

Por defecto se conecta con el usuario `root` sin contraseña. Si se necesitan otros datos de conexión, se copia `config/config.local.example.php` como `config/config.local.php` y se cambian ahí. Ese archivo no se sube al repositorio.

## Usuarios de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@fuerzapro.com | Admin123* |
| Instructor | instructor@fuerzapro.com | Instructor123* |
| Miembro | miembro@fuerzapro.com | Miembro123* |

También se puede crear una cuenta de miembro desde la opción "Crea tu cuenta".

## Seguridad

- Contraseñas guardadas con `password_hash`.
- Consultas preparadas con PDO.
- Token CSRF en los formularios.
- Cada acción verifica el rol del usuario.
- El archivo `.htaccess` bloquea el acceso directo a las carpetas internas.

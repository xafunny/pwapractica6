# 🏋️ Fuerza Pro — Sistema de gestión de un gimnasio

Proyecto final de **Programación Web Avanzada** · Ejercicio 10: *Sistema de gestión de un gimnasio*.

Aplicación web hecha con **HTML, Bootstrap 5, CSS, JavaScript, PHP y MySQL**, **sin frameworks PHP**.
Usa un **pseudo-framework MVC propio**: front controller, router, controladores, modelos con PDO y vistas.

---

## ✨ Funcionalidades por rol

| Rol | Puede… |
|---|---|
| **Administrador** | Ver métricas del gimnasio · CRUD de **usuarios** (miembros, instructores y admins; activar o desactivar) · CRUD de **instalaciones** · CRUD de **clases** de cualquier instructor · supervisar todas las **reservas** · ver y editar la rutina, la dieta y el progreso de cualquier miembro |
| **Instructor** | Gestionar **sus clases y horarios** (crear, editar, cancelar, eliminar) · ver los **inscritos** y **tomar asistencia** · armar la **rutina** y la **dieta** de los miembros · revisar su **progreso** |
| **Miembro** | Registrarse · **reservar** clases con cupos en tiempo real y cancelar · consultar su **historial de actividades** (clases y entrenamientos) · seguir su **rutina semanal** marcando ejercicios · ver su **dieta** · registrar **mediciones** con gráficas · editar su **perfil** |

## 🧱 Tecnologías

- **PHP 8.1+** (PDO, sesiones, `password_hash`), sin frameworks
- **MySQL / MariaDB**, administrado con phpMyAdmin
- **Bootstrap 5.3**, Bootstrap Icons y CSS propio (tema oscuro con acento rojo)
- **JavaScript** puro: `fetch()` para reservar, marcar ejercicios, tomar asistencia y activar usuarios sin recargar; validación de formularios; Chart.js para las gráficas

## 📁 Estructura

```
Fuerza-Pro/
├── gimnasio.sql              ← Base de datos (importar en phpMyAdmin)
├── index.php                 ← Front controller: única entrada (?r=controlador/accion)
├── .htaccess                 ← Bloquea el acceso directo a carpetas internas
├── config/
│   ├── config.php            ← Datos de conexión por defecto (XAMPP/Laragon)
│   └── config.local.example.php ← Plantilla para el hosting
├── core/                     ← Pseudo-framework
│   ├── Database.php          ← Conexión PDO (Singleton)
│   ├── Router.php            ← Enrutador ?r=controlador/accion
│   ├── Controller.php        ← Base: vistas, JSON, control de roles, CSRF
│   ├── Model.php             ← Base: CRUD genérico con consultas preparadas
│   ├── Auth.php              ← Sesión y roles
│   └── helpers.php           ← e(), url(), flash(), csrf, fechas…
├── models/                   ← Usuario, Clase, Reserva, Instalacion, Ejercicio, Medicion, Comida
├── controllers/              ← Auth, Inicio, Usuarios, Instalaciones, Clases, Reservas,
│                               Miembros, Rutina, Dieta, Progreso, Perfil
├── views/                    ← layout/ (header, footer) + una carpeta por módulo
└── assets/
    ├── css/styles.css
    ├── js/                   ← app.js (común) + un script por página
    └── img/
```

**Flujo de una petición:** `index.php` → `Router` → `XxxController::accion()` → `Modelo` (PDO) → `view()` (header + vista + footer), o bien `json()` cuando la petición viene de `fetch()`.

## 🗄️ Base de datos

Se tomó como base la BD sugerida del enunciado y se **mejoró**:

- Una sola tabla **`usuarios`** con `id_rol`, en lugar de tablas separadas de miembros e instructores que repetían correo y contraseña.
- Nombres **sin tildes ni ñ**, **llaves foráneas** reales, índices y `ON DELETE CASCADE` donde corresponde.
- Tablas: `roles`, `usuarios`, `instalaciones`, `clases`, `reservas`, `ejercicios_rutina`, `registros_entrenamiento`, `mediciones`, `comidas`.

## 🚀 Instalación local (XAMPP o Laragon)

1. Copia la carpeta `Fuerza-Pro` en `C:\xampp\htdocs\` (o en `C:\laragon\www\`).
2. Inicia **Apache** y **MySQL**.
3. Abre **phpMyAdmin** → *Nueva* → crea la base de datos `gimnasio` con cotejamiento `utf8mb4_unicode_ci`.
4. Selecciónala → pestaña **Importar** → sube `gimnasio.sql`.
5. Abre `http://localhost/Fuerza-Pro/`.

> Si tu MySQL tiene otra contraseña, otro usuario u otro nombre de base de datos, cópialo en `config/config.local.php` (usa la plantilla `config.local.example.php`).

### Usuarios de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@fuerzapro.com | Admin123* |
| Instructor | instructor@fuerzapro.com | Instructor123* |
| Miembro | miembro@fuerzapro.com | Miembro123* |

## 🌐 Publicar en InfinityFree (u otro hosting gratuito)

1. Crea la cuenta y el sitio. En **MySQL Databases** crea una base de datos y anota el *hostname*, el *nombre*, el *usuario* y la *contraseña*.
2. Entra a **phpMyAdmin** desde el panel, selecciona esa base de datos e **importa `gimnasio.sql`**.
3. Copia `config/config.local.example.php` como `config/config.local.php` y pon allí los datos del paso 1.
4. Sube **el contenido** de la carpeta `Fuerza-Pro` a `htdocs/` con el *File Manager* o por FTP (FileZilla).
5. Abre la URL de tu sitio.

`config.local.php` está en `.gitignore`, así que las contraseñas del hosting **no se suben a GitHub**.

## 🔒 Seguridad aplicada

- Consultas **preparadas (PDO)** en todo el sistema → previene inyección SQL
- Contraseñas con **`password_hash` / `password_verify`** (bcrypt)
- **Token CSRF** en todos los formularios y en las peticiones `fetch()`
- Escape de salida con `htmlspecialchars` (`e()`) → previene XSS
- Control de **roles** en cada acción y verificación de **propiedad** de los datos (un miembro no puede tocar datos de otro; un instructor solo edita sus clases)
- `session_regenerate_id` al iniciar sesión, cookies `HttpOnly` y `SameSite`, límite de intentos de login
- **Transacción con `SELECT … FOR UPDATE`** al reservar, para que dos personas no tomen el último cupo a la vez
- `.htaccess` y la constante `APP` impiden abrir directamente los archivos internos

## 📝 Historia del proyecto

Fuerza Pro empezó como una app de una sola página con Firebase (Auth + Firestore). Para este proyecto se **migró a PHP + MySQL**:

- se reemplazó Firebase por un backend propio con sesiones y PDO;
- se agregó el rol **Instructor** y los módulos de **clases, instalaciones y reservas** del Ejercicio 10;
- se conservaron la identidad visual y las funciones originales de rutina, dieta y progreso.

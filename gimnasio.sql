-- =====================================================================
--  FUERZA PRO - Sistema de gestión de un gimnasio
--  Base de datos MySQL / MariaDB  (importar desde phpMyAdmin)
-- ---------------------------------------------------------------------
--  Proyecto final - Programación Web Avanzada (Ejercicio 10)
--
--  Cambios respecto a la base de datos sugerida en el enunciado:
--   * Se unifican miembros, instructores y administradores en una sola
--     tabla `usuarios` relacionada con `roles` (evita duplicar correo y
--     contraseña en 3 tablas y simplifica el inicio de sesión).
--   * Nombres de tablas y columnas SIN tildes ni "ñ" (evita problemas de
--     codificación en PHP y en hostings gratuitos).
--   * Se agregan llaves foráneas reales (FOREIGN KEY) e índices.
--   * Se agregan tablas propias de la app Fuerza Pro: rutinas de
--     ejercicios, registro de entrenamientos, mediciones corporales y dieta.
--
--  USO LOCAL (XAMPP / Laragon):
--    1. En phpMyAdmin crear la base de datos `gimnasio` (utf8mb4_unicode_ci)
--    2. Seleccionarla y en la pestaña "Importar" subir este archivo.
--  En un hosting (InfinityFree, etc.) crear la BD desde su panel e
--  importar este archivo dentro de ella (NO incluye CREATE DATABASE).
--
--  Usuarios de prueba (contraseña entre paréntesis):
--    admin@fuerzapro.com      (Admin123*)       -> Administrador
--    instructor@fuerzapro.com (Instructor123*)  -> Instructor
--    miembro@fuerzapro.com    (Miembro123*)     -> Miembro
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS comidas;
DROP TABLE IF EXISTS mediciones;
DROP TABLE IF EXISTS registros_entrenamiento;
DROP TABLE IF EXISTS ejercicios_rutina;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS clases;
DROP TABLE IF EXISTS instalaciones;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- ROLES: catálogo de los tipos de usuario del sistema
-- ---------------------------------------------------------------------
CREATE TABLE roles (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (id, nombre) VALUES
  (1, 'Administrador'),
  (2, 'Instructor'),
  (3, 'Miembro');

-- ---------------------------------------------------------------------
-- USUARIOS: todas las personas que inician sesión.
--   especialidad -> solo se usa para instructores
--   nivel/objetivo -> solo se usan para miembros
--   contrasena guarda el HASH generado por password_hash() (bcrypt)
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  nombre           VARCHAR(50)  NOT NULL,
  apellido         VARCHAR(50)  NOT NULL DEFAULT '',
  correo           VARCHAR(100) NOT NULL UNIQUE,
  contrasena       VARCHAR(255) NOT NULL,
  id_rol           INT          NOT NULL,
  telefono         VARCHAR(15)  NULL,
  fecha_nacimiento DATE         NULL,
  genero           ENUM('Masculino','Femenino','Otro') NULL,
  especialidad     VARCHAR(50)  NULL,
  nivel            ENUM('Principiante','Intermedio','Avanzado') NULL,
  objetivo         VARCHAR(255) NULL,
  activo           TINYINT(1)   NOT NULL DEFAULT 1,
  creado_en        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_roles FOREIGN KEY (id_rol) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- INSTALACIONES: salas y áreas físicas del gimnasio
-- ---------------------------------------------------------------------
CREATE TABLE instalaciones (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(50)  NOT NULL,
  tipo        VARCHAR(30)  NOT NULL,
  capacidad   INT          NOT NULL,
  descripcion VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- CLASES: sesiones grupales con instructor, sala, fecha/hora y cupo
-- ---------------------------------------------------------------------
CREATE TABLE clases (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  nombre         VARCHAR(50)  NOT NULL,
  tipo           VARCHAR(30)  NOT NULL,
  descripcion    VARCHAR(255) NULL,
  id_instructor  INT          NOT NULL,
  id_instalacion INT          NOT NULL,
  fecha_hora     DATETIME     NOT NULL,
  duracion_min   INT          NOT NULL DEFAULT 60,
  cupo           INT          NOT NULL DEFAULT 20,
  estado         ENUM('Programada','Cancelada') NOT NULL DEFAULT 'Programada',
  INDEX idx_clases_fecha (fecha_hora),
  CONSTRAINT fk_clases_instructor  FOREIGN KEY (id_instructor)  REFERENCES usuarios(id),
  CONSTRAINT fk_clases_instalacion FOREIGN KEY (id_instalacion) REFERENCES instalaciones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- RESERVAS: un miembro reserva un cupo en una clase (una sola vez)
-- ---------------------------------------------------------------------
CREATE TABLE reservas (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  id_miembro    INT      NOT NULL,
  id_clase      INT      NOT NULL,
  fecha_reserva DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado        ENUM('Reservada','Asistio','Falto','Cancelada') NOT NULL DEFAULT 'Reservada',
  UNIQUE KEY uq_reserva (id_miembro, id_clase),
  CONSTRAINT fk_reservas_miembro FOREIGN KEY (id_miembro) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_reservas_clase   FOREIGN KEY (id_clase)   REFERENCES clases(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- EJERCICIOS_RUTINA: rutina semanal personalizada de cada miembro
-- (la arma el instructor o el propio miembro)
-- ---------------------------------------------------------------------
CREATE TABLE ejercicios_rutina (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  id_miembro    INT          NOT NULL,
  dia           ENUM('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  nombre        VARCHAR(60)  NOT NULL,
  series        VARCHAR(10)  NULL,
  repeticiones  VARCHAR(15)  NULL,
  descanso      VARCHAR(10)  NULL,
  peso          VARCHAR(10)  NULL,
  notas         VARCHAR(255) NULL,
  orden         INT          NOT NULL DEFAULT 0,
  creado_por    INT          NULL,
  CONSTRAINT fk_rutina_miembro FOREIGN KEY (id_miembro) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_rutina_autor   FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- REGISTROS_ENTRENAMIENTO: cada vez que el miembro marca un ejercicio
-- como hecho se guarda la fecha. Sirve para el progreso semanal y
-- para el historial de actividades.
-- ---------------------------------------------------------------------
CREATE TABLE registros_entrenamiento (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  id_ejercicio INT  NOT NULL,
  fecha        DATE NOT NULL,
  UNIQUE KEY uq_registro (id_ejercicio, fecha),
  CONSTRAINT fk_registro_ejercicio FOREIGN KEY (id_ejercicio) REFERENCES ejercicios_rutina(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- MEDICIONES: composición corporal del miembro a lo largo del tiempo
-- ---------------------------------------------------------------------
CREATE TABLE mediciones (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  id_miembro INT          NOT NULL,
  fecha      DATE         NOT NULL,
  peso       DECIMAL(5,2) NOT NULL,
  estatura   DECIMAL(5,1) NULL,
  grasa      DECIMAL(4,1) NULL,
  musculo    DECIMAL(4,1) NULL,
  imc        DECIMAL(4,1) NULL,
  CONSTRAINT fk_mediciones_miembro FOREIGN KEY (id_miembro) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- COMIDAS: plan de alimentación semanal del miembro
-- ---------------------------------------------------------------------
CREATE TABLE comidas (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  id_miembro  INT          NOT NULL,
  dia         ENUM('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  momento     ENUM('Desayuno','Almuerzo','Merienda','Cena') NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  calorias    INT          NULL,
  CONSTRAINT fk_comidas_miembro FOREIGN KEY (id_miembro) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATOS DE PRUEBA
-- =====================================================================
INSERT INTO usuarios (id, nombre, apellido, correo, contrasena, id_rol, telefono, fecha_nacimiento, genero, especialidad, nivel, objetivo) VALUES
  (1, 'Admin',  'Fuerza Pro', 'admin@fuerzapro.com',       '$2y$12$fc398gk.A5xZgWzi3ISwP.rOzjiE4DrMstXvQfPG3ENsrdF1dL8y2', 1, '0990000001', '1990-01-15', 'Masculino', NULL, NULL, NULL),
  (2, 'Carlos', 'Mendoza',    'instructor@fuerzapro.com',  '$2y$12$0JtWF3lmVkbiSegNAOG0QuunqpFpbqDnC4HCe29Rcq4CjsoGMOCYK', 2, '0990000002', '1992-05-20', 'Masculino', 'Musculacion', NULL, NULL),
  (3, 'Andrea', 'Vera',       'andrea@fuerzapro.com',      '$2y$12$0JtWF3lmVkbiSegNAOG0QuunqpFpbqDnC4HCe29Rcq4CjsoGMOCYK', 2, '0990000003', '1995-09-02', 'Femenino',  'Funcional',   NULL, NULL),
  (4, 'Luis',   'Paredes',    'miembro@fuerzapro.com',     '$2y$12$8ABu9.ifDtFsqCW4rAj5Me/XqJu/z2lWasz9AEPfVilYYzcB.sC42', 3, '0990000004', '2001-03-11', 'Masculino', NULL, 'Intermedio', 'Ganar masa muscular'),
  (5, 'María',  'Salazar',    'maria@fuerzapro.com',       '$2y$12$8ABu9.ifDtFsqCW4rAj5Me/XqJu/z2lWasz9AEPfVilYYzcB.sC42', 3, '0990000005', '1998-12-30', 'Femenino',  NULL, 'Principiante', 'Bajar de peso');

INSERT INTO instalaciones (id, nombre, tipo, capacidad, descripcion) VALUES
  (1, 'Sala de Pesas',    'Musculacion', 40, 'Máquinas, mancuernas y barras olímpicas'),
  (2, 'Sala Funcional',   'Funcional',   20, 'Kettlebells, TRX, cajones y cuerdas'),
  (3, 'Estudio de Spinning', 'Cardio',   25, 'Bicicletas estáticas con sonido'),
  (4, 'Sala de Yoga',     'Flexibilidad',15, 'Espacio tranquilo con colchonetas');

-- Las clases se crean con fechas relativas a HOY para que siempre haya
-- clases futuras disponibles al importar la base de datos.
INSERT INTO clases (id, nombre, tipo, descripcion, id_instructor, id_instalacion, fecha_hora, duracion_min, cupo) VALUES
  (1, 'Fuerza Total',     'Musculacion', 'Entrenamiento de fuerza guiado',      2, 1, TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '07:00:00'), 60, 15),
  (2, 'Spinning Extremo', 'Cardio',      'Ciclismo indoor de alta intensidad',  3, 3, TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '18:00:00'), 45, 25),
  (3, 'HIIT Funcional',   'Funcional',   'Circuitos de intervalos',             3, 2, TIMESTAMP(CURDATE() + INTERVAL 2 DAY, '19:00:00'), 50, 2),
  (4, 'Yoga y Movilidad', 'Flexibilidad','Estiramientos y respiración',         3, 4, TIMESTAMP(CURDATE() + INTERVAL 3 DAY, '08:00:00'), 60, 15),
  (5, 'Pierna Pesada',    'Musculacion', 'Sentadilla, prensa y peso muerto',    2, 1, TIMESTAMP(CURDATE() + INTERVAL 4 DAY, '17:00:00'), 60, 12),
  (6, 'Fuerza Total',     'Musculacion', 'Clase pasada de ejemplo',             2, 1, TIMESTAMP(CURDATE() - INTERVAL 3 DAY, '07:00:00'), 60, 15);

INSERT INTO reservas (id_miembro, id_clase, estado) VALUES
  (4, 1, 'Reservada'),
  (5, 3, 'Reservada'),
  (4, 6, 'Asistio');

INSERT INTO ejercicios_rutina (id_miembro, dia, nombre, series, repeticiones, descanso, peso, orden, creado_por) VALUES
  (4, 'Lunes',     'Press banca',          '4', '10',    '90s', '60',  1, 2),
  (4, 'Lunes',     'Press inclinado',      '3', '12',    '60s', '20',  2, 2),
  (4, 'Lunes',     'Fondos en paralelas',  '3', '10',    '60s', '',    3, 2),
  (4, 'Miercoles', 'Sentadilla',           '4', '8',     '120s','80',  1, 2),
  (4, 'Miercoles', 'Prensa',               '3', '12',    '90s', '150', 2, 2),
  (4, 'Viernes',   'Dominadas',            '4', '8',     '90s', '',    1, 2),
  (4, 'Viernes',   'Remo con barra',       '4', '10',    '90s', '50',  2, 2),
  (5, 'Martes',    'Caminadora',           '1', '30 min','',    '',    1, 3),
  (5, 'Jueves',    'Circuito funcional',   '3', '45s',   '30s', '',    1, 3);

INSERT INTO mediciones (id_miembro, fecha, peso, estatura, grasa, musculo, imc) VALUES
  (4, CURDATE() - INTERVAL 60 DAY, 72.50, 175.0, 18.5, 40.1, 23.7),
  (4, CURDATE() - INTERVAL 30 DAY, 73.80, 175.0, 17.2, 41.0, 24.1),
  (4, CURDATE() - INTERVAL 2 DAY,  75.10, 175.0, 16.4, 42.3, 24.5),
  (5, CURDATE() - INTERVAL 20 DAY, 68.00, 162.0, 30.0, 33.0, 25.9);

INSERT INTO comidas (id_miembro, dia, momento, descripcion, calorias) VALUES
  (4, 'Lunes', 'Desayuno', 'Avena con plátano y 3 claras de huevo', 450),
  (4, 'Lunes', 'Almuerzo', 'Arroz integral, pechuga de pollo y ensalada', 700),
  (4, 'Lunes', 'Merienda', 'Yogur griego con frutos secos', 250),
  (4, 'Lunes', 'Cena',     'Salmón con vegetales al vapor', 550);

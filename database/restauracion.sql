-- Script de restauracion listo para importar en phpMyAdmin (local o hosting)
-- En local, crea y selecciona primero la base de datos eventos_academicos si aun no existe

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario  INT AUTO_INCREMENT PRIMARY KEY,
    usuario     VARCHAR(50)  NOT NULL UNIQUE,
    clave_hash  VARCHAR(255) NOT NULL,
    rol         ENUM('admin','estudiante') NOT NULL DEFAULT 'estudiante'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS estudiante (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    carnet        VARCHAR(10) NOT NULL UNIQUE,
    nombre        VARCHAR(60) NOT NULL,
    apellido      VARCHAR(60) NOT NULL,
    id_usuario    INT NOT NULL UNIQUE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evento (
    id_evento       INT AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(15)  NOT NULL UNIQUE,
    nombre          VARCHAR(100) NOT NULL,
    descripcion     TEXT,
    lugar           VARCHAR(100) NOT NULL,
    fecha           DATE NOT NULL,
    hora            TIME NOT NULL,
    cupo_disponible INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inscripcion (
    id_inscripcion    INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante     INT NOT NULL,
    id_evento         INT NOT NULL,
    fecha_inscripcion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_estudiante, id_evento),
    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id_estudiante),
    FOREIGN KEY (id_evento) REFERENCES evento(id_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS asistencia (
    id_asistencia  INT AUTO_INCREMENT PRIMARY KEY,
    id_inscripcion INT NOT NULL UNIQUE,
    fecha          DATE NOT NULL,
    estado         ENUM('presente','ausente','justificado') NOT NULL,
    FOREIGN KEY (id_inscripcion) REFERENCES inscripcion(id_inscripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario administrador inicial (usuario: admin, clave: password)
INSERT INTO usuario (usuario, clave_hash, rol)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE
    clave_hash = VALUES(clave_hash),
    rol = 'admin';

-- Estudiante de prueba inicial (usuario: estudiante1, clave: password, carnet: PE25001)
INSERT INTO usuario (usuario, clave_hash, rol)
VALUES ('estudiante1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante')
ON DUPLICATE KEY UPDATE
    clave_hash = VALUES(clave_hash),
    rol = 'estudiante';

INSERT INTO estudiante (carnet, nombre, apellido, id_usuario)
SELECT 'PE25001', 'Juan', 'Pérez', u.id_usuario
FROM usuario u
WHERE u.usuario = 'estudiante1'
  AND NOT EXISTS (
      SELECT 1 FROM estudiante e WHERE e.carnet = 'PE25001' OR e.id_usuario = u.id_usuario
  );

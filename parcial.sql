CREATE DATABASE eventos_academicos CHARACTER SET utf8mb4;
USE eventos_academicos;

CREATE TABLE usuario (
    id_usuario  INT AUTO_INCREMENT PRIMARY KEY,
    usuario     VARCHAR(50)  NOT NULL UNIQUE,
    clave_hash  VARCHAR(255) NOT NULL,
    rol         ENUM('admin','estudiante') NOT NULL DEFAULT 'estudiante'
);

CREATE TABLE estudiante (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    carnet        VARCHAR(10) NOT NULL UNIQUE,
    nombre        VARCHAR(60) NOT NULL,
    apellido      VARCHAR(60) NOT NULL,
    id_usuario    INT NOT NULL UNIQUE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE evento (
    id_evento       INT AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(15)  NOT NULL UNIQUE,
    nombre          VARCHAR(100) NOT NULL,
    descripcion     TEXT,
    lugar           VARCHAR(100) NOT NULL,
    fecha           DATE NOT NULL,
    hora            TIME NOT NULL,
    cupo_disponible INT UNSIGNED NOT NULL
);

CREATE TABLE inscripcion (
    id_inscripcion    INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante     INT NOT NULL,
    id_evento         INT NOT NULL,
    fecha_inscripcion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_estudiante, id_evento),
    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id_estudiante),
    FOREIGN KEY (id_evento) REFERENCES evento(id_evento)
);

CREATE TABLE asistencia (
    id_asistencia  INT AUTO_INCREMENT PRIMARY KEY,
    id_inscripcion INT NOT NULL UNIQUE,
    fecha          DATE NOT NULL,
    estado         ENUM('presente','ausente','justificado') NOT NULL,
    FOREIGN KEY (id_inscripcion) REFERENCES inscripcion(id_inscripcion)
);
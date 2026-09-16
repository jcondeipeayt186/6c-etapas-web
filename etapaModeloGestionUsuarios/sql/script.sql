/*
    SCRIPT SQL - ETAPA 5 Gestión Usuarios (ETAPA FINAL)
    =====================================================
    Hereda ciudades + personas de etapa3 y agrega:
      - usuarios (id, username, password, fechaUltimoAcceso, fechaCreacion)
      - roles (id, nombre, descripcion, tipo TINYINT)
      - usuario_rol (id, usuario_id, rol_id, fechaCreacion) N:M

    tipo: 1 = Administrador, 2 = Directivo, 3 = Usuario/Operador, 4 = Invitado (ejemplo)

    Ejecutar:
      mysql -u root -p < script.sql
    o desde phpMyAdmin pestaña SQL con BD seleccionada.
*/

CREATE DATABASE IF NOT EXISTS contactos5 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE contactos5;

-- ===========================================
-- TABLA CIUDADES (heredada de etapa3)
-- ===========================================
CREATE TABLE IF NOT EXISTS ciudades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    provincia VARCHAR(100) NOT NULL,
    latitud DECIMAL(9,6),
    longitud DECIMAL(10,6),
    codigo_postal VARCHAR(10),
    descripcion TEXT,
    fecha_fundacion DATE
);

-- ===========================================
-- TABLA PERSONAS (con FK a ciudades)
-- ===========================================
CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    cuit VARCHAR(20) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    ciudad_id INT,
    observaciones TEXT,
    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)
);

-- ===========================================
-- TABLA USUARIOS
-- ===========================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'hash bcrypt',
    fechaUltimoAcceso DATETIME NULL,
    fechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================
-- TABLA ROLES
-- ===========================================
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    tipo TINYINT NOT NULL COMMENT '1=Administrador, 2=Directivo, 3=Usuario'
);

-- ===========================================
-- TABLA INTERMEDIA USUARIO_ROL (N:M)
-- ===========================================
CREATE TABLE IF NOT EXISTS usuario_rol (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    rol_id INT NOT NULL,
    fechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY uq_usuario_rol (usuario_id, rol_id)
);

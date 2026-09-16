USE contactos5;

-- Limpiar para re-ejecutar (orden por FK)
-- SET FOREIGN_KEY_CHECKS=0; TRUNCATE ... SET FOREIGN_KEY_CHECKS=1;

-- -------------------------------------------------------
-- ROLES BASE
-- -------------------------------------------------------
INSERT INTO roles (nombre, descripcion, tipo) VALUES
('Administrador', 'Acceso total: gestiona usuarios, roles, personas y ciudades', 1),
('Directivo', 'Gestiona personas y ciudades, no gestiona usuarios', 2),
('Operador', 'Usuario operativo con acceso limitado (sin módulos administrativos)', 3)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- -------------------------------------------------------
-- USUARIOS DEMO (passwords hasheados con bcrypt)
-- admin / admin123 -> tipo 1
-- directivo / direc123 -> tipo 2
-- operador / oper123 -> tipo 3
-- Los hashes fueron generados con password_hash('xxx', PASSWORD_DEFAULT)
-- -------------------------------------------------------
-- Para regenerarlos en PHP: echo password_hash('admin123', PASSWORD_DEFAULT);

-- Hashes generados con password_hash('admin123'/'direc123'/'oper123', PASSWORD_DEFAULT) el 2026-09-16
INSERT INTO usuarios (username, password, fechaCreacion) VALUES
('admin', '$2y$10$nDGkPVrmR4iS/QGcp.SO/.cxxNb9kMtIZw0PRWoggn0L33.I/O75e', '2026-01-01 08:00:00'),
('directivo', '$2y$10$Zj2V5AFkDc3wvPMKks2loO1ivoixuekUWjeZTsLTJ80n3jo.Mttfa', '2026-01-01 08:00:00'),
('operador', '$2y$10$zcR6zL4Q4ZZDI.lbeXx7MOR9chDSCIS8Kb6lUCwv4sF/Hhvk4ihym', '2026-01-01 08:00:00')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Por si la tabla ya existía con hashes viejos (ej: hash de 'password'), forzar actualización correcta por usuario
UPDATE usuarios SET password = '$2y$10$nDGkPVrmR4iS/QGcp.SO/.cxxNb9kMtIZw0PRWoggn0L33.I/O75e' WHERE username='admin';
UPDATE usuarios SET password = '$2y$10$Zj2V5AFkDc3wvPMKks2loO1ivoixuekUWjeZTsLTJ80n3jo.Mttfa' WHERE username='directivo';
UPDATE usuarios SET password = '$2y$10$zcR6zL4Q4ZZDI.lbeXx7MOR9chDSCIS8Kb6lUCwv4sF/Hhvk4ihym' WHERE username='operador';

-- -------------------------------------------------------
-- ASIGNACIÓN USUARIO_ROL
-- -------------------------------------------------------
-- admin -> Administrador (1)
INSERT IGNORE INTO usuario_rol (usuario_id, rol_id) SELECT u.id, r.id FROM usuarios u, roles r WHERE u.username='admin' AND r.nombre='Administrador';
-- directivo -> Directivo (2)
INSERT IGNORE INTO usuario_rol (usuario_id, rol_id) SELECT u.id, r.id FROM usuarios u, roles r WHERE u.username='directivo' AND r.nombre='Directivo';
-- operador -> Operador (3)
INSERT IGNORE INTO usuario_rol (usuario_id, rol_id) SELECT u.id, r.id FROM usuarios u, roles r WHERE u.username='operador' AND r.nombre='Operador';

-- -------------------------------------------------------
-- CIUDADES (20, igual que etapa3)
-- -------------------------------------------------------
INSERT IGNORE INTO ciudades (id, nombre, provincia, latitud, longitud, codigo_postal, descripcion, fecha_fundacion) VALUES
(1, 'Río Cuarto', 'Córdoba', -33.123456, -64.349924, '5800', 'Ciudad del sur de Córdoba, capital alterna', '1786-11-11'),
(2, 'Córdoba', 'Córdoba', -31.420083, -64.188776, '5000', 'Capital de la provincia de Córdoba', '1573-07-06'),
(3, 'Villa María', 'Córdoba', -32.410000, -63.240000, '5900', 'Ciudad industrial del centro cordobés', '1867-09-27'),
(4, 'Alta Gracia', 'Córdoba', -31.658333, -64.430556, '5186', 'Ciudad del Tajamar', '1588-01-01'),
(5, 'Jesús María', 'Córdoba', -30.983333, -64.100000, '5220', 'Festival de Doma y Folklore', '1873-01-01'),
(6, 'La Carlota', 'Córdoba', -33.416667, -62.900000, '2670', 'Sureste cordobés', '1737-01-01'),
(7, 'Rosario', 'Santa Fe', -32.958702, -60.693900, '2000', 'Ciudad portuaria del Paraná', '1852-08-05'),
(8, 'Santa Fe', 'Santa Fe', -31.633333, -60.700000, '3000', 'Capital de Santa Fe', '1573-11-15'),
(9, 'Rafaela', 'Santa Fe', -31.250000, -61.483333, '2300', 'Perla del oeste santafesino', '1881-10-24'),
(10, 'La Plata', 'Buenos Aires', -34.921450, -57.954530, '1900', 'Capital de Buenos Aires, ciudad de las diagonales', '1882-11-19'),
(11, 'Mar del Plata', 'Buenos Aires', -38.005477, -57.542610, '7600', 'Ciudad balnearia', '1874-02-10'),
(12, 'Bahía Blanca', 'Buenos Aires', -38.005000, -62.270000, '8000', 'Puerto del sur bonaerense', '1828-04-11'),
(13, 'Tandil', 'Buenos Aires', -37.316667, -59.150000, '7000', 'Sierras bonaerenses', '1823-04-04'),
(14, 'Mendoza', 'Mendoza', -32.889458, -68.845840, '5500', 'Capital del vino', '1561-03-02'),
(15, 'San Juan', 'San Juan', -31.537500, -68.536390, '5400', 'Tierra del sol', '1562-06-13'),
(16, 'San Luis', 'San Luis', -33.295010, -66.335630, '5700', 'Capital puntana', '1594-08-25'),
(17, 'Tucumán', 'Tucumán', -26.808285, -65.217590, '4000', 'Jardín de la República', '1565-05-31'),
(18, 'Salta', 'Salta', -24.782127, -65.423198, '4400', 'La linda', '1582-04-16'),
(19, 'Neuquén', 'Neuquén', -38.005477, -68.053680, '8300', 'Capital de la Patagonia', '1904-09-12'),
(20, 'Bariloche', 'Río Negro', -41.133472, -71.310360, '8400', 'Ciudad andina, turismo y chocolate', '1902-05-03');

-- -------------------------------------------------------
-- PERSONAS (20 ejemplo, el resto se puede cargar desde la app)
-- -------------------------------------------------------
INSERT IGNORE INTO personas (id, nombre, apellido, dni, cuit, fecha_nacimiento, email, telefono, direccion, ciudad_id, observaciones) VALUES
(1, 'Julián', 'Conde', '30090561', '20300905618', '1983-04-10', 'jconde@ac.unrc.edu.ar', '3584123456', 'Av. San Martín 123', 1, 'Docente'),
(2, 'María', 'Pérez', '31333456', '27313334569', '1987-05-12', 'maria.perez@gmail.com', '3534111579', 'Belgrano 456', 2, 'Estudiante'),
(3, 'Juan', 'Gómez', '28456123', '20284561230', '1980-11-03', 'juan.gomez@hotmail.com', '3516789012', 'Colón 789', 2, NULL),
(4, 'Lucía', 'Fernández', '35123456', '27351234568', '1990-07-22', 'lucia.fernandez@gmail.com', '3512345678', 'Gral. Paz 101', 3, 'Diseñadora'),
(5, 'Carlos', 'Rodríguez', '29567890', '20295678901', '1982-02-14', 'carlos.rodriguez@outlook.com', '3513456789', 'Rivadavia 202', 1, NULL);

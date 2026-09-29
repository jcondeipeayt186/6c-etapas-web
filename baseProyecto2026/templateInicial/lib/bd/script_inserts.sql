/*
    SCRIPT DE INSERTS - 20 CIUDADES + 50 PERSONAS
    ===============================================
    Este script puebla la base de datos con datos de ejemplo para probar
    filtros, listados, JOINs y estadísticas.

    Orden importante: primero las CIUDADES (porque las personas dependen de ellas vía ciudad_id)
    luego las PERSONAS.

    Ejecutar DESPUÉS de script.sql:
      mysql -u root -p contactos3 < script_inserts.sql
    o pegarlo en phpMyAdmin (pestaña SQL) con la base contactos3 seleccionada.
*/

USE contactos3;

-- -------------------------------------------------------
-- 20 CIUDADES (Córdoba, Santa Fe, Buenos Aires, etc.)
-- -------------------------------------------------------
INSERT INTO ciudades (nombre, provincia, latitud, longitud, codigo_postal, descripcion, fecha_fundacion) VALUES
-- Córdoba (6)
('Río Cuarto', 'Córdoba', -33.123456, -64.349924, '5800', 'Ciudad del sur de Córdoba, capital alterna', '1786-11-11'),
('Córdoba', 'Córdoba', -31.420083, -64.188776, '5000', 'Capital de la provincia de Córdoba', '1573-07-06'),
('Villa María', 'Córdoba', -32.410000, -63.240000, '5900', 'Ciudad industrial del centro cordobés', '1867-09-27'),
('Alta Gracia', 'Córdoba', -31.658333, -64.430556, '5186', 'Ciudad del Tajamar', '1588-01-01'),
('Jesús María', 'Córdoba', -30.983333, -64.100000, '5220', 'Festival de Doma y Folklore', '1873-01-01'),
('La Carlota', 'Córdoba', -33.416667, -62.900000, '2670', 'Sureste cordobés', '1737-01-01'),
-- Santa Fe (3)
('Rosario', 'Santa Fe', -32.958702, -60.693900, '2000', 'Ciudad portuaria del Paraná', '1852-08-05'),
('Santa Fe', 'Santa Fe', -31.633333, -60.700000, '3000', 'Capital de Santa Fe', '1573-11-15'),
('Rafaela', 'Santa Fe', -31.250000, -61.483333, '2300', 'Perla del oeste santafesino', '1881-10-24'),
-- Buenos Aires (4)
('La Plata', 'Buenos Aires', -34.921450, -57.954530, '1900', 'Capital de Buenos Aires, ciudad de las diagonales', '1882-11-19'),
('Mar del Plata', 'Buenos Aires', -38.005477, -57.542610, '7600', 'Ciudad balnearia', '1874-02-10'),
('Bahía Blanca', 'Buenos Aires', -38.005000, -62.270000, '8000', 'Puerto del sur bonaerense', '1828-04-11'),
('Tandil', 'Buenos Aires', -37.316667, -59.150000, '7000', 'Sierras bonaerenses', '1823-04-04'),
-- Otras provincias (7)
('Mendoza', 'Mendoza', -32.889458, -68.845840, '5500', 'Capital del vino', '1561-03-02'),
('San Juan', 'San Juan', -31.537500, -68.536390, '5400', 'Tierra del sol', '1562-06-13'),
('San Luis', 'San Luis', -33.295010, -66.335630, '5700', 'Capital puntana', '1594-08-25'),
('Tucumán', 'Tucumán', -26.808285, -65.217590, '4000', 'Jardín de la República', '1565-05-31'),
('Salta', 'Salta', -24.782127, -65.423198, '4400', 'La linda', '1582-04-16'),
('Neuquén', 'Neuquén', -38.005477, -68.053680, '8300', 'Capital de la Patagonia', '1904-09-12'),
('Bariloche', 'Río Negro', -41.133472, -71.310360, '8400', 'Ciudad andina, turismo y chocolate', '1902-05-03');

-- -------------------------------------------------------
-- 50 PERSONAS (cada una con ciudad_id entre 1 y 20)
-- -------------------------------------------------------
INSERT INTO personas (nombre, apellido, dni, cuit, fecha_nacimiento, email, telefono, direccion, ciudad_id, observaciones) VALUES
('Julián', 'Conde', '30090561', '20300905618', '1983-04-10', 'jconde@ac.unrc.edu.ar', '3584123456', 'Av. San Martín 123', 1, 'Docente'),
('María', 'Pérez', '31333456', '27313334569', '1987-05-12', 'maria.perez@gmail.com', '3534111579', 'Belgrano 456', 2, 'Estudiante'),
('Juan', 'Gómez', '28456123', '20284561230', '1980-11-03', 'juan.gomez@hotmail.com', '3516789012', 'Colón 789', 2, NULL),
('Lucía', 'Fernández', '35123456', '27351234568', '1990-07-22', 'lucia.fernandez@gmail.com', '3512345678', 'Gral. Paz 101', 3, 'Diseñadora'),
('Carlos', 'Rodríguez', '29567890', '20295678901', '1982-02-14', 'carlos.rodriguez@outlook.com', '3513456789', 'Rivadavia 202', 1, NULL),
('Ana', 'Martínez', '33890123', '27338901234', '1988-09-30', 'ana.martinez@gmail.com', '3514567890', 'Sarmiento 303', 4, 'Médica'),
('Pedro', 'López', '27456890', '20274568902', '1979-12-05', 'pedro.lopez@yahoo.com', '3515678901', 'Mitre 404', 5, NULL),
('Sofía', 'González', '36234567', '27362345679', '1992-03-18', 'sofia.gonzalez@gmail.com', '3416789012', 'San Martín 505', 7, 'Abogada'),
('Diego', 'Sánchez', '30123456', '20301234567', '1983-06-25', 'diego.sanchez@gmail.com', '3417890123', 'Córdoba 606', 7, NULL),
('Valentina', 'Díaz', '34345678', '27343456780', '1989-10-11', 'valentina.diaz@gmail.com', '3418901234', 'Santa Fe 707', 8, NULL),
('Matías', 'Torres', '28901234', '20289012345', '1981-01-20', 'matias.torres@hotmail.com', '2219012345', 'Calle 12 808', 10, NULL),
('Florencia', 'Ramírez', '35678901', '27356789012', '1991-08-07', 'flor.ramirez@gmail.com', '2210123456', 'Calle 53 909', 10, 'Arquitecta'),
('Agustín', 'Flores', '31234567', '20312345678', '1986-04-14', 'agustin.flores@gmail.com', '2231234567', 'Güemes 110', 11, NULL),
('Camila', 'Acosta', '33456789', '27334567890', '1988-12-28', 'camila.acosta@gmail.com', '2232345678', 'Colón 220', 11, NULL),
('Facundo', 'Herrera', '29876543', '20298765432', '1982-05-09', 'facundo.herrera@outlook.com', '2913456789', 'Alsina 330', 12, NULL),
('Julieta', 'Medina', '34567890', '27345678901', '1989-06-15', 'julieta.medina@gmail.com', '2914567890', 'Brown 440', 12, NULL),
('Santiago', 'Ruiz', '27654321', '20276543210', '1979-03-27', 'santiago.ruiz@gmail.com', '2615678901', 'San Martín 550', 14, 'Enólogo'),
('Martina', 'Alvarez', '35901234', '27359012345', '1992-09-19', 'martina.alvarez@gmail.com', '2616789012', 'Belgrano 660', 14, NULL),
('Nicolás', 'Giménez', '30567890', '20305678901', '1984-11-11', 'nicolas.gimenez@hotmail.com', '2647890123', 'Libertador 770', 15, NULL),
('Felicitas', 'Morales', '32654321', '27326543210', '1987-02-03', 'felicitas.morales@gmail.com', '2648901234', 'Rivadavia 880', 15, NULL),
('Tomás', 'Vargas', '31890123', '20318901234', '1986-07-07', 'tomas.vargas@gmail.com', '2669012345', 'Illia 990', 16, NULL),
('Pilar', 'Castro', '33789012', '27337890123', '1988-04-22', 'pilar.castro@gmail.com', '2660123456', 'San Martín 111', 16, NULL),
('Gonzalo', 'Ortiz', '29456789', '20294567890', '1982-10-30', 'gonzalo.ortiz@yahoo.com', '3811234567', '24 de Septiembre 222', 17, NULL),
('Micaela', 'Silva', '34890123', '27348901234', '1990-01-08', 'micaela.silva@gmail.com', '3812345678', 'Av. Sarmiento 333', 17, NULL),
('Joaquín', 'Rojas', '28765432', '20287654321', '1980-08-16', 'joaquin.rojas@gmail.com', '3873456789', 'Mitre 444', 18, 'Guía turística'),
('Abril', 'Mendoza', '35234567', '27352345678', '1991-05-05', 'abril.mendoza@gmail.com', '3874567890', 'Belgrano 555', 18, NULL),
('Ramiro', 'Sosa', '31456789', '20314567890', '1986-09-12', 'ramiro.sosa@hotmail.com', '2995678901', 'Av. Argentina 666', 19, NULL),
('Victoria', 'Guzmán', '32901234', '27329012345', '1987-11-25', 'victoria.guzman@gmail.com', '2996789012', 'Sarmiento 777', 19, NULL),
('Luciano', 'Navarro', '28345678', '20283456789', '1980-06-30', 'luciano.navarro@gmail.com', '2944123456', 'Mitre 888', 20, 'Chocolatero'),
('Candela', 'Peña', '35567890', '27355678901', '1991-03-03', 'candela.pena@gmail.com', '2944234567', 'Moreno 999', 20, NULL),
('Emiliano', 'Ríos', '30234567', '20302345678', '1983-12-19', 'emiliano.rios@gmail.com', '3584345678', 'Alberdi 1010', 1, NULL),
('Delfina', 'Leiva', '34678901', '27346789012', '1989-07-14', 'delfina.leiva@gmail.com', '3585456789', 'Sobremonte 1111', 1, NULL),
('Franco', 'Domínguez', '31901234', '20319012345', '1986-02-28', 'franco.dominguez@outlook.com', '3516567890', 'Chacabuco 1212', 2, NULL),
('Morena', 'Farias', '33801234', '27338012345', '1988-05-20', 'morena.farias@gmail.com', '3517678901', 'Ituzaingó 1313', 2, NULL),
('Thiago', 'Cabrera', '32234567', '20322345678', '1987-08-11', 'thiago.cabrera@gmail.com', '3518789012', 'Lima 1414', 3, NULL),
('Isabella', 'Molina', '35345678', '27353456789', '1991-10-02', 'isabella.molina@gmail.com', '3519890123', 'Alvear 1515', 3, 'Chef'),
('Benjamín', 'Suárez', '29765432', '20297654321', '1982-03-15', 'benjamin.suarez@hotmail.com', '3410901234', 'Pellegrini 1616', 7, NULL),
('Juana', 'Ponce', '33901234', '27339012345', '1988-07-09', 'juana.ponce@gmail.com', '3411012345', 'Oroño 1717', 7, NULL),
('Ignacio', 'Reyes', '28654321', '20286543210', '1980-09-23', 'ignacio.reyes@yahoo.com', '2212123456', '7 y 50 1818', 10, NULL),
('Lola', 'Aguirre', '34789012', '27347890123', '1990-02-11', 'lola.aguirre@gmail.com', '2213234567', 'Cantilo 1919', 10, NULL),
('Bautista', 'Ojeda', '31567890', '20315678901', '1986-11-30', 'bautista.ojeda@gmail.com', '2234345678', 'Alberti 2020', 11, NULL),
('Uma', 'Peralta', '34123456', '27341234567', '1989-01-25', 'uma.peralta@gmail.com', '2235456789', 'Luro 2121', 11, NULL),
('Felipe', 'Luna', '29345678', '20293456789', '1982-12-12', 'felipe.luna@outlook.com', '2916567890', 'Chiclana 2222', 12, NULL),
('Renata', 'Campos', '34234567', '27342345678', '1989-04-05', 'renata.campos@gmail.com', '2917678901', 'Vieytes 2323', 12, NULL),
('Jeronimo', 'Bustos', '30456789', '20304567890', '1984-05-18', 'jeronimo.bustos@gmail.com', '2618789012', 'Sarmiento 2424', 14, 'Sommelier'),
('Alma', 'Vega', '35789012', '27357890123', '1991-06-29', 'alma.vega@gmail.com', '2619890123', 'Arístides 2525', 14, NULL),
('Máximo', 'Coronel', '29654321', '20296543210', '1982-01-05', 'maximo.coronel@hotmail.com', '2640123456', 'Ig. de la Roza 2626', 15, NULL),
('Amparo', 'Godoy', '34901234', '27349012345', '1990-08-20', 'amparo.godoy@gmail.com', '2641234567', 'Central 2727', 15, NULL),
('Ciro', 'Fuentes', '31789012', '20317890123', '1986-10-17', 'ciro.fuentes@gmail.com', '2662345678', 'Pringles 2828', 16, NULL),
('Helena', 'Chávez', '33678901', '27336789012', '1988-03-11', 'helena.chavez@gmail.com', '2663456789', 'Junín 2929', 16, NULL);

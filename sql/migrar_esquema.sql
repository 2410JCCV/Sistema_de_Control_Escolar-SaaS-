-- =============================================
-- Script de Migración - Sistema de Control Escolar
-- Migrar de esquema simple a esquema completo
-- =============================================

USE sistema_escolar;

-- =============================================
-- 1. Crear tabla de usuarios si no existe
-- =============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    rol ENUM('admin', 'profesor', 'estudiante') NOT NULL DEFAULT 'estudiante',
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- 2. Crear tabla de grados si no existe
-- =============================================
CREATE TABLE IF NOT EXISTS grados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 3. Actualizar tabla de grupos
-- =============================================
-- Primero, crear la nueva estructura de grupos
CREATE TABLE IF NOT EXISTS grupos_nuevos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(10) NOT NULL,
    grado_id INT NOT NULL,
    capacidad INT DEFAULT 30,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grado_id) REFERENCES grados(id) ON DELETE CASCADE
);

-- =============================================
-- 4. Crear tabla de aulas
-- =============================================
CREATE TABLE IF NOT EXISTS aulas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    ubicacion VARCHAR(100),
    capacidad INT DEFAULT 30,
    tipo ENUM('aula', 'laboratorio', 'biblioteca', 'gimnasio') NOT NULL DEFAULT 'aula',
    estado ENUM('activo', 'inactivo', 'mantenimiento') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 5. Actualizar tabla de materias
-- =============================================
CREATE TABLE IF NOT EXISTS materias_nuevas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    creditos INT DEFAULT 1,
    grado_id INT NOT NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grado_id) REFERENCES grados(id) ON DELETE CASCADE
);

-- =============================================
-- 6. Actualizar tabla de profesores
-- =============================================
CREATE TABLE IF NOT EXISTS profesores_nuevos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100),
    fecha_nacimiento DATE,
    especialidad VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    fecha_ingreso DATE NOT NULL,
    salario DECIMAL(10,2),
    usuario_id INT,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- =============================================
-- 7. Actualizar tabla de estudiantes
-- =============================================
CREATE TABLE IF NOT EXISTS estudiantes_nuevos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100),
    fecha_nacimiento DATE,
    grado_id INT NOT NULL,
    grupo_id INT NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    nombre_tutor VARCHAR(200),
    telefono_tutor VARCHAR(20),
    usuario_id INT,
    estado ENUM('activo', 'inactivo', 'egresado') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (grado_id) REFERENCES grados(id) ON DELETE CASCADE,
    FOREIGN KEY (grupo_id) REFERENCES grupos_nuevos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- =============================================
-- 8. Crear tabla de horarios
-- =============================================
CREATE TABLE IF NOT EXISTS horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materia_id INT NOT NULL,
    profesor_id INT NOT NULL,
    grupo_id INT NOT NULL,
    aula_id INT NOT NULL,
    dia_semana ENUM('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (materia_id) REFERENCES materias_nuevas(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES profesores_nuevos(id) ON DELETE CASCADE,
    FOREIGN KEY (grupo_id) REFERENCES grupos_nuevos(id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aulas(id) ON DELETE CASCADE
);

-- =============================================
-- 9. Crear tabla de calificaciones
-- =============================================
CREATE TABLE IF NOT EXISTS calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    materia_id INT NOT NULL,
    profesor_id INT NOT NULL,
    tipo_evaluacion ENUM('examen', 'tarea', 'proyecto', 'participacion', 'practica') NOT NULL,
    calificacion DECIMAL(5,2) NOT NULL,
    fecha_evaluacion DATE NOT NULL,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes_nuevos(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias_nuevas(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES profesores_nuevos(id) ON DELETE CASCADE
);

-- =============================================
-- 10. Crear tabla de asistencias
-- =============================================
CREATE TABLE IF NOT EXISTS asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    materia_id INT NOT NULL,
    profesor_id INT NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('presente', 'ausente', 'justificado', 'tardanza') NOT NULL,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes_nuevos(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias_nuevas(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES profesores_nuevos(id) ON DELETE CASCADE
);

-- =============================================
-- 11. Crear tabla de notificaciones
-- =============================================
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'warning', 'success', 'danger') NOT NULL DEFAULT 'info',
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- =============================================
-- 12. Crear tabla de configuraciones
-- =============================================
CREATE TABLE IF NOT EXISTS configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descripcion TEXT,
    tipo ENUM('texto', 'numero', 'booleano', 'json') NOT NULL DEFAULT 'texto',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- 13. Insertar datos básicos
-- =============================================

-- Insertar grados
INSERT IGNORE INTO grados (nombre, descripcion) VALUES
('1° Grado', 'Primer grado de educación primaria'),
('2° Grado', 'Segundo grado de educación primaria'),
('3° Grado', 'Tercer grado de educación primaria'),
('4° Grado', 'Cuarto grado de educación primaria'),
('5° Grado', 'Quinto grado de educación primaria'),
('6° Grado', 'Sexto grado de educación primaria');

-- Insertar grupos
INSERT IGNORE INTO grupos_nuevos (nombre, grado_id, capacidad) VALUES
('A', 1, 30), ('B', 1, 30),
('A', 2, 30), ('B', 2, 30),
('A', 3, 30), ('B', 3, 30),
('A', 4, 30), ('B', 4, 30),
('A', 5, 30), ('B', 5, 30),
('A', 6, 30), ('B', 6, 30);

-- Insertar aulas
INSERT IGNORE INTO aulas (nombre, ubicacion, capacidad, tipo) VALUES
('Aula 101', 'Primer piso - Ala Norte', 30, 'aula'),
('Aula 102', 'Primer piso - Ala Norte', 30, 'aula'),
('Aula 103', 'Primer piso - Ala Norte', 30, 'aula'),
('Aula 201', 'Segundo piso - Ala Norte', 30, 'aula'),
('Aula 202', 'Segundo piso - Ala Norte', 30, 'aula'),
('Aula 203', 'Segundo piso - Ala Norte', 30, 'aula'),
('Laboratorio de Ciencias', 'Segundo piso - Ala Sur', 25, 'laboratorio'),
('Sala de Computación', 'Primer piso - Ala Sur', 20, 'laboratorio'),
('Biblioteca', 'Planta baja - Ala Central', 50, 'biblioteca'),
('Gimnasio', 'Planta baja - Ala Este', 100, 'gimnasio');

-- Insertar usuario administrador
INSERT IGNORE INTO usuarios (username, password, email, nombre, apellido, rol) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@escuela.com', 'Administrador', 'Sistema', 'admin');

-- =============================================
-- 14. Crear índices para mejorar el rendimiento
-- =============================================
CREATE INDEX IF NOT EXISTS idx_estudiantes_matricula ON estudiantes_nuevos(matricula);
CREATE INDEX IF NOT EXISTS idx_estudiantes_grado_grupo ON estudiantes_nuevos(grado_id, grupo_id);
CREATE INDEX IF NOT EXISTS idx_profesores_codigo ON profesores_nuevos(codigo);
CREATE INDEX IF NOT EXISTS idx_calificaciones_estudiante_materia ON calificaciones(estudiante_id, materia_id);
CREATE INDEX IF NOT EXISTS idx_asistencias_estudiante_fecha ON asistencias(estudiante_id, fecha);
CREATE INDEX IF NOT EXISTS idx_horarios_dia_hora ON horarios(dia_semana, hora_inicio);
CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario_leida ON notificaciones(usuario_id, leida);

-- =============================================
-- 15. Renombrar tablas (reemplazar las antiguas)
-- =============================================

-- Hacer backup de las tablas antiguas
RENAME TABLE estudiantes TO estudiantes_backup;
RENAME TABLE profesores TO profesores_backup;
RENAME TABLE materias TO materias_backup;
RENAME TABLE grupos TO grupos_backup;

-- Renombrar las nuevas tablas
RENAME TABLE estudiantes_nuevos TO estudiantes;
RENAME TABLE profesores_nuevos TO profesores;
RENAME TABLE materias_nuevas TO materias;
RENAME TABLE grupos_nuevos TO grupos;

-- =============================================
-- 16. Migrar datos existentes (si los hay)
-- =============================================

-- Migrar estudiantes existentes
INSERT INTO estudiantes (matricula, nombre, apellido_paterno, apellido_materno, grado_id, grupo_id, email, estado)
SELECT 
    CONCAT('EST', LPAD(id, 3, '0')) as matricula,
    nombre,
    '' as apellido_paterno,
    '' as apellido_materno,
    CASE 
        WHEN grupo LIKE '1%' THEN 1
        WHEN grupo LIKE '2%' THEN 2
        WHEN grupo LIKE '3%' THEN 3
        WHEN grupo LIKE '4%' THEN 4
        WHEN grupo LIKE '5%' THEN 5
        WHEN grupo LIKE '6%' THEN 6
        ELSE 1
    END as grado_id,
    CASE 
        WHEN grupo LIKE '%A' THEN 1
        WHEN grupo LIKE '%B' THEN 2
        ELSE 1
    END as grupo_id,
    email,
    'activo' as estado
FROM estudiantes_backup
WHERE nombre IS NOT NULL;

-- Migrar profesores existentes
INSERT INTO profesores (codigo, nombre, apellido_paterno, especialidad, email, fecha_ingreso, estado)
SELECT 
    CONCAT('PROF', LPAD(id, 3, '0')) as codigo,
    nombre,
    '' as apellido_paterno,
    COALESCE(materia, 'General') as especialidad,
    email,
    CURDATE() as fecha_ingreso,
    'activo' as estado
FROM profesores_backup
WHERE nombre IS NOT NULL;

-- Migrar materias existentes
INSERT INTO materias (codigo, nombre, grado_id, creditos, estado)
SELECT 
    COALESCE(codigo, CONCAT('MAT-', LPAD(id, 3, '0'))) as codigo,
    nombre,
    CASE 
        WHEN nivel = 'Primaria' THEN 1
        WHEN nivel = 'Secundaria' THEN 6
        ELSE 1
    END as grado_id,
    COALESCE(creditos, 1) as creditos,
    'activo' as estado
FROM materias_backup
WHERE nombre IS NOT NULL;

-- =============================================
-- 17. Limpiar tablas de backup (opcional)
-- =============================================
-- DROP TABLE IF EXISTS estudiantes_backup;
-- DROP TABLE IF EXISTS profesores_backup;
-- DROP TABLE IF EXISTS materias_backup;
-- DROP TABLE IF EXISTS grupos_backup;

-- =============================================
-- 18. Insertar configuraciones del sistema
-- =============================================
INSERT IGNORE INTO configuraciones (clave, valor, descripcion, tipo) VALUES
('nombre_escuela', 'Escuela Primaria "Benito Juárez"', 'Nombre oficial de la institución educativa', 'texto'),
('direccion_escuela', 'Av. Principal #123, Col. Centro, Ciudad, Estado', 'Dirección completa de la escuela', 'texto'),
('telefono_escuela', '555-0000', 'Teléfono principal de la escuela', 'texto'),
('email_escuela', 'contacto@escuela.com', 'Correo electrónico de contacto', 'texto'),
('director_escuela', 'Lic. María Elena González', 'Nombre del director(a) de la escuela', 'texto'),
('ciclo_escolar_actual', '2024-2025', 'Ciclo escolar vigente', 'texto'),
('calificacion_minima', '6.0', 'Calificación mínima aprobatoria', 'numero'),
('calificacion_maxima', '10.0', 'Calificación máxima posible', 'numero'),
('porcentaje_asistencia_minimo', '80', 'Porcentaje mínimo de asistencia requerido', 'numero'),
('dias_clase_por_semana', '5', 'Número de días de clase por semana', 'numero'),
('horas_clase_por_dia', '6', 'Número de horas de clase por día', 'numero'),
('activo', '1', 'Estado del sistema (1=activo, 0=inactivo)', 'booleano');

-- =============================================
-- Mensaje de finalización
-- =============================================
SELECT 'Migración completada exitosamente. El sistema ahora está listo para agregar estudiantes.' as mensaje;

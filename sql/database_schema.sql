-- =============================================
-- Sistema de Control Escolar - Base de Datos
-- =============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS sistema_escolar 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE sistema_escolar;

-- =============================================
-- Tabla de usuarios del sistema
-- =============================================
CREATE TABLE usuarios (
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
-- Tabla de grados
-- =============================================
CREATE TABLE grados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- Tabla de grupos
-- =============================================
CREATE TABLE grupos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(10) NOT NULL,
    grado_id INT NOT NULL,
    capacidad INT DEFAULT 30,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grado_id) REFERENCES grados(id) ON DELETE CASCADE
);

-- =============================================
-- Tabla de aulas
-- =============================================
CREATE TABLE aulas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    ubicacion VARCHAR(100),
    capacidad INT DEFAULT 30,
    tipo ENUM('aula', 'laboratorio', 'biblioteca', 'gimnasio') NOT NULL DEFAULT 'aula',
    estado ENUM('activo', 'inactivo', 'mantenimiento') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- Tabla de materias
-- =============================================
CREATE TABLE materias (
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
-- Tabla de profesores
-- =============================================
CREATE TABLE profesores (
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
-- Tabla de estudiantes
-- =============================================
CREATE TABLE estudiantes (
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
    FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- =============================================
-- Tabla de horarios
-- =============================================
CREATE TABLE horarios (
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
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE CASCADE,
    FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aulas(id) ON DELETE CASCADE
);

-- =============================================
-- Tabla de calificaciones
-- =============================================
CREATE TABLE calificaciones (
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
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE CASCADE
);

-- =============================================
-- Tabla de asistencias
-- =============================================
CREATE TABLE asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    materia_id INT NOT NULL,
    profesor_id INT NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('presente', 'ausente', 'justificado', 'tardanza') NOT NULL,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE CASCADE
);

-- =============================================
-- Tabla de notificaciones
-- =============================================
CREATE TABLE notificaciones (
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
-- Tabla de configuraciones del sistema
-- =============================================
CREATE TABLE configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descripcion TEXT,
    tipo ENUM('texto', 'numero', 'booleano', 'json') NOT NULL DEFAULT 'texto',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- Índices para mejorar el rendimiento
-- =============================================
CREATE INDEX idx_estudiantes_matricula ON estudiantes(matricula);
CREATE INDEX idx_estudiantes_grado_grupo ON estudiantes(grado_id, grupo_id);
CREATE INDEX idx_profesores_codigo ON profesores(codigo);
CREATE INDEX idx_calificaciones_estudiante_materia ON calificaciones(estudiante_id, materia_id);
CREATE INDEX idx_asistencias_estudiante_fecha ON asistencias(estudiante_id, fecha);
CREATE INDEX idx_horarios_dia_hora ON horarios(dia_semana, hora_inicio);
CREATE INDEX idx_notificaciones_usuario_leida ON notificaciones(usuario_id, leida);


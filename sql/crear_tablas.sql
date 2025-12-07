-- Script para crear las tablas del Sistema de Control Escolar
-- Ejecutar en phpMyAdmin

CREATE DATABASE IF NOT EXISTS sistema_escolar;
USE sistema_escolar;

-- Tabla de estudiantes
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    grupo VARCHAR(20),
    promedio DECIMAL(3,1) DEFAULT 0.0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de profesores
CREATE TABLE IF NOT EXISTS profesores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    materia VARCHAR(100),
    grupos INT DEFAULT 0,
    estudiantes INT DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de materias
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(20) UNIQUE,
    nivel VARCHAR(50),
    creditos INT DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de grupos
CREATE TABLE IF NOT EXISTS grupos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    nivel VARCHAR(50),
    estudiantes INT DEFAULT 0,
    profesor VARCHAR(100),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar datos de ejemplo
INSERT INTO estudiantes (nombre, email, grupo, promedio) VALUES
('Ana García López', 'ana.garcia@escuela.com', '1° A', 9.0),
('Carlos Méndez Ruiz', 'carlos.mendez@escuela.com', '1° A', 8.4),
('María Fernández Torres', 'maria.fernandez@escuela.com', '1° A', 9.6),
('Luis Rodríguez Pérez', 'luis.rodriguez@escuela.com', '1° A', 7.5),
('Sofía Hernández Jiménez', 'sofia.hernandez@escuela.com', '1° A', 9.1),
('Juan Pérez García', 'juan.perez@escuela.com', '1° B', 8.5),
('María González López', 'maria.gonzalez@escuela.com', '1° B', 9.2),
('Carlos Rodríguez Martín', 'carlos.rodriguez@escuela.com', '1° B', 7.8),
('Ana Martínez Silva', 'ana.martinez@escuela.com', '2° A', 8.9),
('Luis Fernández Torres', 'luis.fernandez@escuela.com', '2° B', 8.1);

INSERT INTO profesores (nombre, email, materia, grupos, estudiantes) VALUES
('María González', 'maria.gonzalez@escuela.com', 'Matemáticas', 2, 48),
('Juan Pérez', 'juan.perez@escuela.com', 'Ciencias', 2, 53),
('Ana Martínez', 'ana.martinez@escuela.com', 'Historia', 2, 53),
('Carlos López', 'carlos.lopez@escuela.com', 'Educación Física', 2, 48);

INSERT INTO materias (nombre, codigo, nivel, creditos) VALUES
('Matemáticas', 'MAT-101', 'Primaria', 4),
('Español', 'ESP-102', 'Primaria', 4),
('Ciencias Naturales', 'CIE-103', 'Primaria', 3),
('Historia', 'HIS-104', 'Primaria', 3),
('Educación Física', 'EDF-105', 'Primaria', 2),
('Matemáticas Avanzadas', 'MAT-201', 'Secundaria', 5),
('Ciencias Físicas', 'CIE-201', 'Secundaria', 4);

INSERT INTO grupos (nombre, nivel, estudiantes, profesor) VALUES
('1° A - Primaria', 'Primaria', 25, 'María González'),
('1° B - Primaria', 'Primaria', 23, 'Juan Pérez'),
('2° A - Primaria', 'Primaria', 28, 'Ana Martínez'),
('2° B - Primaria', 'Primaria', 24, 'Carlos López');

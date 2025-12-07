-- =============================================
-- Sistema de Control Escolar - Datos de Ejemplo
-- =============================================

USE sistema_escolar;

-- =============================================
-- Insertar grados
-- =============================================
INSERT INTO grados (nombre, descripcion) VALUES
('1° Grado', 'Primer grado de educación primaria'),
('2° Grado', 'Segundo grado de educación primaria'),
('3° Grado', 'Tercer grado de educación primaria'),
('4° Grado', 'Cuarto grado de educación primaria'),
('5° Grado', 'Quinto grado de educación primaria'),
('6° Grado', 'Sexto grado de educación primaria');

-- =============================================
-- Insertar grupos
-- =============================================
INSERT INTO grupos (nombre, grado_id, capacidad) VALUES
('A', 1, 30),
('B', 1, 30),
('A', 2, 30),
('B', 2, 30),
('A', 3, 30),
('B', 3, 30),
('A', 4, 30),
('B', 4, 30),
('A', 5, 30),
('B', 5, 30),
('A', 6, 30),
('B', 6, 30);

-- =============================================
-- Insertar aulas
-- =============================================
INSERT INTO aulas (nombre, ubicacion, capacidad, tipo) VALUES
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

-- =============================================
-- Insertar materias
-- =============================================
INSERT INTO materias (codigo, nombre, descripcion, creditos, grado_id) VALUES
-- 1° Grado
('MAT-1', 'Matemáticas I', 'Fundamentos de matemáticas para primer grado', 1, 1),
('ESP-1', 'Español I', 'Lengua y literatura para primer grado', 1, 1),
('CNS-1', 'Ciencias Naturales I', 'Introducción a las ciencias naturales', 1, 1),
('HIS-1', 'Historia I', 'Historia local y familiar', 1, 1),
('EDF-1', 'Educación Física I', 'Desarrollo físico y deportes', 1, 1),

-- 2° Grado
('MAT-2', 'Matemáticas II', 'Matemáticas para segundo grado', 1, 2),
('ESP-2', 'Español II', 'Lengua y literatura para segundo grado', 1, 2),
('CNS-2', 'Ciencias Naturales II', 'Ciencias naturales para segundo grado', 1, 2),
('HIS-2', 'Historia II', 'Historia regional', 1, 2),
('EDF-2', 'Educación Física II', 'Educación física para segundo grado', 1, 2),

-- 3° Grado
('MAT-3', 'Matemáticas III', 'Matemáticas para tercer grado', 1, 3),
('ESP-3', 'Español III', 'Lengua y literatura para tercer grado', 1, 3),
('CNS-3', 'Ciencias Naturales III', 'Ciencias naturales para tercer grado', 1, 3),
('HIS-3', 'Historia III', 'Historia nacional', 1, 3),
('EDF-3', 'Educación Física III', 'Educación física para tercer grado', 1, 3),

-- 4° Grado
('MAT-4', 'Matemáticas IV', 'Matemáticas para cuarto grado', 1, 4),
('ESP-4', 'Español IV', 'Lengua y literatura para cuarto grado', 1, 4),
('CNS-4', 'Ciencias Naturales IV', 'Ciencias naturales para cuarto grado', 1, 4),
('HIS-4', 'Historia IV', 'Historia universal', 1, 4),
('EDF-4', 'Educación Física IV', 'Educación física para cuarto grado', 1, 4),

-- 5° Grado
('MAT-5', 'Matemáticas V', 'Matemáticas para quinto grado', 1, 5),
('ESP-5', 'Español V', 'Lengua y literatura para quinto grado', 1, 5),
('CNS-5', 'Ciencias Naturales V', 'Ciencias naturales para quinto grado', 1, 5),
('HIS-5', 'Historia V', 'Historia de México', 1, 5),
('EDF-5', 'Educación Física V', 'Educación física para quinto grado', 1, 5),

-- 6° Grado
('MAT-6', 'Matemáticas VI', 'Matemáticas para sexto grado', 1, 6),
('ESP-6', 'Español VI', 'Lengua y literatura para sexto grado', 1, 6),
('CNS-6', 'Ciencias Naturales VI', 'Ciencias naturales para sexto grado', 1, 6),
('HIS-6', 'Historia VI', 'Historia contemporánea', 1, 6),
('EDF-6', 'Educación Física VI', 'Educación física para sexto grado', 1, 6);

-- =============================================
-- Insertar usuarios del sistema
-- =============================================
INSERT INTO usuarios (username, password, email, nombre, apellido, rol) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@escuela.com', 'Administrador', 'Sistema', 'admin'),
('prof1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ana.garcia@escuela.com', 'Ana', 'García López', 'profesor'),
('prof2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'roberto.martinez@escuela.com', 'Roberto', 'Martínez Silva', 'profesor'),
('prof3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'carmen.rodriguez@escuela.com', 'Carmen', 'Rodríguez Pérez', 'profesor'),
('est1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'juan.perez@email.com', 'Juan', 'Pérez García', 'estudiante'),
('est2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maria.lopez@email.com', 'María', 'López Rodríguez', 'estudiante');

-- =============================================
-- Insertar profesores
-- =============================================
INSERT INTO profesores (codigo, nombre, apellido_paterno, apellido_materno, especialidad, telefono, email, fecha_ingreso, salario, usuario_id) VALUES
('PROF001', 'Ana', 'García', 'López', 'Matemáticas', '555-1001', 'ana.garcia@escuela.com', '2020-08-15', 15000.00, 2),
('PROF002', 'Roberto', 'Martínez', 'Silva', 'Español', '555-1002', 'roberto.martinez@escuela.com', '2020-08-20', 15000.00, 3),
('PROF003', 'Carmen', 'Rodríguez', 'Pérez', 'Ciencias', '555-1003', 'carmen.rodriguez@escuela.com', '2020-09-10', 15000.00, 4);

-- =============================================
-- Insertar estudiantes
-- =============================================
INSERT INTO estudiantes (matricula, nombre, apellido_paterno, apellido_materno, grado_id, grupo_id, telefono, email, nombre_tutor, telefono_tutor, usuario_id) VALUES
('EST001', 'Juan', 'Pérez', 'García', 3, 5, '555-0123', 'juan.perez@email.com', 'Carlos Pérez', '555-0124', 5),
('EST002', 'María', 'López', 'Rodríguez', 3, 6, '555-0125', 'maria.lopez@email.com', 'Elena López', '555-0126', 6),
('EST003', 'Carlos', 'Hernández', 'Silva', 4, 7, '555-0127', 'carlos.hernandez@email.com', 'Roberto Hernández', '555-0128', NULL),
('EST004', 'Ana', 'González', 'Morales', 4, 7, '555-0129', 'ana.gonzalez@email.com', 'Patricia González', '555-0130', NULL),
('EST005', 'Luis', 'Ramírez', 'Castro', 5, 9, '555-0131', 'luis.ramirez@email.com', 'Miguel Ramírez', '555-0132', NULL),
('EST006', 'Sofia', 'Torres', 'Vega', 5, 9, '555-0133', 'sofia.torres@email.com', 'Isabel Torres', '555-0134', NULL),
('EST007', 'Diego', 'Mendoza', 'Ruiz', 6, 11, '555-0135', 'diego.mendoza@email.com', 'Fernando Mendoza', '555-0136', NULL),
('EST008', 'Valentina', 'Jiménez', 'Herrera', 6, 11, '555-0137', 'valentina.jimenez@email.com', 'Carmen Jiménez', '555-0138', NULL);

-- =============================================
-- Insertar horarios
-- =============================================
INSERT INTO horarios (materia_id, profesor_id, grupo_id, aula_id, dia_semana, hora_inicio, hora_fin) VALUES
-- Lunes
(11, 1, 5, 1, 'lunes', '08:00:00', '09:00:00'), -- Matemáticas III - Grupo 3A
(12, 2, 5, 2, 'lunes', '09:00:00', '10:00:00'), -- Español III - Grupo 3A
(13, 3, 5, 3, 'lunes', '10:00:00', '11:00:00'), -- Ciencias III - Grupo 3A

-- Martes
(11, 1, 5, 1, 'martes', '08:00:00', '09:00:00'), -- Matemáticas III - Grupo 3A
(14, 2, 5, 2, 'martes', '09:00:00', '10:00:00'), -- Historia III - Grupo 3A
(15, 3, 5, 4, 'martes', '10:00:00', '11:00:00'), -- Educación Física III - Grupo 3A

-- Miércoles
(12, 2, 5, 2, 'miercoles', '08:00:00', '09:00:00'), -- Español III - Grupo 3A
(13, 3, 5, 3, 'miercoles', '09:00:00', '10:00:00'), -- Ciencias III - Grupo 3A
(11, 1, 5, 1, 'miercoles', '10:00:00', '11:00:00'), -- Matemáticas III - Grupo 3A

-- Jueves
(14, 2, 5, 2, 'jueves', '08:00:00', '09:00:00'), -- Historia III - Grupo 3A
(15, 3, 5, 4, 'jueves', '09:00:00', '10:00:00'), -- Educación Física III - Grupo 3A
(11, 1, 5, 1, 'jueves', '10:00:00', '11:00:00'), -- Matemáticas III - Grupo 3A

-- Viernes
(12, 2, 5, 2, 'viernes', '08:00:00', '09:00:00'), -- Español III - Grupo 3A
(13, 3, 5, 3, 'viernes', '09:00:00', '10:00:00'), -- Ciencias III - Grupo 3A
(14, 2, 5, 2, 'viernes', '10:00:00', '11:00:00'); -- Historia III - Grupo 3A

-- =============================================
-- Insertar calificaciones de ejemplo
-- =============================================
INSERT INTO calificaciones (estudiante_id, materia_id, profesor_id, tipo_evaluacion, calificacion, fecha_evaluacion, observaciones) VALUES
-- Juan Pérez (EST001) - 3° Grado
(1, 11, 1, 'examen', 85.50, '2024-01-15', 'Buen desempeño en el examen'),
(1, 12, 2, 'examen', 92.00, '2024-01-16', 'Excelente trabajo'),
(1, 13, 3, 'examen', 78.50, '2024-01-17', 'Necesita mejorar en ciencias'),
(1, 11, 1, 'tarea', 90.00, '2024-01-20', 'Tarea completada correctamente'),
(1, 12, 2, 'proyecto', 88.00, '2024-01-25', 'Proyecto bien presentado'),

-- María López (EST002) - 3° Grado
(2, 11, 1, 'examen', 95.00, '2024-01-15', 'Excelente calificación'),
(2, 12, 2, 'examen', 89.50, '2024-01-16', 'Muy buen trabajo'),
(2, 13, 3, 'examen', 92.00, '2024-01-17', 'Destacada en ciencias'),
(2, 11, 1, 'tarea', 94.00, '2024-01-20', 'Tarea impecable'),
(2, 12, 2, 'proyecto', 91.00, '2024-01-25', 'Proyecto creativo'),

-- Carlos Hernández (EST003) - 4° Grado
(3, 16, 1, 'examen', 82.00, '2024-01-15', 'Buen desempeño'),
(3, 17, 2, 'examen', 87.50, '2024-01-16', 'Muy bien'),
(3, 18, 3, 'examen', 85.00, '2024-01-17', 'Satisfactorio'),
(3, 16, 1, 'tarea', 88.00, '2024-01-20', 'Tarea bien realizada'),
(3, 17, 2, 'proyecto', 86.00, '2024-01-25', 'Proyecto completo');

-- =============================================
-- Insertar asistencias de ejemplo
-- =============================================
INSERT INTO asistencias (estudiante_id, materia_id, profesor_id, fecha, estado, observaciones) VALUES
-- Juan Pérez - Enero 2024
(1, 11, 1, '2024-01-15', 'presente', ''),
(1, 12, 2, '2024-01-16', 'presente', ''),
(1, 13, 3, '2024-01-17', 'presente', ''),
(1, 11, 1, '2024-01-22', 'presente', ''),
(1, 12, 2, '2024-01-23', 'tardanza', 'Llegó 10 minutos tarde'),
(1, 13, 3, '2024-01-24', 'presente', ''),
(1, 11, 1, '2024-01-29', 'presente', ''),
(1, 12, 2, '2024-01-30', 'presente', ''),
(1, 13, 3, '2024-01-31', 'ausente', 'Falta justificada por enfermedad'),

-- María López - Enero 2024
(2, 11, 1, '2024-01-15', 'presente', ''),
(2, 12, 2, '2024-01-16', 'presente', ''),
(2, 13, 3, '2024-01-17', 'presente', ''),
(2, 11, 1, '2024-01-22', 'presente', ''),
(2, 12, 2, '2024-01-23', 'presente', ''),
(2, 13, 3, '2024-01-24', 'presente', ''),
(2, 11, 1, '2024-01-29', 'presente', ''),
(2, 12, 2, '2024-01-30', 'presente', ''),
(2, 13, 3, '2024-01-31', 'presente', '');

-- =============================================
-- Insertar configuraciones del sistema
-- =============================================
INSERT INTO configuraciones (clave, valor, descripcion, tipo) VALUES
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
-- Insertar notificaciones de ejemplo
-- =============================================
INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES
(1, 'Bienvenido al Sistema', 'Bienvenido al Sistema de Control Escolar. Su cuenta ha sido creada exitosamente.', 'success'),
(2, 'Nuevo Horario Asignado', 'Se le ha asignado un nuevo horario para la materia de Matemáticas.', 'info'),
(3, 'Recordatorio de Evaluación', 'Recuerde que mañana tiene programada una evaluación de Español.', 'warning'),
(4, 'Actualización de Datos', 'Por favor actualice su información personal en el sistema.', 'info'),
(5, 'Nueva Calificación', 'Se ha registrado una nueva calificación en su materia de Matemáticas.', 'info'),
(6, 'Asistencia Registrada', 'Su asistencia ha sido registrada correctamente para hoy.', 'success');


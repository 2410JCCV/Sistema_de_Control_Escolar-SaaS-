# 📋 Tablas para Agregar a la Base de Datos

Este documento contiene todas las tablas SQL necesarias para los nuevos módulos del sistema escolar.

## ⚠️ IMPORTANTE
Ejecuta estos scripts SQL en tu base de datos `sistema_escolar` antes de usar los nuevos módulos.

---

## 📚 1. Tabla: `libros` (Módulo Biblioteca)

```sql
CREATE TABLE libros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(200) NOT NULL,
    editorial VARCHAR(100),
    isbn VARCHAR(20),
    categoria VARCHAR(100),
    año_publicacion YEAR,
    cantidad_total INT NOT NULL DEFAULT 1,
    cantidad_disponible INT NOT NULL DEFAULT 1,
    ubicacion VARCHAR(100),
    descripcion TEXT,
    estado ENUM('disponible', 'prestado', 'reservado', 'mantenimiento') NOT NULL DEFAULT 'disponible',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_libros_codigo (codigo),
    INDEX idx_libros_titulo (titulo),
    INDEX idx_libros_autor (autor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📖 2. Tabla: `prestamos_libros` (Módulo Biblioteca)

```sql
CREATE TABLE prestamos_libros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libro_id INT NOT NULL,
    estudiante_id INT NOT NULL,
    profesor_id INT,
    fecha_prestamo DATE NOT NULL,
    fecha_devolucion_esperada DATE NOT NULL,
    fecha_devolucion_real DATE,
    estado ENUM('prestado', 'devuelto', 'vencido', 'perdido') NOT NULL DEFAULT 'prestado',
    observaciones TEXT,
    multa DECIMAL(10,2) DEFAULT 0.00,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE SET NULL,
    INDEX idx_prestamos_estudiante (estudiante_id),
    INDEX idx_prestamos_libro (libro_id),
    INDEX idx_prestamos_fecha (fecha_prestamo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📅 3. Tabla: `eventos` (Módulo Eventos/Actividades)

```sql
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tipo ENUM('academico', 'deportivo', 'cultural', 'social', 'reunion', 'otro') NOT NULL DEFAULT 'academico',
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME,
    ubicacion VARCHAR(200),
    organizador_id INT,
    grupo_id INT,
    grado_id INT,
    estado ENUM('programado', 'en_curso', 'finalizado', 'cancelado') NOT NULL DEFAULT 'programado',
    participantes_max INT,
    costo DECIMAL(10,2) DEFAULT 0.00,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizador_id) REFERENCES profesores(id) ON DELETE SET NULL,
    FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE SET NULL,
    FOREIGN KEY (grado_id) REFERENCES grados(id) ON DELETE SET NULL,
    INDEX idx_eventos_fecha (fecha_inicio),
    INDEX idx_eventos_tipo (tipo),
    INDEX idx_eventos_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 👥 4. Tabla: `participantes_eventos` (Módulo Eventos/Actividades)

```sql
CREATE TABLE participantes_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    estudiante_id INT,
    profesor_id INT,
    tipo_participante ENUM('estudiante', 'profesor', 'invitado') NOT NULL,
    nombre_invitado VARCHAR(200),
    asistio BOOLEAN DEFAULT FALSE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE CASCADE,
    INDEX idx_participantes_evento (evento_id),
    INDEX idx_participantes_estudiante (estudiante_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📦 5. Tabla: `inventario` (Módulo Inventario/Recursos)

```sql
CREATE TABLE inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    categoria ENUM('equipo', 'mobiliario', 'material', 'tecnologia', 'deportivo', 'otro') NOT NULL DEFAULT 'equipo',
    descripcion TEXT,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    cantidad_total INT NOT NULL DEFAULT 1,
    cantidad_disponible INT NOT NULL DEFAULT 1,
    ubicacion VARCHAR(200),
    estado_general ENUM('excelente', 'bueno', 'regular', 'malo', 'inutilizable') NOT NULL DEFAULT 'bueno',
    valor_estimado DECIMAL(10,2),
    fecha_adquisicion DATE,
    proveedor VARCHAR(200),
    numero_serie VARCHAR(100),
    observaciones TEXT,
    estado ENUM('disponible', 'en_uso', 'mantenimiento', 'perdido', 'dado_baja') NOT NULL DEFAULT 'disponible',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_inventario_codigo (codigo),
    INDEX idx_inventario_categoria (categoria),
    INDEX idx_inventario_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔄 6. Tabla: `movimientos_inventario` (Módulo Inventario/Recursos)

```sql
CREATE TABLE movimientos_inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventario_id INT NOT NULL,
    tipo_movimiento ENUM('prestamo', 'devolucion', 'mantenimiento', 'baja', 'traslado', 'ajuste') NOT NULL,
    usuario_id INT,
    estudiante_id INT,
    profesor_id INT,
    cantidad INT NOT NULL DEFAULT 1,
    fecha_movimiento DATETIME NOT NULL,
    fecha_devolucion_esperada DATETIME,
    ubicacion_anterior VARCHAR(200),
    ubicacion_nueva VARCHAR(200),
    observaciones TEXT,
    estado ENUM('activo', 'completado', 'cancelado') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventario_id) REFERENCES inventario(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE SET NULL,
    FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE SET NULL,
    INDEX idx_movimientos_inventario (inventario_id),
    INDEX idx_movimientos_fecha (fecha_movimiento),
    INDEX idx_movimientos_tipo (tipo_movimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 💰 7. Tabla: `pagos` (Módulo Pagos/Finanzas)

```sql
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_pago VARCHAR(50) NOT NULL UNIQUE,
    estudiante_id INT NOT NULL,
    concepto ENUM('matricula', 'mensualidad', 'inscripcion', 'materiales', 'actividad', 'multa', 'otro') NOT NULL,
    descripcion VARCHAR(200),
    monto DECIMAL(10,2) NOT NULL,
    monto_pagado DECIMAL(10,2) DEFAULT 0.00,
    fecha_vencimiento DATE,
    fecha_pago DATE,
    metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta', 'cheque', 'otro'),
    estado ENUM('pendiente', 'pagado', 'parcial', 'vencido', 'cancelado') NOT NULL DEFAULT 'pendiente',
    referencia_pago VARCHAR(100),
    observaciones TEXT,
    usuario_registro_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_pagos_estudiante (estudiante_id),
    INDEX idx_pagos_estado (estado),
    INDEX idx_pagos_fecha_vencimiento (fecha_vencimiento),
    INDEX idx_pagos_codigo (codigo_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📊 8. Tabla: `recibos_pago` (Módulo Pagos/Finanzas)

```sql
CREATE TABLE recibos_pago (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pago_id INT NOT NULL,
    numero_recibo VARCHAR(50) NOT NULL UNIQUE,
    monto_recibido DECIMAL(10,2) NOT NULL,
    fecha_recibo DATETIME NOT NULL,
    metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta', 'cheque', 'otro') NOT NULL,
    referencia VARCHAR(100),
    usuario_caja_id INT,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_caja_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_recibos_pago (pago_id),
    INDEX idx_recibos_numero (numero_recibo),
    INDEX idx_recibos_fecha (fecha_recibo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📝 Notas Importantes

1. **Orden de Ejecución**: Ejecuta las tablas en el orden presentado, ya que algunas tienen dependencias (foreign keys).

2. **Tablas Existentes**: Las siguientes tablas ya existen en tu base de datos y NO necesitas crearlas:
   - `asistencias` (ya existe)
   - `notificaciones` (ya existe)

3. **Índices**: Todas las tablas incluyen índices para mejorar el rendimiento de las consultas.

4. **Charset**: Todas las tablas usan `utf8mb4` para soportar caracteres especiales y emojis.

5. **Foreign Keys**: Las relaciones están configuradas con `ON DELETE CASCADE` o `ON DELETE SET NULL` según corresponda.

---

## 🚀 Cómo Ejecutar

### Opción 1: Desde phpMyAdmin
1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Selecciona la base de datos `sistema_escolar`
3. Ve a la pestaña "SQL"
4. Copia y pega cada bloque SQL
5. Haz clic en "Continuar"

### Opción 2: Desde la línea de comandos
```bash
mysql -u root -p sistema_escolar < TABLAS_PARA_AGREGAR.sql
```

### Opción 3: Crear un archivo SQL único
Puedes copiar todos los bloques SQL en un solo archivo llamado `nuevas_tablas.sql` y ejecutarlo completo.

---

## ✅ Verificación

Después de ejecutar los scripts, verifica que las tablas se crearon correctamente:

```sql
SHOW TABLES LIKE '%libros%';
SHOW TABLES LIKE '%eventos%';
SHOW TABLES LIKE '%inventario%';
SHOW TABLES LIKE '%pagos%';
```

Todas deberían aparecer en la lista.

---

## 📞 Soporte

Si encuentras algún error al ejecutar estos scripts, verifica:
- Que la base de datos `sistema_escolar` exista
- Que las tablas referenciadas (estudiantes, profesores, grupos, etc.) existan
- Que tengas permisos de administrador en la base de datos




# Sistema de Control Escolar

## 📚 Documentación Completa del Sistema

Sistema integral de gestión escolar desarrollado en PHP con MySQL, diseñado para administrar de manera eficiente todos los aspectos de una institución educativa. El sistema incluye gestión de estudiantes, profesores, materias, calificaciones, asistencias, horarios, biblioteca, inventario, pagos, eventos y notificaciones.

---

## 📋 Tabla de Contenidos

1. [Descripción General](#descripción-general)
2. [Características Principales](#características-principales)
3. [Requisitos del Sistema](#requisitos-del-sistema)
4. [Instalación y Configuración](#instalación-y-configuración)
5. [Estructura del Proyecto](#estructura-del-proyecto)
6. [Módulos del Sistema](#módulos-del-sistema)
7. [Base de Datos](#base-de-datos)
8. [Sistema de Autenticación y Permisos](#sistema-de-autenticación-y-permisos)
9. [Funcionamiento General](#funcionamiento-general)
10. [Archivos Principales](#archivos-principales)
11. [Guía de Uso](#guía-de-uso)
12. [Personalización](#personalización)
13. [Solución de Problemas](#solución-de-problemas)

---

## 🎯 Descripción General

El **Sistema de Control Escolar** es una aplicación web completa desarrollada en PHP que permite gestionar todos los aspectos administrativos y académicos de una institución educativa. El sistema está diseñado con una arquitectura modular que facilita el mantenimiento y la expansión de funcionalidades.

### Objetivos del Sistema

- Centralizar la información académica y administrativa
- Facilitar la gestión de estudiantes, profesores y materias
- Automatizar el registro de calificaciones y asistencias
- Generar reportes y estadísticas académicas
- Gestionar recursos como biblioteca, inventario y aulas
- Proporcionar una interfaz intuitiva y moderna

---

## ✨ Características Principales

### 🎨 Interfaz de Usuario
- **Diseño Moderno**: Interfaz colorida y amigable con temática escolar
- **Responsive**: Adaptable a dispositivos móviles, tablets y escritorio
- **Bootstrap 5**: Framework CSS para componentes modernos
- **Font Awesome**: Iconografía completa para mejor UX
- **Animaciones**: Efectos visuales suaves y profesionales

### 🔐 Seguridad
- Sistema de autenticación con sesiones PHP
- Control de acceso basado en roles (Admin, Profesor, Estudiante)
- Sanitización de datos de entrada
- Protección contra inyección SQL con PDO
- Validación de formularios en cliente y servidor

### 📊 Funcionalidades Avanzadas
- Búsqueda y filtrado avanzado en todos los módulos
- Paginación de resultados para mejor rendimiento
- Exportación de datos a CSV y Excel
- Sistema de notificaciones en tiempo real
- Dashboard con estadísticas en tiempo real
- Generación de reportes personalizados

---

## 💻 Requisitos del Sistema

### Servidor Web
- **PHP**: Versión 7.4 o superior
- **MySQL**: Versión 5.7 o superior (o MariaDB 10.2+)
- **Servidor Web**: Apache 2.4+ o Nginx
- **WampServer**: Recomendado para desarrollo local (Windows)

### Extensiones PHP Requeridas
- `pdo_mysql` - Para conexión a base de datos
- `mbstring` - Para manejo de caracteres UTF-8
- `session` - Para gestión de sesiones
- `json` - Para manejo de JSON
- `gd` - Para procesamiento de imágenes (opcional)

### Navegadores Soportados
- Chrome/Edge (últimas 2 versiones)
- Firefox (últimas 2 versiones)
- Safari (últimas 2 versiones)
- Opera (últimas 2 versiones)

---

## 🚀 Instalación y Configuración

### Paso 1: Preparar el Entorno

1. **Instalar WampServer** (si no está instalado)
   - Descargar desde: https://www.wampserver.com/
   - Instalar y asegurar que MySQL y Apache estén corriendo

2. **Ubicar el Proyecto**
   - Colocar la carpeta `sistema_escolar` en: `C:\wamp64\www\`
   - La ruta completa debe ser: `C:\wamp64\www\sistema_escolar\`

### Paso 2: Configurar la Base de Datos

1. **Iniciar WampServer**
   - Asegurar que el ícono esté verde (todos los servicios activos)

2. **Acceder a phpMyAdmin**
   - Abrir navegador en: `http://localhost/phpmyadmin`
   - Usuario: `root` (contraseña: vacía por defecto)

3. **Importar la Base de Datos**
   - Clic en la pestaña "Importar"
   - Seleccionar el archivo: `sql/database_schema.sql`
   - Clic en "Continuar"
   - Repetir el proceso para: `sql/sample_data.sql` (datos de ejemplo)

4. **Verificar la Instalación**
   - En el panel izquierdo debe aparecer la base de datos `sistema_escolar`
   - Debe contener todas las tablas necesarias

### Paso 3: Configurar la Aplicación

1. **Editar Configuración de Base de Datos**
   - Abrir: `config/database.php`
   - Verificar que las credenciales sean correctas:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sistema_escolar');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

2. **Configurar URL del Sistema**
   - Editar: `config/config.php`
   - Ajustar `SITE_URL` según tu configuración:
```php
   define('SITE_URL', 'https://tarea.site/');
   // O para localhost:
   // define('SITE_URL', 'http://localhost/sistema_escolar/');
```

### Paso 4: Acceder al Sistema

1. **Abrir el Navegador**
   - URL: `http://localhost/sistema_escolar/`
   - O según tu configuración: `https://tarea.site/`

2. **Credenciales por Defecto**
- **Usuario**: `admin`
- **Contraseña**: `admin123`

3. **Primer Acceso**
   - Al iniciar sesión, serás redirigido al Dashboard
   - Verás las estadísticas generales del sistema

---

## 📁 Estructura del Proyecto

```
sistema_escolar/
│
├── 📂 ajax/                          # Peticiones AJAX
│   └── buscar.php                    # Búsqueda dinámica
│
├── 📂 assets/                        # Recursos estáticos
│   ├── 📂 css/
│   │   ├── style.css                 # Estilos principales
│   │   └── dashboard-style.css       # Estilos del dashboard
│   ├── 📂 js/
│   │   └── main.js                   # JavaScript principal
│   └── 📂 uploads/                    # Archivos subidos (si aplica)
│
├── 📂 config/                        # Configuración del sistema
│   ├── config.php                    # Configuración general
│   └── database.php                  # Conexión y funciones de BD
│
├── 📂 includes/                      # Archivos incluidos
│   ├── functions.php                 # Funciones auxiliares
│   ├── navbar.php                    # Barra de navegación
│   ├── footer.php                    # Pie de página
│   ├── search_bar.php                # Barra de búsqueda
│   └── search.php                    # Funciones de búsqueda
│
├── 📂 modules/                       # Módulos del sistema
│   ├── 📂 asistencias/               # Gestión de asistencias
│   │   ├── index.php                 # Listado principal
│   │   ├── listar.php                # Lista de asistencias
│   │   ├── agregar.php               # Registrar asistencia
│   │   ├── editar.php                # Editar asistencia
│   │   ├── eliminar.php               # Eliminar asistencia
│   │   ├── ver.php                   # Ver detalles
│   │   └── exportar.php              # Exportar datos
│   │
│   ├── 📂 aulas/                     # Gestión de aulas
│   │   ├── index.php, listar.php, agregar.php, etc.
│   │
│   ├── 📂 biblioteca/                # Gestión de biblioteca
│   │   ├── index.php, listar.php
│   │   ├── agregar.php               # Agregar libro
│   │   ├── realizar_prestamo.php     # Realizar préstamo
│   │   ├── devolver_libro.php        # Devolver libro
│   │   ├── prestamos.php             # Ver préstamos
│   │   └── exportar.php
│   │
│   ├── 📂 calificaciones/             # Gestión de calificaciones
│   │   ├── index.php, listar.php
│   │   ├── agregar.php               # Agregar calificación
│   │   ├── editar.php, eliminar.php
│   │   ├── ver.php, exportar.php
│   │
│   ├── 📂 estudiantes/                # Gestión de estudiantes
│   │   ├── index.php, listar.php
│   │   ├── agregar.php               # Registrar estudiante
│   │   ├── editar.php, eliminar.php
│   │   ├── ver.php, exportar.php
│   │
│   ├── 📂 eventos/                   # Gestión de eventos
│   │   ├── index.php, listar.php
│   │   ├── agregar.php, editar.php
│   │   ├── eliminar.php, ver.php
│   │   └── exportar.php
│   │
│   ├── 📂 grupos/                    # Gestión de grupos
│   │   ├── index.php, listar.php
│   │   ├── agregar.php, editar.php
│   │   ├── eliminar.php, ver.php
│   │   └── exportar.php
│   │
│   ├── 📂 horarios/                  # Gestión de horarios
│   │   ├── index.php, listar.php
│   │   ├── agregar.php, editar.php
│   │   ├── eliminar.php, ver.php
│   │   └── exportar.php
│   │
│   ├── 📂 inventario/                # Gestión de inventario
│   │   ├── index.php, listar.php
│   │   ├── agregar.php, editar.php
│   │   ├── eliminar.php, ver.php
│   │
│   ├── 📂 materias/                  # Gestión de materias
│   │   ├── index.php, listar.php
│   │   ├── agregar.php, editar.php
│   │   ├── eliminar.php, ver.php
│   │   └── exportar.php
│   │
│   ├── 📂 notificaciones/            # Sistema de notificaciones
│   │   ├── index.php, listar.php
│   │   ├── agregar.php, eliminar.php
│   │   ├── marcar_leida.php          # Marcar como leída
│   │   └── marcar_todas_leidas.php   # Marcar todas como leídas
│   │
│   ├── 📂 pagos/                     # Gestión de pagos
│   │   ├── index.php, listar.php
│   │   ├── agregar.php, editar.php
│   │   ├── eliminar.php, ver.php
│   │
│   ├── 📂 profesores/                # Gestión de profesores
│   │   ├── index.php, listar.php
│   │   ├── agregar.php, editar.php
│   │   ├── eliminar.php, ver.php
│   │   └── exportar.php
│   │
│   ├── 📂 reportes/                  # Generación de reportes
│   │   ├── index.php                 # Índice de reportes
│   │   ├── generales.php             # Reportes generales
│   │   ├── estudiantes.php            # Reportes de estudiantes
│   │   ├── profesores.php             # Reportes de profesores
│   │   ├── grupos.php                 # Reportes de grupos
│   │   ├── materias.php               # Reportes de materias
│   │   ├── calificaciones.php         # Reportes de calificaciones
│   │   └── exportar.php               # Exportar reportes
│   │
│   └── 📂 usuarios/                  # Gestión de usuarios
│       ├── index.php, listar.php
│       ├── agregar.php, editar.php
│       ├── eliminar.php, ver.php
│       └── exportar.php
│
├── 📂 sql/                           # Scripts SQL
│   ├── database_schema.sql           # Estructura de la BD
│   ├── sample_data.sql               # Datos de ejemplo
│   ├── crear_tablas.sql              # Script de creación
│   ├── migrar_esquema.sql            # Script de migración
│   └── README.md                     # Documentación SQL
│
├── 📄 index.php                      # Página de inicio/login
├── 📄 dashboard.php                  # Panel principal
├── 📄 logout.php                     # Cerrar sesión
├── 📄 sistema_escolar (3).sql        # Backup SQL completo
└── 📄 README.md                      # Este archivo
```

---

## 🧩 Módulos del Sistema

### 1. Módulo de Estudiantes

**Ubicación**: `modules/estudiantes/`

**Funcionalidades**:
- ✅ Registro de nuevos estudiantes con matrícula automática
- ✅ Edición de información estudiantil
- ✅ Consulta y visualización de datos completos
- ✅ Eliminación lógica (cambia estado a inactivo)
- ✅ Búsqueda por nombre, apellido, matrícula o email
- ✅ Filtrado por grado y grupo
- ✅ Paginación de resultados
- ✅ Exportación a CSV/Excel
- ✅ Asignación a grados y grupos
- ✅ Gestión de información de contacto y tutores

**Archivos Principales**:
- `index.php` - Redirige al listado
- `listar.php` - Lista todos los estudiantes con filtros
- `agregar.php` - Formulario de registro
- `editar.php` - Formulario de edición
- `ver.php` - Vista detallada del estudiante
- `eliminar.php` - Procesa la eliminación
- `exportar.php` - Exporta datos a diferentes formatos

**Campos Principales**:
- Matrícula (generada automáticamente)
- Nombre completo (nombre, apellido paterno, apellido materno)
- Fecha de nacimiento
- Grado y grupo asignados
- Información de contacto (teléfono, email, dirección)
- Datos del tutor
- Estado (activo/inactivo/egresado)

---

### 2. Módulo de Profesores

**Ubicación**: `modules/profesores/`

**Funcionalidades**:
- ✅ Registro de profesores con código único
- ✅ Gestión de especialidades y materias
- ✅ Asignación de horarios
- ✅ Consulta de grupos asignados
- ✅ Visualización de estudiantes a cargo
- ✅ Exportación de datos

**Campos Principales**:
- Código de profesor
- Nombre completo
- Especialidad
- Fecha de ingreso
- Información de contacto
- Salario (opcional)
- Vinculación con usuario del sistema

---

### 3. Módulo de Materias

**Ubicación**: `modules/materias/`

**Funcionalidades**:
- ✅ Creación de materias por grado
- ✅ Asignación de códigos únicos
- ✅ Gestión de créditos
- ✅ Asociación con grados escolares
- ✅ Consulta de calificaciones por materia
- ✅ Estadísticas de aprobación

**Campos Principales**:
- Código de materia
- Nombre de la materia
- Descripción
- Créditos
- Grado asociado
- Estado (activo/inactivo)

---

### 4. Módulo de Calificaciones

**Ubicación**: `modules/calificaciones/`

**Funcionalidades**:
- ✅ Registro de calificaciones por estudiante y materia
- ✅ Diferentes tipos de evaluación (examen, tarea, proyecto, participación, práctica)
- ✅ Cálculo automático de promedios
- ✅ Filtrado por estudiante, materia, grupo o profesor
- ✅ Historial completo de calificaciones
- ✅ Exportación de boletas

**Tipos de Evaluación**:
- Examen
- Tarea
- Proyecto
- Participación
- Práctica

**Campos Principales**:
- Estudiante
- Materia
- Profesor
- Tipo de evaluación
- Calificación (0-10)
- Fecha de evaluación
- Observaciones

---

### 5. Módulo de Asistencias

**Ubicación**: `modules/asistencias/`

**Funcionalidades**:
- ✅ Registro diario de asistencias
- ✅ Estados: presente, ausente, justificado, tardanza
- ✅ Registro por materia y profesor
- ✅ Consulta de historial de asistencias
- ✅ Estadísticas de asistencia por estudiante
- ✅ Exportación de reportes

**Estados de Asistencia**:
- Presente
- Ausente
- Justificado
- Tardanza

---

### 6. Módulo de Horarios

**Ubicación**: `modules/horarios/`

**Funcionalidades**:
- ✅ Creación de horarios de clases
- ✅ Asignación de materia, profesor, grupo y aula
- ✅ Configuración de días de la semana
- ✅ Definición de horarios (hora inicio/fin)
- ✅ Consulta de horarios por grupo o profesor
- ✅ Validación de conflictos de horarios

**Campos Principales**:
- Materia
- Profesor
- Grupo
- Aula
- Día de la semana
- Hora de inicio
- Hora de fin
- Estado (activo/inactivo)

---

### 7. Módulo de Grupos

**Ubicación**: `modules/grupos/`

**Funcionalidades**:
- ✅ Creación de grupos por grado
- ✅ Asignación de capacidad máxima
- ✅ Gestión de grupos (A, B, C, etc.)
- ✅ Consulta de estudiantes por grupo
- ✅ Estadísticas de grupos

**Campos Principales**:
- Nombre del grupo
- Grado asociado
- Capacidad máxima
- Estado (activo/inactivo)

---

### 8. Módulo de Aulas

**Ubicación**: `modules/aulas/`

**Funcionalidades**:
- ✅ Registro de aulas y espacios físicos
- ✅ Tipos: aula, laboratorio, biblioteca, gimnasio
- ✅ Gestión de capacidad
- ✅ Estados: activo, inactivo, mantenimiento
- ✅ Asignación a horarios

**Tipos de Aulas**:
- Aula regular
- Laboratorio
- Biblioteca
- Gimnasio

---

### 9. Módulo de Biblioteca

**Ubicación**: `modules/biblioteca/`

**Funcionalidades**:
- ✅ Registro de libros y materiales
- ✅ Gestión de préstamos
- ✅ Control de devoluciones
- ✅ Historial de préstamos
- ✅ Consulta de disponibilidad
- ✅ Reportes de préstamos

**Archivos Especiales**:
- `realizar_prestamo.php` - Procesa nuevos préstamos
- `devolver_libro.php` - Procesa devoluciones
- `prestamos.php` - Lista todos los préstamos

---

### 10. Módulo de Inventario

**Ubicación**: `modules/inventario/`

**Funcionalidades**:
- ✅ Registro de materiales y equipos
- ✅ Control de stock
- ✅ Categorización de inventario
- ✅ Historial de movimientos

---

### 11. Módulo de Pagos

**Ubicación**: `modules/pagos/`

**Funcionalidades**:
- ✅ Registro de pagos de estudiantes
- ✅ Tipos de pago (matrícula, mensualidad, etc.)
- ✅ Control de pagos pendientes
- ✅ Historial de pagos
- ✅ Generación de recibos

---

### 12. Módulo de Eventos

**Ubicación**: `modules/eventos/`

**Funcionalidades**:
- ✅ Creación de eventos escolares
- ✅ Fechas y horarios de eventos
- ✅ Descripción y detalles
- ✅ Calendario de eventos
- ✅ Exportación de calendario

---

### 13. Módulo de Notificaciones

**Ubicación**: `modules/notificaciones/`

**Funcionalidades**:
- ✅ Sistema de notificaciones en tiempo real
- ✅ Tipos: info, warning, success, danger
- ✅ Marcar como leída/no leída
- ✅ Marcar todas como leídas
- ✅ Contador de notificaciones no leídas
- ✅ Historial de notificaciones

**Tipos de Notificaciones**:
- Info (información general)
- Warning (advertencias)
- Success (éxito)
- Danger (errores/importantes)

---

### 14. Módulo de Usuarios

**Ubicación**: `modules/usuarios/`

**Funcionalidades**:
- ✅ Creación de usuarios del sistema
- ✅ Asignación de roles (admin, profesor, estudiante)
- ✅ Gestión de permisos
- ✅ Activación/desactivación de usuarios
- ✅ Vinculación con profesores o estudiantes

**Roles Disponibles**:
- **Admin**: Acceso completo
- **Profesor**: Acceso a calificaciones, asistencias, estudiantes
- **Estudiante**: Acceso limitado a información propia

---

### 15. Módulo de Reportes

**Ubicación**: `modules/reportes/`

**Funcionalidades**:
- ✅ Reportes generales del sistema
- ✅ Reportes por estudiantes
- ✅ Reportes por profesores
- ✅ Reportes por grupos
- ✅ Reportes por materias
- ✅ Reportes de calificaciones
- ✅ Exportación en múltiples formatos
- ✅ Gráficos y estadísticas

**Tipos de Reportes**:
- Estadísticas generales
- Rendimiento académico
- Asistencias
- Calificaciones por período
- Reportes personalizados

---

## 🗄️ Base de Datos

### Estructura de la Base de Datos

La base de datos `sistema_escolar` está diseñada con las siguientes características:
- **Motor**: MySQL/MariaDB
- **Codificación**: UTF-8 (utf8mb4_unicode_ci)
- **Tipo de Tablas**: InnoDB (soporte para transacciones y claves foráneas)

### Tablas Principales

#### 1. `usuarios`
Almacena los usuarios del sistema con sus credenciales y roles.

**Campos**:
- `id` (INT, PK, AUTO_INCREMENT)
- `username` (VARCHAR(50), UNIQUE)
- `password` (VARCHAR(255)) - Hash de contraseña
- `email` (VARCHAR(100), UNIQUE)
- `nombre` (VARCHAR(100))
- `apellido` (VARCHAR(100))
- `rol` (ENUM: 'admin', 'profesor', 'estudiante')
- `estado` (ENUM: 'activo', 'inactivo')
- `fecha_creacion` (TIMESTAMP)
- `fecha_actualizacion` (TIMESTAMP)

#### 2. `grados`
Define los grados escolares (1°, 2°, 3°, etc.).

**Campos**:
- `id` (INT, PK)
- `nombre` (VARCHAR(50))
- `descripcion` (TEXT)
- `estado` (ENUM: 'activo', 'inactivo')

#### 3. `grupos`
Grupos dentro de cada grado (A, B, C, etc.).

**Campos**:
- `id` (INT, PK)
- `nombre` (VARCHAR(10))
- `grado_id` (INT, FK → grados.id)
- `capacidad` (INT, DEFAULT 30)
- `estado` (ENUM: 'activo', 'inactivo')

#### 4. `estudiantes`
Información completa de los estudiantes.

**Campos**:
- `id` (INT, PK)
- `matricula` (VARCHAR(20), UNIQUE) - Generada automáticamente
- `nombre`, `apellido_paterno`, `apellido_materno` (VARCHAR(100))
- `fecha_nacimiento` (DATE)
- `grado_id` (INT, FK → grados.id)
- `grupo_id` (INT, FK → grupos.id)
- `telefono`, `email`, `direccion`
- `nombre_tutor`, `telefono_tutor`
- `usuario_id` (INT, FK → usuarios.id, NULL)
- `estado` (ENUM: 'activo', 'inactivo', 'egresado')

#### 5. `profesores`
Información del personal docente.

**Campos**:
- `id` (INT, PK)
- `codigo` (VARCHAR(20), UNIQUE)
- `nombre`, `apellido_paterno`, `apellido_materno`
- `fecha_nacimiento` (DATE)
- `especialidad` (VARCHAR(100))
- `telefono`, `email`, `direccion`
- `fecha_ingreso` (DATE)
- `salario` (DECIMAL(10,2))
- `usuario_id` (INT, FK → usuarios.id, NULL)
- `estado` (ENUM: 'activo', 'inactivo')

#### 6. `materias`
Materias escolares por grado.

**Campos**:
- `id` (INT, PK)
- `codigo` (VARCHAR(20), UNIQUE)
- `nombre` (VARCHAR(100))
- `descripcion` (TEXT)
- `creditos` (INT, DEFAULT 1)
- `grado_id` (INT, FK → grados.id)
- `estado` (ENUM: 'activo', 'inactivo')

#### 7. `aulas`
Espacios físicos de la institución.

**Campos**:
- `id` (INT, PK)
- `nombre` (VARCHAR(50))
- `ubicacion` (VARCHAR(100))
- `capacidad` (INT, DEFAULT 30)
- `tipo` (ENUM: 'aula', 'laboratorio', 'biblioteca', 'gimnasio')
- `estado` (ENUM: 'activo', 'inactivo', 'mantenimiento')

#### 8. `horarios`
Programación de clases.

**Campos**:
- `id` (INT, PK)
- `materia_id` (INT, FK → materias.id)
- `profesor_id` (INT, FK → profesores.id)
- `grupo_id` (INT, FK → grupos.id)
- `aula_id` (INT, FK → aulas.id)
- `dia_semana` (ENUM: 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado')
- `hora_inicio` (TIME)
- `hora_fin` (TIME)
- `estado` (ENUM: 'activo', 'inactivo')

#### 9. `calificaciones`
Registro de calificaciones.

**Campos**:
- `id` (INT, PK)
- `estudiante_id` (INT, FK → estudiantes.id)
- `materia_id` (INT, FK → materias.id)
- `profesor_id` (INT, FK → profesores.id)
- `tipo_evaluacion` (ENUM: 'examen', 'tarea', 'proyecto', 'participacion', 'practica')
- `calificacion` (DECIMAL(5,2)) - Rango 0-10
- `fecha_evaluacion` (DATE)
- `observaciones` (TEXT)

#### 10. `asistencias`
Registro de asistencias diarias.

**Campos**:
- `id` (INT, PK)
- `estudiante_id` (INT, FK → estudiantes.id)
- `materia_id` (INT, FK → materias.id)
- `profesor_id` (INT, FK → profesores.id)
- `fecha` (DATE)
- `estado` (ENUM: 'presente', 'ausente', 'justificado', 'tardanza')
- `observaciones` (TEXT)

#### 11. `notificaciones`
Sistema de notificaciones.

**Campos**:
- `id` (INT, PK)
- `usuario_id` (INT, FK → usuarios.id)
- `titulo` (VARCHAR(200))
- `mensaje` (TEXT)
- `tipo` (ENUM: 'info', 'warning', 'success', 'danger')
- `leida` (BOOLEAN, DEFAULT FALSE)
- `fecha_creacion` (TIMESTAMP)

#### 12. `configuraciones`
Configuraciones del sistema.

**Campos**:
- `id` (INT, PK)
- `clave` (VARCHAR(100), UNIQUE)
- `valor` (TEXT)
- `descripcion` (TEXT)
- `tipo` (ENUM: 'texto', 'numero', 'booleano', 'json')

### Relaciones entre Tablas

```
grados (1) ──→ (N) grupos
grupos (1) ──→ (N) estudiantes
grados (1) ──→ (N) materias
grados (1) ──→ (N) estudiantes

estudiantes (1) ──→ (N) calificaciones
materias (1) ──→ (N) calificaciones
profesores (1) ──→ (N) calificaciones

estudiantes (1) ──→ (N) asistencias
materias (1) ──→ (N) asistencias
profesores (1) ──→ (N) asistencias

horarios:
  - materia_id → materias
  - profesor_id → profesores
  - grupo_id → grupos
  - aula_id → aulas

usuarios (1) ──→ (0..1) profesores
usuarios (1) ──→ (0..1) estudiantes
usuarios (1) ──→ (N) notificaciones
```

### Índices para Optimización

El sistema incluye índices en campos frecuentemente consultados:
- `idx_estudiantes_matricula` - Búsqueda por matrícula
- `idx_estudiantes_grado_grupo` - Filtrado por grado y grupo
- `idx_profesores_codigo` - Búsqueda por código
- `idx_calificaciones_estudiante_materia` - Consultas de calificaciones
- `idx_asistencias_estudiante_fecha` - Consultas de asistencias
- `idx_horarios_dia_hora` - Consultas de horarios
- `idx_notificaciones_usuario_leida` - Notificaciones no leídas

---

## 🔐 Sistema de Autenticación y Permisos

### Autenticación

El sistema utiliza sesiones PHP para la autenticación:

1. **Login** (`index.php`):
   - Formulario de usuario y contraseña
   - Validación de credenciales
   - Creación de sesión con datos del usuario
   - Redirección al dashboard

2. **Sesión**:
   - `$_SESSION['user_id']` - ID del usuario
   - `$_SESSION['username']` - Nombre de usuario
   - `$_SESSION['user_role']` - Rol del usuario

3. **Logout** (`logout.php`):
   - Destrucción de sesión
   - Redirección al login

### Control de Acceso

El sistema implementa control de acceso basado en roles mediante la función `hasPermission()` en `includes/functions.php`:

```php
function hasPermission($permission) {
    // Verifica si el usuario tiene el permiso requerido
    // 'admin' - Solo administradores
    // 'profesor' - Administradores y profesores
    // 'estudiante' - Todos los usuarios autenticados
}
```

### Roles y Permisos

#### Administrador (`admin`)
- ✅ Acceso completo a todos los módulos
- ✅ Gestión de usuarios
- ✅ Configuración del sistema
- ✅ Generación de reportes
- ✅ Administración de grupos, aulas, horarios
- ✅ Gestión de biblioteca, inventario, pagos, eventos

#### Profesor (`profesor`)
- ✅ Consulta de estudiantes
- ✅ Gestión de calificaciones
- ✅ Registro de asistencias
- ✅ Consulta de horarios
- ✅ Consulta de materias
- ❌ No puede gestionar usuarios
- ❌ No puede acceder a configuración del sistema

#### Estudiante (`estudiante`)
- ✅ Consulta de sus propias calificaciones
- ✅ Consulta de sus asistencias
- ✅ Consulta de horarios
- ❌ No puede modificar datos
- ❌ Acceso muy limitado

### Protección de Páginas

Cada página protegida debe incluir:

```php
require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    redirect('index.php');
}

// Verificar permisos específicos
if (!hasPermission('admin')) {
    redirect('dashboard.php');
}
```

---

## ⚙️ Funcionamiento General

### Flujo de Usuario

1. **Acceso Inicial**:
   - Usuario accede a `index.php`
   - Ve formulario de login
   - Ingresa credenciales

2. **Autenticación**:
   - Sistema valida credenciales
   - Crea sesión de usuario
   - Redirige a `dashboard.php`

3. **Dashboard**:
   - Muestra estadísticas generales
   - Accesos rápidos a módulos
   - Actividad reciente
   - Notificaciones

4. **Navegación**:
   - Usuario accede a módulos desde el menú
   - Cada módulo tiene su propia estructura
   - Funciones CRUD (Crear, Leer, Actualizar, Eliminar)

5. **Operaciones**:
   - Listar registros con filtros y paginación
   - Agregar nuevos registros
   - Editar registros existentes
   - Ver detalles completos
   - Eliminar registros (lógico o físico)
   - Exportar datos

### Flujo de Datos

1. **Entrada de Datos**:
   - Usuario completa formulario
   - JavaScript valida en cliente
   - Datos se envían vía POST

2. **Procesamiento**:
   - PHP sanitiza datos (`sanitize()`)
   - Validación en servidor
   - Consulta a base de datos (PDO)
   - Procesamiento de resultados

3. **Salida**:
   - Renderizado de HTML
   - Mensajes de éxito/error
   - Redirección o actualización de página

### Funciones Auxiliares

El archivo `includes/functions.php` contiene funciones reutilizables:

- `sanitize($data)` - Limpia datos de entrada
- `isLoggedIn()` - Verifica autenticación
- `hasPermission($permission)` - Verifica permisos
- `redirect($url)` - Redirige a otra página
- `showError($message)` - Muestra error
- `showSuccess($message)` - Muestra éxito
- `formatDate($date, $format)` - Formatea fechas
- `generateCode($prefix, $length)` - Genera códigos únicos
- `isValidEmail($email)` - Valida emails
- `paginate($total, $page, $per_page)` - Calcula paginación

### Funciones de Base de Datos

El archivo `config/database.php` contiene:

- `conectarDB()` - Establece conexión PDO
- `obtenerEstudiantes($filtros)` - Obtiene estudiantes con filtros
- `agregarEstudiante($datos)` - Agrega nuevo estudiante
- `actualizarEstudiante($id, $datos)` - Actualiza estudiante
- `eliminarEstudiante($id)` - Elimina estudiante (lógico)
- `obtenerProfesores($filtros)` - Obtiene profesores
- `obtenerMaterias($filtros)` - Obtiene materias
- `obtenerGrupos($filtros)` - Obtiene grupos
- `obtenerEstadisticasGenerales()` - Calcula estadísticas

---

## 📄 Archivos Principales

### `index.php`
Página de inicio y login del sistema.

**Funcionalidades**:
- Formulario de autenticación
- Validación de credenciales
- Creación de sesión
- Redirección al dashboard
- Diseño atractivo con animaciones

**Características**:
- Validación de campos requeridos
- Mensajes de error
- Credenciales por defecto mostradas
- Diseño responsive

### `dashboard.php`
Panel principal del sistema después del login.

**Funcionalidades**:
- Estadísticas generales (estudiantes, profesores, materias, grupos)
- Accesos rápidos a módulos principales
- Actividad reciente
- Notificaciones no leídas
- Navegación principal

**Características**:
- Tarjetas de estadísticas animadas
- Diseño colorido y amigable
- Responsive design
- Integración con todos los módulos

### `config/config.php`
Configuración general del sistema.

**Contenido**:
- Configuración de sesión
- Constantes de base de datos
- Configuración del sitio (nombre, URL, email)
- Rutas de archivos
- Límites del sistema (paginación, tamaño de archivos)
- Inclusión de archivos necesarios

### `config/database.php`
Conexión y funciones de base de datos.

**Funcionalidades**:
- Función `conectarDB()` - Conexión PDO con manejo de errores
- Funciones CRUD para estudiantes
- Funciones de consulta para profesores, materias, grupos
- Función de estadísticas generales
- Manejo de UTF-8

**Características**:
- Manejo de errores con try-catch
- Configuración de charset UTF-8
- Reintento de conexión si falla
- Preparación de consultas (seguridad)

### `includes/functions.php`
Funciones auxiliares del sistema.

**Funciones Principales**:
- `sanitize()` - Sanitización de datos
- `isLoggedIn()` - Verificación de sesión
- `hasPermission()` - Control de permisos
- `redirect()` - Redirección
- `showError()` / `showSuccess()` - Mensajes
- `formatDate()` - Formateo de fechas
- `generateCode()` - Generación de códigos
- `isValidEmail()` - Validación de email
- `paginate()` - Cálculo de paginación
- `obtenerCalificacionesPorMateria()` - Consultas de calificaciones

### `includes/navbar.php`
Barra de navegación del sistema.

**Funcionalidades**:
- Menú principal con todos los módulos
- Menús desplegables por categoría
- Indicador de notificaciones no leídas
- Menú de usuario (perfil, logout)
- Diseño responsive con Bootstrap

**Estructura**:
- Dashboard
- Estudiantes (admin, profesor)
- Profesores (admin)
- Materias
- Calificaciones
- Asistencias (admin, profesor)
- Administración (admin)
- Notificaciones
- Reportes (admin)

### `assets/js/main.js`
JavaScript principal del sistema.

**Funcionalidades**:
- Inicialización de tooltips y popovers de Bootstrap
- Confirmación de eliminaciones
- Auto-ocultar alertas después de 5 segundos
- Validación de formularios
- Búsqueda en tiempo real
- Auto-generación de matrículas
- Formateo de números telefónicos
- Formateo de moneda
- Validación de fechas
- Estados de carga en formularios
- Selección múltiple en tablas
- Acciones masivas
- Funcionalidad de impresión
- Exportación a CSV/Excel
- Sistema de notificaciones
- Carga dinámica de contenido

---

## 📖 Guía de Uso

### Para Administradores

#### 1. Gestión de Estudiantes

1. **Agregar Estudiante**:
   - Ir a: Estudiantes → Agregar Estudiante
   - Completar formulario (nombre, apellidos, fecha nacimiento)
   - Seleccionar grado y grupo
   - Ingresar información de contacto
   - La matrícula se genera automáticamente
   - Guardar

2. **Buscar Estudiante**:
   - Ir a: Estudiantes → Listar Estudiantes
   - Usar barra de búsqueda (nombre, apellido, matrícula, email)
   - Aplicar filtros por grado y grupo
   - Los resultados se paginan automáticamente

3. **Editar Estudiante**:
   - Desde la lista, clic en botón "Editar"
   - Modificar información necesaria
   - Guardar cambios

4. **Eliminar Estudiante**:
   - Desde la lista, clic en botón "Eliminar"
   - Confirmar eliminación
   - El estudiante cambia a estado "inactivo" (no se borra físicamente)

#### 2. Gestión de Calificaciones

1. **Agregar Calificación**:
   - Ir a: Calificaciones → Agregar Calificación
   - Seleccionar estudiante, materia y profesor
   - Elegir tipo de evaluación
   - Ingresar calificación (0-10)
   - Agregar observaciones (opcional)
   - Guardar

2. **Ver Calificaciones**:
   - Ir a: Calificaciones → Ver Calificaciones
   - Filtrar por estudiante, materia, grupo o profesor
   - Ver historial completo
   - Exportar boletas

#### 3. Gestión de Horarios

1. **Crear Horario**:
   - Ir a: Horarios → Agregar Horario
   - Seleccionar materia, profesor, grupo y aula
   - Elegir día de la semana
   - Definir hora de inicio y fin
   - Guardar

2. **Consultar Horarios**:
   - Ver horarios por grupo o profesor
   - Validar conflictos de horarios

#### 4. Generar Reportes

1. **Acceder a Reportes**:
   - Ir a: Reportes → Índice de Reportes
   - Seleccionar tipo de reporte deseado

2. **Tipos de Reportes**:
   - Generales: Estadísticas del sistema
   - Estudiantes: Rendimiento por estudiante
   - Profesores: Actividad docente
   - Grupos: Estadísticas por grupo
   - Materias: Rendimiento por materia
   - Calificaciones: Análisis de calificaciones

3. **Exportar Reportes**:
   - Cada reporte tiene opción de exportar
   - Formatos: CSV, Excel, PDF (según implementación)

### Para Profesores

#### 1. Registrar Calificaciones

1. Ir a: Calificaciones → Agregar Calificación
2. Seleccionar estudiante de su grupo
3. Seleccionar materia que imparte
4. Ingresar calificación y tipo de evaluación
5. Guardar

#### 2. Registrar Asistencias

1. Ir a: Asistencias → Registrar Asistencia
2. Seleccionar fecha, materia y grupo
3. Marcar estado de cada estudiante (presente/ausente/justificado/tardanza)
4. Guardar

#### 3. Consultar Información

- Ver estudiantes de sus grupos
- Consultar calificaciones registradas
- Ver horarios asignados
- Consultar asistencias

### Para Estudiantes

#### 1. Consultar Calificaciones

1. Acceder al sistema con credenciales de estudiante
2. Ir a: Calificaciones → Ver Calificaciones
3. Ver solo sus propias calificaciones
4. Ver promedios por materia

#### 2. Consultar Asistencias

1. Ir a: Asistencias → Ver Asistencias
2. Ver su historial de asistencias
3. Ver porcentaje de asistencia

#### 3. Consultar Horarios

1. Ir a: Horarios → Ver Horarios
2. Ver horario de clases de su grupo

---

## 🎨 Personalización

### Cambiar Colores y Estilos

1. **Editar CSS Principal**:
   - Archivo: `assets/css/style.css`
   - Modificar variables CSS o clases directamente

2. **Editar Estilos del Dashboard**:
   - Archivo: `assets/css/dashboard-style.css`
   - Personalizar colores de tarjetas, botones, etc.

3. **Variables CSS en Dashboard**:
   ```css
   :root {
       --sunny-yellow: #FFD700;
       --sky-blue: #4ECDC4;
       --grass-green: #10B981;
       /* Modificar estos valores */
   }
   ```

### Cambiar Nombre del Sistema

1. Editar `config/config.php`:
   ```php
   define('SITE_NAME', 'Tu Nombre de Sistema');
   ```

### Configurar Paginación

1. Editar `config/config.php`:
   ```php
   define('RECORDS_PER_PAGE', 20); // Cambiar número de registros por página
   ```

### Agregar Nuevos Módulos

1. **Crear Estructura**:
   ```
   modules/nuevo_modulo/
   ├── index.php
   ├── listar.php
   ├── agregar.php
   ├── editar.php
   ├── eliminar.php
   ├── ver.php
   └── exportar.php
   ```

2. **Agregar al Menú**:
   - Editar `includes/navbar.php`
   - Agregar enlace al nuevo módulo

3. **Crear Tabla en BD**:
   - Crear script SQL para la nueva tabla
   - Ejecutar en phpMyAdmin

4. **Agregar Funciones**:
   - Agregar funciones en `config/database.php` si es necesario

---

## 🔧 Solución de Problemas

### Error de Conexión a Base de Datos

**Síntomas**: Mensaje "Error de conexión" al acceder al sistema

**Soluciones**:
1. Verificar que WampServer esté ejecutándose (ícono verde)
2. Verificar que MySQL esté activo
3. Revisar credenciales en `config/database.php`
4. Verificar que la base de datos `sistema_escolar` exista
5. Verificar que el puerto de MySQL sea el correcto (3306 por defecto)

### Error 404 - Página No Encontrada

**Síntomas**: Al acceder a una URL, aparece error 404

**Soluciones**:
1. Verificar que los archivos estén en `C:\wamp64\www\sistema_escolar\`
2. Verificar que Apache esté ejecutándose
3. Revisar la configuración de `SITE_URL` en `config/config.php`
4. Verificar que el archivo `.htaccess` no esté bloqueando (si existe)

### Error de Codificación (Caracteres Raros)

**Síntomas**: Se muestran caracteres extraños (Ã±, Ã©, etc.)

**Soluciones**:
1. Verificar que la base de datos use UTF-8 (utf8mb4_unicode_ci)
2. Verificar que los archivos PHP tengan codificación UTF-8
3. Agregar al inicio de archivos PHP:
   ```php
   header('Content-Type: text/html; charset=UTF-8');
   mb_internal_encoding('UTF-8');
   ```
4. Verificar configuración de PHP para charset

### Error de Permisos

**Síntomas**: No se pueden crear/editar registros

**Soluciones**:
1. Verificar permisos de archivos en Windows
2. Asegurar que Apache tenga permisos de escritura
3. Verificar permisos de usuario en MySQL
4. Revisar que el usuario tenga rol adecuado en el sistema

### Sesión Expirada

**Síntomas**: Se redirige al login constantemente

**Soluciones**:
1. Verificar configuración de sesiones en PHP
2. Aumentar tiempo de vida de sesión en `php.ini`:
   ```ini
   session.gc_maxlifetime = 3600
   ```
3. Verificar que las cookies estén habilitadas en el navegador

### Error al Exportar Datos

**Síntomas**: No se descarga el archivo de exportación

**Soluciones**:
1. Verificar permisos de escritura en servidor
2. Verificar que no haya output antes de headers
3. Revisar configuración de PHP para límites de memoria
4. Verificar que el formato de exportación esté implementado

### Problemas con Búsqueda

**Síntomas**: La búsqueda no encuentra resultados

**Soluciones**:
1. Verificar que la consulta SQL use LIKE correctamente
2. Verificar codificación de caracteres
3. Revisar índices en la base de datos
4. Verificar que los datos existan en la BD

---

## 📝 Notas Adicionales

### Mejores Prácticas

1. **Seguridad**:
   - Cambiar credenciales por defecto
   - Usar contraseñas seguras
   - Mantener el sistema actualizado
   - Realizar respaldos periódicos

2. **Mantenimiento**:
   - Limpiar registros antiguos periódicamente
   - Optimizar base de datos regularmente
   - Revisar logs de errores
   - Actualizar dependencias

3. **Rendimiento**:
   - Usar índices en consultas frecuentes
   - Implementar caché cuando sea posible
   - Optimizar consultas SQL
   - Limitar resultados con paginación

### Respaldos

**Crear Respaldo de Base de Datos**:
```sql
-- Desde phpMyAdmin: Exportar → SQL
-- O desde línea de comandos:
mysqldump -u root -p sistema_escolar > backup_$(date +%Y%m%d).sql
```

**Restaurar Respaldo**:
```sql
-- Desde phpMyAdmin: Importar → Seleccionar archivo
-- O desde línea de comandos:
mysql -u root -p sistema_escolar < backup_20240101.sql
```

### Actualizaciones Futuras

Posibles mejoras y funcionalidades futuras:
- Sistema de mensajería entre usuarios
- Portal para padres de familia
- Aplicación móvil
- Integración con sistemas de pago
- Sistema de tareas y deberes
- Foros y discusiones
- Biblioteca digital
- Videoconferencias integradas

---

## 📞 Soporte y Contacto

Para soporte técnico, consultas o reportar problemas:

- **Documentación**: Ver este README y archivos en carpeta `sql/`
- **Issues**: Reportar problemas en el sistema de issues del repositorio
- **Email**: soporte@sistemaescolar.com (ejemplo)

---

## 📄 Licencia

Este proyecto está disponible para uso educativo. Ver archivo `LICENSE` para más detalles.

---

## 🙏 Agradecimientos

- **Bootstrap 5** - Framework CSS para diseño responsive
- **Font Awesome** - Iconografía completa
- **PHP Community** - Comunidad de desarrolladores PHP
- **MySQL/MariaDB** - Sistema de gestión de base de datos
- **WampServer** - Entorno de desarrollo local

---

## 📚 Recursos Adicionales

- **Documentación PHP**: https://www.php.net/docs.php
- **Documentación MySQL**: https://dev.mysql.com/doc/
- **Bootstrap 5**: https://getbootstrap.com/docs/5.1/
- **Font Awesome**: https://fontawesome.com/

---

**Desarrollado con ❤️ para la educación**

*Última actualización: 2024*

---

## 🔄 Changelog

### Versión 1.0.0
- ✅ Sistema base implementado
- ✅ Módulos principales funcionales
- ✅ Sistema de autenticación y permisos
- ✅ Interfaz responsive
- ✅ Base de datos optimizada
- ✅ Sistema de notificaciones
- ✅ Exportación de datos
- ✅ Dashboard con estadísticas
- ✅ Búsqueda y filtrado avanzado
- ✅ Paginación de resultados

---

*Este documento se actualiza periódicamente. Para la versión más reciente, consulta el repositorio del proyecto.*

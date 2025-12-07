# 🎨 Guía para Aplicar Estilo del Dashboard a Todos los Módulos

He actualizado la configuración de la base de datos para usar el puerto **8080** y he aplicado el estilo del dashboard al módulo de Estudiantes como ejemplo.

## ✅ Cambios Realizados

### 1. Configuración de Base de Datos
- ✅ Actualizado `config/config.php` para incluir `DB_PORT = '8080'`
- ✅ Actualizado `config/database.php` para usar el puerto en la conexión DSN

### 2. Estilos del Dashboard
- ✅ Creado `assets/css/dashboard-style.css` con todos los estilos del dashboard
- ✅ Aplicado al módulo de Estudiantes como ejemplo

## 📋 Pasos para Aplicar a Otros Módulos

Para cada módulo (Profesores, Materias, Calificaciones, etc.), necesitas:

### 1. Actualizar el `<head>` del archivo PHP

**Cambiar de:**
```php
<link href="../../assets/css/style.css" rel="stylesheet">
<style>
    /* estilos personalizados */
</style>
```

**A:**
```php
<link href="../../assets/css/style.css" rel="stylesheet">
<link href="../../assets/css/dashboard-style.css" rel="stylesheet">
<style>
    body { font-family: 'Comic Sans MS', 'Chalkboard', 'Marker Felt', cursive; }
    .main-container {
        background: linear-gradient(135deg, rgba(224, 242, 254, 0.5) 0%, rgba(254, 243, 199, 0.5) 50%, rgba(252, 231, 243, 0.5) 100%);
        padding: 2rem;
        min-height: calc(100vh - 100px);
    }
    .page-header {
        background: linear-gradient(135deg, var(--sky-blue) 0%, #2196F3 100%);
        border-radius: 25px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        color: white;
    }
</style>
```

### 2. Actualizar el `<body>`

**Cambiar de:**
```php
<body>
    <div class="container-fluid mt-4">
```

**A:**
```php
<body class="dashboard-style">
    <div class="main-container">
```

### 3. Actualizar el Header

**Cambiar de:**
```php
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">
            <i class="fas fa-icon me-2 text-primary"></i>
            <?php echo $page_title; ?>
        </h2>
        <p class="text-muted mb-0">Descripción</p>
    </div>
    <div>
        <a href="agregar.php" class="btn btn-primary btn-lg">
            <i class="fas fa-plus me-2"></i>Agregar
        </a>
    </div>
</div>
```

**A:**
```php
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-icon me-2"></i>
                <?php echo $page_title; ?>
            </h2>
            <p class="mb-0 mt-2">Descripción</p>
        </div>
        <div>
            <a href="agregar.php" class="btn btn-light btn-lg">
                <i class="fas fa-plus me-2"></i>Agregar
            </a>
        </div>
    </div>
</div>
```

### 4. Actualizar Cards de Estadísticas

**Cambiar de:**
```php
<div class="card stats-card">
    <div class="card-body text-center">
        <i class="fas fa-icon fa-2x mb-2"></i>
        <h4>123</h4>
        <p class="mb-0">Total</p>
    </div>
</div>
```

**A:**
```php
<div class="stat-card students"> <!-- o teachers, subjects, groups según corresponda -->
    <div class="stat-icon">
        <i class="fas fa-icon"></i>
    </div>
    <h3 class="stat-number">123</h3>
    <p class="stat-label">Total</p>
</div>
```

### 5. Actualizar Cards de Contenido

**Cambiar de:**
```php
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-icon me-2"></i>Título
        </h5>
    </div>
    <div class="card-body">
```

**A:**
```php
<div class="content-card">
    <div class="content-card-header">
        <i class="fas fa-icon"></i>
        Título
    </div>
    <div class="card-body p-4">
```

### 6. Actualizar Tablas

**Cambiar de:**
```php
<table class="table table-hover">
    <thead class="table-dark">
```

**A:**
```php
<table class="table table-module table-hover">
    <thead>
```

### 7. Actualizar Badges

**Cambiar de:**
```php
<span class="badge bg-success">Activo</span>
```

**A:**
```php
<span class="badge badge-module bg-success">Activo</span>
```

### 8. Actualizar Botones

**Cambiar de:**
```php
<a href="#" class="btn btn-primary">Acción</a>
```

**A:**
```php
<a href="#" class="btn btn-school btn-students">Acción</a>
<!-- o btn-teachers, btn-subjects, btn-attendance según corresponda -->
```

### 9. Actualizar Alertas

**Cambiar de:**
```php
<div class="alert alert-success">
```

**A:**
```php
<div class="alert alert-success alert-module">
```

## 🎨 Clases CSS Disponibles

### Cards de Estadísticas
- `.stat-card.students` - Azul (estudiantes)
- `.stat-card.teachers` - Verde (profesores)
- `.stat-card.subjects` - Naranja (materias)
- `.stat-card.groups` - Púrpura (grupos)
- `.stat-card.attendance` - Coral (asistencias)
- `.stat-card.events` - Rosa (eventos)
- `.stat-card.library` - Lima (biblioteca)
- `.stat-card.inventory` - Azul claro (inventario)
- `.stat-card.payments` - Naranja oscuro (pagos)

### Botones
- `.btn-school.btn-students` - Azul
- `.btn-school.btn-teachers` - Verde
- `.btn-school.btn-subjects` - Naranja
- `.btn-school.btn-attendance` - Coral
- `.btn-school.btn-events` - Rosa
- `.btn-school.btn-library` - Lima
- `.btn-school.btn-inventory` - Azul claro
- `.btn-school.btn-payments` - Naranja oscuro

## 📝 Archivos a Actualizar

Aplica estos cambios a los siguientes archivos:

### Módulo de Profesores
- `modules/profesores/index.php` (listar.php)
- `modules/profesores/agregar.php`
- `modules/profesores/editar.php`
- `modules/profesores/ver.php`

### Módulo de Materias
- `modules/materias/index.php` (listar.php)
- `modules/materias/agregar.php`
- `modules/materias/editar.php`
- `modules/materias/ver.php`

### Módulo de Calificaciones
- `modules/calificaciones/index.php` (listar.php)
- `modules/calificaciones/agregar.php`
- `modules/calificaciones/editar.php`
- `modules/calificaciones/ver.php`

### Módulo de Horarios
- `modules/horarios/index.php` (listar.php)
- `modules/horarios/agregar.php`
- `modules/horarios/editar.php`
- `modules/horarios/ver.php`

### Módulo de Aulas
- `modules/aulas/index.php` (listar.php)
- `modules/aulas/agregar.php`
- `modules/aulas/editar.php`
- `modules/aulas/ver.php`

### Módulo de Grupos
- `modules/grupos/index.php` (listar.php)
- `modules/grupos/agregar.php`
- `modules/grupos/editar.php`
- `modules/grupos/ver.php`

### Módulo de Usuarios
- `modules/usuarios/index.php` (listar.php)
- `modules/usuarios/agregar.php`
- `modules/usuarios/editar.php`
- `modules/usuarios/ver.php`

## ⚠️ Nota Importante

El módulo de **Estudiantes** ya está actualizado como referencia. Puedes usarlo como plantilla para los demás módulos.

## 🔌 Conexión a Base de Datos

La conexión ahora usa el puerto **8080**:
- Host: `localhost`
- Puerto: `8080`
- Base de datos: `sistema_escolar`

Esto se configuró automáticamente en `config/config.php` y `config/database.php`.




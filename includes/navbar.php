<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CAMBIO: URL actualizada de /sistema_escolar/assets/css/style.css a /assets/css/style.css para dominio https://tarea.site/ -->
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- CAMBIO: URL actualizada de /sistema_escolar/dashboard.php a /dashboard.php para dominio https://tarea.site/ -->
            <a class="navbar-brand" href="/dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>
                <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    
                    <?php if (hasPermission('admin') || hasPermission('profesor')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="estudiantesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-graduate me-1"></i>Estudiantes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/modules/estudiantes/listar.php">Listar Estudiantes</a></li>
                            <li><a class="dropdown-item" href="/modules/estudiantes/agregar.php">Agregar Estudiante</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('admin')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profesoresDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chalkboard-teacher me-1"></i>Profesores
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/modules/profesores/listar.php">Listar Profesores</a></li>
                            <li><a class="dropdown-item" href="/modules/profesores/agregar.php">Agregar Profesor</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="materiasDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-book me-1"></i>Materias
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/modules/materias/listar.php">Listar Materias</a></li>
                            <?php if (hasPermission('admin')): ?>
                            <li><a class="dropdown-item" href="/modules/materias/agregar.php">Agregar Materia</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="calificacionesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-line me-1"></i>Calificaciones
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/modules/calificaciones/listar.php">Ver Calificaciones</a></li>
                            <?php if (hasPermission('profesor')): ?>
                            <li><a class="dropdown-item" href="/modules/calificaciones/agregar.php">Agregar Calificación</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    
                    <?php if (hasPermission('admin') || hasPermission('profesor')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="asistenciasDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-check me-1"></i>Asistencias
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/modules/asistencias/listar.php">Listar Asistencias</a></li>
                            <li><a class="dropdown-item" href="/modules/asistencias/agregar.php">Registrar Asistencia</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('admin')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="administracionDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs me-1"></i>Administración
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/modules/usuarios/listar.php">Usuarios</a></li>
                            <li><a class="dropdown-item" href="/modules/grupos/listar.php">Grupos</a></li>
                            <li><a class="dropdown-item" href="/modules/aulas/listar.php">Aulas</a></li>
                            <li><a class="dropdown-item" href="/modules/horarios/listar.php">Horarios</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/modules/eventos/listar.php"><i class="fas fa-calendar-alt me-1"></i>Eventos</a></li>
                            <li><a class="dropdown-item" href="/modules/biblioteca/listar.php"><i class="fas fa-book-reader me-1"></i>Biblioteca</a></li>
                            <li><a class="dropdown-item" href="/modules/inventario/listar.php"><i class="fas fa-boxes me-1"></i>Inventario</a></li>
                            <li><a class="dropdown-item" href="/modules/pagos/listar.php"><i class="fas fa-money-bill-wave me-1"></i>Pagos</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/modules/notificaciones/listar.php">
                            <i class="fas fa-bell me-1"></i>Notificaciones
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/modules/reportes/index.php">
                            <i class="fas fa-chart-bar me-1"></i>Reportes
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/profile.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
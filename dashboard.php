<?php
/**
 * Dashboard principal del sistema
 * Sistema de Control Escolar
 */

require_once 'config/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    redirect('index.php');
}

$user_role = $_SESSION['user_role'];

// Obtener estadísticas reales
$stats = obtenerEstadisticasGenerales();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Temática Infantil - Dashboard Escolar */
        :root {
            --sunny-yellow: #FFD700;
            --sky-blue: #4ECDC4;
            --grass-green: #10B981;
            --coral: #FF6B6B;
            --purple: #A78BFA;
            --pink: #F472B6;
            --orange: #FB923C;
            --lime: #84CC16;
        }
        
        body {
            background: linear-gradient(135deg, #E0F2FE 0%, #FEF3C7 50%, #FCE7F3 100%);
            min-height: 100vh;
            font-family: 'Comic Sans MS', 'Chalkboard', 'Marker Felt', cursive;
        }
        
        /* Navbar infantil */
        .navbar-school {
            background: linear-gradient(135deg, var(--sky-blue) 0%, var(--purple) 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            border-bottom: 5px solid var(--sunny-yellow);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 20px;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.05);
        }
        
        /* Cards de estadísticas */
        .stat-card {
            border-radius: 25px;
            border: none;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
        }
        
        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .stat-card.students {
            background: linear-gradient(135deg, var(--sky-blue) 0%, #2196F3 100%);
        }
        
        .stat-card.teachers {
            background: linear-gradient(135deg, var(--grass-green) 0%, #059669 100%);
        }
        
        .stat-card.subjects {
            background: linear-gradient(135deg, var(--orange) 0%, #EA580C 100%);
        }
        
        .stat-card.groups {
            background: linear-gradient(135deg, var(--purple) 0%, #7C3AED 100%);
        }
        
        .stat-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 2.5rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .stat-number {
            font-size: 3.5rem;
            font-weight: bold;
            color: white;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.2);
            margin: 0;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.95);
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        /* Cards de contenido */
        .content-card {
            background: white;
            border-radius: 25px;
            border: 3px solid var(--sunny-yellow);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .content-card-header {
            background: linear-gradient(135deg, var(--purple) 0%, var(--pink) 100%);
            padding: 1.5rem;
            color: white;
            font-weight: bold;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
        }
        
        .content-card-header i {
            margin-right: 1rem;
            font-size: 1.8rem;
        }
        
        /* Botones infantiles */
        .btn-school {
            border-radius: 20px;
            padding: 1rem 2rem;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: 3px solid transparent;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-school:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        
        .btn-students {
            background: linear-gradient(135deg, var(--sky-blue) 0%, #2196F3 100%);
            color: white;
        }
        
        .btn-teachers {
            background: linear-gradient(135deg, var(--grass-green) 0%, #059669 100%);
            color: white;
        }
        
        .btn-subjects {
            background: linear-gradient(135deg, var(--orange) 0%, #EA580C 100%);
            color: white;
        }
        
        .btn-grades {
            background: linear-gradient(135deg, var(--coral) 0%, #EF4444 100%);
            color: white;
        }
        
        .btn-attendance {
            background: linear-gradient(135deg, var(--sky-blue) 0%, #0EA5E9 100%);
            color: white;
        }
        
        .btn-events {
            background: linear-gradient(135deg, var(--pink) 0%, #EC4899 100%);
            color: white;
        }
        
        .btn-library {
            background: linear-gradient(135deg, var(--lime) 0%, #65A30D 100%);
            color: white;
        }
        
        .btn-inventory {
            background: linear-gradient(135deg, var(--coral) 0%, #DC2626 100%);
            color: white;
        }
        
        .btn-payments {
            background: linear-gradient(135deg, var(--sunny-yellow) 0%, #EAB308 100%);
            color: white;
        }
        
        .btn-notifications {
            background: linear-gradient(135deg, var(--purple) 0%, #7C3AED 100%);
            color: white;
        }
        
        /* Lista de actividad */
        .activity-item {
            padding: 1rem;
            border-left: 5px solid;
            margin-bottom: 1rem;
            background: #F9FAFB;
            border-radius: 15px;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .activity-students { border-color: var(--sky-blue); }
        .activity-grades { border-color: var(--coral); }
        .activity-schedule { border-color: var(--orange); }
        
        .activity-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        .activity-students .activity-icon {
            background: linear-gradient(135deg, var(--sky-blue) 0%, #2196F3 100%);
            color: white;
        }
        
        .activity-grades .activity-icon {
            background: linear-gradient(135deg, var(--coral) 0%, #EF4444 100%);
            color: white;
        }
        
        .activity-schedule .activity-icon {
            background: linear-gradient(135deg, var(--orange) 0%, #EA580C 100%);
            color: white;
        }
        
        /* Contenedor principal */
        .main-container {
            background: linear-gradient(135deg, rgba(224, 242, 254, 0.5) 0%, rgba(254, 243, 199, 0.5) 50%, rgba(252, 231, 243, 0.5) 100%);
            padding: 2rem;
            min-height: calc(100vh - 100px);
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, var(--sunny-yellow) 0%, var(--orange) 100%);
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            animation: slideDown 0.8s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .welcome-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.2);
            margin-bottom: 0.5rem;
        }
        
        .welcome-subtitle {
            font-size: 1.2rem;
            color: rgba(255,255,255,0.95);
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .stat-number {
                font-size: 2.5rem;
            }
            
            .welcome-title {
                font-size: 1.8rem;
            }
            
            .stat-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar Infantil -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-school">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>
                <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    
                    <?php if (hasPermission('admin') || hasPermission('profesor')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="estudiantesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-graduate me-1"></i>Estudiantes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/estudiantes/listar.php">Listar Estudiantes</a></li>
                            <li><a class="dropdown-item" href="modules/estudiantes/agregar.php">Agregar Estudiante</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('admin')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profesoresDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chalkboard-teacher me-1"></i>Profesores
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/profesores/listar.php">Listar Profesores</a></li>
                            <li><a class="dropdown-item" href="modules/profesores/agregar.php">Agregar Profesor</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="materiasDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-book me-1"></i>Materias
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/materias/listar.php">Listar Materias</a></li>
                            <?php if (hasPermission('admin')): ?>
                            <li><a class="dropdown-item" href="modules/materias/agregar.php">Agregar Materia</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="calificacionesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-line me-1"></i>Calificaciones
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/calificaciones/listar.php">Ver Calificaciones</a></li>
                            <?php if (hasPermission('profesor')): ?>
                            <li><a class="dropdown-item" href="modules/calificaciones/agregar.php">Agregar Calificación</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    
                    <?php if (hasPermission('admin')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="asistenciasDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-check me-1"></i>Asistencias
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/asistencias/listar.php">Listar Asistencias</a></li>
                            <li><a class="dropdown-item" href="modules/asistencias/agregar.php">Registrar Asistencia</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="administracionDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs me-1"></i>Administración
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/usuarios/listar.php">Usuarios</a></li>
                            <li><a class="dropdown-item" href="modules/grupos/listar.php">Grupos</a></li>
                            <li><a class="dropdown-item" href="modules/aulas/listar.php">Aulas</a></li>
                            <li><a class="dropdown-item" href="modules/horarios/listar.php">Horarios</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="modules/eventos/listar.php"><i class="fas fa-calendar-alt me-1"></i>Eventos</a></li>
                            <li><a class="dropdown-item" href="modules/biblioteca/listar.php"><i class="fas fa-book-reader me-1"></i>Biblioteca</a></li>
                            <li><a class="dropdown-item" href="modules/inventario/listar.php"><i class="fas fa-boxes me-1"></i>Inventario</a></li>
                            <li><a class="dropdown-item" href="modules/pagos/listar.php"><i class="fas fa-money-bill-wave me-1"></i>Pagos</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="modules/notificaciones/listar.php">
                            <i class="fas fa-bell me-1"></i>Notificaciones
                            <?php
                            try {
                                $pdo = conectarDB();
                                $user_id = $_SESSION['user_id'];
                                $no_leidas = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leida = 0");
                                $no_leidas->execute([$user_id]);
                                $count = $no_leidas->fetchColumn();
                                if ($count > 0) {
                                    echo '<span class="badge bg-danger ms-1">' . $count . '</span>';
                                }
                            } catch (Exception $e) {}
                            ?>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="modules/reportes/index.php">
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
                            <li><a class="dropdown-item" href="profile.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="main-container">
        <!-- Banner de Bienvenida -->
        <div class="welcome-banner">
            <h1 class="welcome-title">
                <i class="fas fa-star me-2"></i>
                ¡Bienvenido, <?php echo ucfirst($_SESSION['username']); ?>! 🎉
            </h1>
            <p class="welcome-subtitle">
                <i class="fas fa-school me-2"></i>
                Sistema de Gestión Escolar - ¡Aquí está todo controlado!
            </p>
        </div>
        
        <!-- Tarjetas de Estadísticas -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card students">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['total_estudiantes']; ?></h3>
                    <p class="stat-label">Estudiantes 🎓</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card teachers">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['total_profesores']; ?></h3>
                    <p class="stat-label">Profesores 👨‍🏫</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card subjects">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['total_materias']; ?></h3>
                    <p class="stat-label">Materias 📚</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card groups">
                    <div class="stat-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['total_grupos']; ?></h3>
                    <p class="stat-label">Grupos 👥</p>
                </div>
            </div>
        </div>
        
        <!-- Accesos Rápidos y Actividad -->
        <div class="row">
            <!-- Accesos Rápidos -->
            <div class="col-lg-8 mb-4">
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-rocket"></i>
                        Accesos Rápidos
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <?php if (hasPermission('admin') || hasPermission('profesor')): ?>
                            <div class="col-md-6">
                                <a href="modules/estudiantes/listar.php" class="btn btn-school btn-students w-100">
                                    <i class="fas fa-user-graduate me-2"></i>
                                    Gestionar Estudiantes
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('admin')): ?>
                            <div class="col-md-6">
                                <a href="modules/profesores/listar.php" class="btn btn-school btn-teachers w-100">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>
                                    Gestionar Profesores
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-md-6">
                                <a href="modules/materias/listar.php" class="btn btn-school btn-subjects w-100">
                                    <i class="fas fa-book me-2"></i>
                                    Ver Materias
                                </a>
                            </div>
                            
                            <div class="col-md-6">
                                <a href="modules/calificaciones/listar.php" class="btn btn-school btn-grades w-100">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Ver Calificaciones
                                </a>
                            </div>
                            
                            <?php if (hasPermission('admin')): ?>
                            <div class="col-md-6">
                                <a href="modules/asistencias/listar.php" class="btn btn-school btn-attendance w-100">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Ver Asistencias
                                </a>
                            </div>
                            
                            <div class="col-md-6">
                                <a href="modules/eventos/listar.php" class="btn btn-school btn-events w-100">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Ver Eventos
                                </a>
                            </div>
                            
                            <div class="col-md-6">
                                <a href="modules/biblioteca/listar.php" class="btn btn-school btn-library w-100">
                                    <i class="fas fa-book-reader me-2"></i>
                                    Biblioteca
                                </a>
                            </div>
                            
                            <div class="col-md-6">
                                <a href="modules/inventario/listar.php" class="btn btn-school btn-inventory w-100">
                                    <i class="fas fa-boxes me-2"></i>
                                    Inventario
                                </a>
                            </div>
                            
                            <div class="col-md-6">
                                <a href="modules/pagos/listar.php" class="btn btn-school btn-payments w-100">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    Pagos
                                </a>
                            </div>
                            
                            <div class="col-md-6">
                                <a href="modules/notificaciones/listar.php" class="btn btn-school btn-notifications w-100">
                                    <i class="fas fa-bell me-2"></i>
                                    Notificaciones
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actividad Reciente -->
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-history"></i>
                        Actividad Reciente
                    </div>
                    <div class="card-body p-4">
                        <div class="activity-item activity-students d-flex">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong>Estudiante Registrado</strong>
                                <p class="mb-0 text-muted">Hace 2 horas</p>
                            </div>
                        </div>
                        
                        <div class="activity-item activity-grades d-flex">
                            <div class="activity-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong>Calificación Actualizada</strong>
                                <p class="mb-0 text-muted">Hace 4 horas</p>
                            </div>
                        </div>
                        
                        <div class="activity-item activity-schedule d-flex">
                            <div class="activity-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong>Nuevo Horario</strong>
                                <p class="mb-0 text-muted">Ayer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>


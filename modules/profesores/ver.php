<?php
/**
 * Ver Detalles del Profesor
 * Sistema de Control Escolar
 */

// Configurar codificación UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn() || (!hasPermission('admin') && !hasPermission('profesor'))) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Detalles del Profesor';

// Obtener ID del profesor
$profesor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($profesor_id <= 0) {
    $_SESSION['error'] = 'ID de profesor no válido';
    redirect('index.php');
}

// Obtener datos del profesor
try {
    $pdo = conectarDB();
    
    $sql = "SELECT * FROM profesores WHERE id = ? AND estado = 'activo'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$profesor_id]);
    $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profesor) {
        $_SESSION['error'] = 'Profesor no encontrado';
        redirect('index.php');
    }
    
    // Calcular edad si tiene fecha de nacimiento
    $edad = null;
    if (!empty($profesor['fecha_nacimiento'])) {
        $fecha_nacimiento = new DateTime($profesor['fecha_nacimiento']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha_nacimiento)->y;
    }
    
    // Calcular años de experiencia
    $fecha_ingreso = new DateTime($profesor['fecha_ingreso']);
    $hoy = new DateTime();
    $experiencia = $hoy->diff($fecha_ingreso)->y;
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos del profesor: ' . $e->getMessage();
    redirect('index.php');
}

include __DIR__ . '/../../includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        .info-card {
            border-left: 4px solid #0d6efd;
            transition: transform 0.2s;
        }
        .info-card:hover {
            transform: translateY(-2px);
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .stat-item {
            text-align: center;
            padding: 15px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .specialty-badge {
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0">
                            <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>
                            <?php echo $page_title; ?>
                        </h2>
                        <p class="text-muted mb-0">Información detallada del profesor</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
                        <a href="editar.php?id=<?php echo $profesor['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    </div>
                </div>

                <!-- Perfil del profesor -->
                <div class="profile-header p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-1">
                                <?php echo htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido_paterno'] . ' ' . $profesor['apellido_materno']); ?>
                            </h3>
                            <p class="mb-2">
                                <i class="fas fa-id-card me-2"></i>
                                Código: <strong><?php echo htmlspecialchars($profesor['codigo']); ?></strong>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-graduation-cap me-2"></i>
                                <span class="specialty-badge"><?php echo htmlspecialchars($profesor['especialidad']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $experiencia; ?></div>
                                <div>Años de experiencia</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información detallada -->
                <div class="row">
                    <!-- Información Personal -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2"></i>Información Personal
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Nombre:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($profesor['nombre']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Apellido Paterno:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($profesor['apellido_paterno']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Apellido Materno:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($profesor['apellido_materno'] ?? 'No especificado'); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Fecha de Nacimiento:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo !empty($profesor['fecha_nacimiento']) ? date('d/m/Y', strtotime($profesor['fecha_nacimiento'])) : 'No especificada'; ?>
                                    </div>
                                </div>
                                <?php if ($edad !== null): ?>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Edad:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo $edad; ?> años
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Estado:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-<?php echo $profesor['estado'] == 'activo' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($profesor['estado']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Profesional -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-graduation-cap me-2"></i>Información Profesional
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Especialidad:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="specialty-badge">
                                            <?php echo htmlspecialchars($profesor['especialidad']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Fecha de Ingreso:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo date('d/m/Y', strtotime($profesor['fecha_ingreso'])); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Años de Experiencia:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo $experiencia; ?> años
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Salario:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php if (!empty($profesor['salario'])): ?>
                                        <strong class="text-success">$<?php echo number_format($profesor['salario'], 2); ?></strong>
                                        <?php else: ?>
                                        <span class="text-muted">No especificado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-envelope me-2"></i>Información de Contacto
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Email:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php if (!empty($profesor['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($profesor['email']); ?>" class="text-decoration-none">
                                            <i class="fas fa-envelope me-1"></i>
                                            <?php echo htmlspecialchars($profesor['email']); ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">No especificado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Teléfono:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php if (!empty($profesor['telefono'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($profesor['telefono']); ?>" class="text-decoration-none">
                                            <i class="fas fa-phone me-1"></i>
                                            <?php echo htmlspecialchars($profesor['telefono']); ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">No especificado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Dirección:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php if (!empty($profesor['direccion'])): ?>
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($profesor['direccion']); ?>
                                        <?php else: ?>
                                        <span class="text-muted">No especificada</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Sistema -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Información del Sistema
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>ID:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        #<?php echo $profesor['id']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Código:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-info fs-6">
                                            <?php echo htmlspecialchars($profesor['codigo']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Fecha de Registro:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($profesor['fecha_creacion'])); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Última Actualización:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($profesor['fecha_actualizacion'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-3">Acciones Disponibles</h5>
                        <div class="btn-group" role="group">
                            <a href="editar.php?id=<?php echo $profesor['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Editar Profesor
                            </a>
                            <button type="button" class="btn btn-danger" onclick="eliminarProfesor(<?php echo $profesor['id']; ?>, '<?php echo htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido_paterno']); ?>')">
                                <i class="fas fa-trash me-2"></i>Eliminar Profesor
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-list me-2"></i>Ver Lista Completa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para eliminar profesor
        function eliminarProfesor(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas eliminar al profesor "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                // Crear formulario para enviar POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>


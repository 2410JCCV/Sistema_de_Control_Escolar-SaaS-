<?php
/**
 * Ver Detalles de la Calificación
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

$page_title = 'Detalles de la Calificación';

// Obtener ID de la calificación
$calificacion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($calificacion_id <= 0) {
    $_SESSION['error'] = 'ID de calificación no válido';
    redirect('index.php');
}

// Obtener datos de la calificación
try {
    $pdo = conectarDB();
    
    $sql = "
        SELECT 
            c.*,
            e.nombre as estudiante_nombre,
            e.apellido_paterno as estudiante_apellido,
            e.apellido_materno as estudiante_apellido_materno,
            m.nombre as materia_nombre,
            p.nombre as profesor_nombre,
            p.apellido_paterno as profesor_apellido,
            p.apellido_materno as profesor_apellido_materno
        FROM calificaciones c
        LEFT JOIN estudiantes e ON c.estudiante_id = e.id
        LEFT JOIN materias m ON c.materia_id = m.id
        LEFT JOIN profesores p ON c.profesor_id = p.id
        WHERE c.id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$calificacion_id]);
    $calificacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$calificacion) {
        $_SESSION['error'] = 'Calificación no encontrada';
        redirect('index.php');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos de la calificación: ' . $e->getMessage();
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
        .calificacion-header {
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
        .calificacion-badge {
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
        }
        .tipo-badge {
            background: linear-gradient(45deg, #a8e6cf, #88d8c0);
            color: #2d5a27;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
        }
        .nota-excelente { color: #28a745; font-weight: bold; }
        .nota-buena { color: #17a2b8; font-weight: bold; }
        .nota-regular { color: #ffc107; font-weight: bold; }
        .nota-mala { color: #dc3545; font-weight: bold; }
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
                            <i class="fas fa-chart-line me-2 text-primary"></i>
                            <?php echo $page_title; ?>
                        </h2>
                        <p class="text-muted mb-0">Información detallada de la calificación</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
                        <a href="editar.php?id=<?php echo $calificacion['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    </div>
                </div>

                <!-- Perfil de la calificación -->
                <div class="calificacion-header p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-1">
                                <?php echo htmlspecialchars(($calificacion['estudiante_nombre'] ?? '') . ' ' . ($calificacion['estudiante_apellido'] ?? '') . ' ' . ($calificacion['estudiante_apellido_materno'] ?? '')); ?>
                            </h3>
                            <p class="mb-2">
                                <i class="fas fa-book me-2"></i>
                                Materia: <strong><?php echo htmlspecialchars($calificacion['materia_nombre'] ?? ''); ?></strong>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-chalkboard-teacher me-2"></i>
                                Profesor: <strong><?php echo htmlspecialchars(($calificacion['profesor_nombre'] ?? '') . ' ' . ($calificacion['profesor_apellido'] ?? '') . ' ' . ($calificacion['profesor_apellido_materno'] ?? '')); ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stat-item">
                                <?php
                                $nota = $calificacion['calificacion'];
                                $clase_nota = '';
                                if ($nota >= 90) $clase_nota = 'nota-excelente';
                                elseif ($nota >= 80) $clase_nota = 'nota-buena';
                                elseif ($nota >= 70) $clase_nota = 'nota-regular';
                                else $clase_nota = 'nota-mala';
                                ?>
                                <div class="stat-value <?php echo $clase_nota; ?>"><?php echo number_format($nota, 2); ?></div>
                                <div>Calificación</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información detallada -->
                <div class="row">
                    <!-- Información del Estudiante -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-graduate me-2"></i>Información del Estudiante
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Nombre:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars(($calificacion['estudiante_nombre'] ?? '') . ' ' . ($calificacion['estudiante_apellido'] ?? '') . ' ' . ($calificacion['estudiante_apellido_materno'] ?? '')); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>ID:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        #<?php echo $calificacion['estudiante_id']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Materia -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-book me-2"></i>Información de la Materia
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Materia:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-info fs-6">
                                            <?php echo htmlspecialchars($calificacion['materia_nombre'] ?? ''); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>ID:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        #<?php echo $calificacion['materia_id']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Profesor -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>Información del Profesor
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Nombre:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars(($calificacion['profesor_nombre'] ?? '') . ' ' . ($calificacion['profesor_apellido'] ?? '') . ' ' . ($calificacion['profesor_apellido_materno'] ?? '')); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>ID:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        #<?php echo $calificacion['profesor_id']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Evaluación -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Información de la Evaluación
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Calificación:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="calificacion-badge <?php echo $clase_nota; ?>">
                                            <?php echo number_format($calificacion['calificacion'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Tipo:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="tipo-badge">
                                            <?php echo ucfirst($calificacion['tipo_evaluacion']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Fecha:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($calificacion['fecha_evaluacion'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="col-lg-12 mb-4">
                        <div class="card info-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-comment me-2"></i>Observaciones
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($calificacion['observaciones'])): ?>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($calificacion['observaciones'] ?? '')); ?></p>
                                <?php else: ?>
                                <p class="text-muted mb-0">No hay observaciones para esta calificación.</p>
                                <?php endif; ?>
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
                                        #<?php echo $calificacion['id']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Fecha de Registro:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($calificacion['fecha_creacion'])); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Última Actualización:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($calificacion['fecha_actualizacion'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Estadísticas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div class="text-center">
                                            <div class="stat-value <?php echo $clase_nota; ?>"><?php echo number_format($calificacion['calificacion'], 1); ?></div>
                                            <div>Puntuación</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-center">
                                            <div class="stat-value text-info"><?php echo ucfirst($calificacion['tipo_evaluacion']); ?></div>
                                            <div>Tipo</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="text-center">
                                            <?php
                                            $rendimiento = '';
                                            if ($nota >= 90) $rendimiento = 'Excelente';
                                            elseif ($nota >= 80) $rendimiento = 'Bueno';
                                            elseif ($nota >= 70) $rendimiento = 'Regular';
                                            else $rendimiento = 'Necesita Mejorar';
                                            ?>
                                            <div class="stat-value text-primary"><?php echo $rendimiento; ?></div>
                                            <div>Rendimiento</div>
                                        </div>
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
                            <a href="editar.php?id=<?php echo $calificacion['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Editar Calificación
                            </a>
                            <button type="button" class="btn btn-danger" onclick="eliminarCalificacion(<?php echo $calificacion['id']; ?>, '<?php echo htmlspecialchars(($calificacion['estudiante_nombre'] ?? '') . ' ' . ($calificacion['estudiante_apellido'] ?? '')); ?>')">
                                <i class="fas fa-trash me-2"></i>Eliminar Calificación
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
        // Función para eliminar calificación
        function eliminarCalificacion(id, estudiante) {
            if (confirm(`¿Estás seguro de que deseas eliminar la calificación de "${estudiante}"?\n\nEsta acción no se puede deshacer.`)) {
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

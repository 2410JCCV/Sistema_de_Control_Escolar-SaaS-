<?php
/**
 * Ver Detalles de la Materia
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

$page_title = 'Detalles de la Materia';

// Obtener ID de la materia
$materia_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($materia_id <= 0) {
    $_SESSION['error'] = 'ID de materia no válido';
    redirect('index.php');
}

// Obtener datos de la materia
try {
    $pdo = conectarDB();
    
    $sql = "
        SELECT 
            m.*,
            g.nombre as grado_nombre
        FROM materias m
        LEFT JOIN grados g ON m.grado_id = g.id
        WHERE m.id = ? AND m.estado = 'activo'
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$materia_id]);
    $materia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$materia) {
        $_SESSION['error'] = 'Materia no encontrada';
        redirect('index.php');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos de la materia: ' . $e->getMessage();
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
        .materia-header {
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
        .credits-badge {
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
        }
        .grado-badge {
            background: linear-gradient(45deg, #a8e6cf, #88d8c0);
            color: #2d5a27;
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
                            <i class="fas fa-book me-2 text-primary"></i>
                            <?php echo $page_title; ?>
                        </h2>
                        <p class="text-muted mb-0">Información detallada de la materia</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
                        <a href="editar.php?id=<?php echo $materia['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    </div>
                </div>

                <!-- Perfil de la materia -->
                <div class="materia-header p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-1">
                                <?php echo htmlspecialchars($materia['nombre']); ?>
                            </h3>
                            <p class="mb-2">
                                <i class="fas fa-id-card me-2"></i>
                                Código: <strong><?php echo htmlspecialchars($materia['codigo']); ?></strong>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-graduation-cap me-2"></i>
                                <span class="grado-badge"><?php echo htmlspecialchars($materia['grado_nombre'] ?? 'Sin grado'); ?></span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $materia['creditos'] ?? 0; ?></div>
                                <div>Créditos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información detallada -->
                <div class="row">
                    <!-- Información Básica -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-book me-2"></i>Información Básica
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Nombre:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo htmlspecialchars($materia['nombre']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Código:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-info fs-6">
                                            <?php echo htmlspecialchars($materia['codigo']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Créditos:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="credits-badge">
                                            <?php echo $materia['creditos'] ?? 0; ?> créditos
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Estado:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-<?php echo $materia['estado'] == 'activo' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($materia['estado']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Académica -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-graduation-cap me-2"></i>Información Académica
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Grado:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="grado-badge">
                                            <?php echo htmlspecialchars($materia['grado_nombre'] ?? 'Sin grado'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>ID del Grado:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        #<?php echo $materia['grado_id']; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Carga Académica:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php if (!empty($materia['creditos'])): ?>
                                        <strong class="text-success"><?php echo $materia['creditos']; ?> créditos</strong>
                                        <?php else: ?>
                                        <span class="text-muted">No especificada</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="col-lg-12 mb-4">
                        <div class="card info-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-align-left me-2"></i>Descripción
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($materia['descripcion'])): ?>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($materia['descripcion'])); ?></p>
                                <?php else: ?>
                                <p class="text-muted mb-0">No hay descripción disponible para esta materia.</p>
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
                                        #<?php echo $materia['id']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Código:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-info fs-6">
                                            <?php echo htmlspecialchars($materia['codigo']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Fecha de Registro:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($materia['fecha_creacion'])); ?>
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
                                            <div class="stat-value text-primary"><?php echo $materia['creditos'] ?? 0; ?></div>
                                            <div>Créditos</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-center">
                                            <div class="stat-value text-success">1</div>
                                            <div>Grado</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="text-center">
                                            <div class="stat-value text-info"><?php echo $materia['estado'] == 'activo' ? 'Activa' : 'Inactiva'; ?></div>
                                            <div>Estado</div>
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
                            <a href="editar.php?id=<?php echo $materia['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Editar Materia
                            </a>
                            <button type="button" class="btn btn-danger" onclick="eliminarMateria(<?php echo $materia['id']; ?>, '<?php echo htmlspecialchars($materia['nombre']); ?>')">
                                <i class="fas fa-trash me-2"></i>Eliminar Materia
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
        // Función para eliminar materia
        function eliminarMateria(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas eliminar la materia "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
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


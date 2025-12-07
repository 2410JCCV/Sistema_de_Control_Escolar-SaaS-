<?php
/**
 * Ver Detalles del Grupo
 * Sistema de Control Escolar
 */

// Configurar codificación UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn() || !hasPermission('admin')) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Detalles del Grupo';

// Obtener ID del grupo
$grupo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($grupo_id <= 0) {
    $_SESSION['error'] = 'ID de grupo no válido';
    redirect('index.php');
}

// Obtener datos del grupo
try {
    $pdo = conectarDB();
    
    $sql = "
        SELECT g.*, gr.nombre as grado_nombre
        FROM grupos g
        LEFT JOIN grados gr ON g.grado_id = gr.id
        WHERE g.id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$grupo_id]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$grupo) {
        $_SESSION['error'] = 'Grupo no encontrado';
        redirect('index.php');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos del grupo: ' . $e->getMessage();
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
        .grupo-header {
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
        .grado-badge {
            background: linear-gradient(45deg, #a8e6cf, #88d8c0);
            color: #2d5a27;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
        }
        .estado-activo { color: #28a745; font-weight: bold; }
        .estado-inactivo { color: #dc3545; font-weight: bold; }
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
                            <i class="fas fa-layer-group me-2 text-primary"></i>
                            <?php echo $page_title; ?>
                        </h2>
                        <p class="text-muted mb-0">Información detallada del grupo</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
                        <a href="editar.php?id=<?php echo $grupo['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    </div>
                </div>

                <!-- Perfil del grupo -->
                <div class="grupo-header p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-1">
                                Grupo <?php echo htmlspecialchars($grupo['nombre']); ?>
                            </h3>
                            <p class="mb-2">
                                <i class="fas fa-graduation-cap me-2"></i>
                                Grado: <strong><?php echo htmlspecialchars($grupo['grado_nombre'] ?? 'Sin grado'); ?></strong>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                Capacidad: <strong><?php echo $grupo['capacidad'] ?? 'Sin límite'; ?> estudiantes</strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stat-item">
                                <div class="stat-value text-white">#<?php echo $grupo['id']; ?></div>
                                <div>ID Grupo</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información detallada -->
                <div class="row">
                    <!-- Información del Grupo -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-layer-group me-2"></i>Información del Grupo
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Nombre:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <strong><?php echo htmlspecialchars($grupo['nombre']); ?></strong>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Grado:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge grado-badge">
                                            <?php echo htmlspecialchars($grupo['grado_nombre'] ?? 'Sin grado'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Capacidad:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-users me-1"></i>
                                        <?php echo $grupo['capacidad'] ?? 'Sin límite'; ?> estudiantes
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Estado:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php
                                        $clase_estado = $grupo['estado'] === 'activo' ? 'estado-activo' : 'estado-inactivo';
                                        ?>
                                        <span class="<?php echo $clase_estado; ?>">
                                            <i class="fas fa-circle me-1"></i>
                                            <?php echo ucfirst($grupo['estado']); ?>
                                        </span>
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
                                        #<?php echo $grupo['id']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Grado ID:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        #<?php echo $grupo['grado_id']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Fecha de Creación:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($grupo['fecha_creacion'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="col-lg-12 mb-4">
                        <div class="card info-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Estadísticas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="stat-value text-primary"><?php echo htmlspecialchars($grupo['nombre']); ?></div>
                                            <div>Nombre del Grupo</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="stat-value text-info"><?php echo htmlspecialchars($grupo['grado_nombre'] ?? 'Sin grado'); ?></div>
                                            <div>Grado Asignado</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="stat-value text-success"><?php echo $grupo['capacidad'] ?? '∞'; ?></div>
                                            <div>Capacidad</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="stat-value text-warning">#<?php echo $grupo['id']; ?></div>
                                            <div>ID del Grupo</div>
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
                            <a href="editar.php?id=<?php echo $grupo['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Editar Grupo
                            </a>
                            <button type="button" class="btn btn-danger" onclick="eliminarGrupo(<?php echo $grupo['id']; ?>, '<?php echo htmlspecialchars($grupo['nombre']); ?>')">
                                <i class="fas fa-trash me-2"></i>Eliminar Grupo
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
        // Función para eliminar grupo
        function eliminarGrupo(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas eliminar el grupo "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
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



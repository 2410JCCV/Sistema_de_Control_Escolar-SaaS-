<?php
/**
 * Ver Detalles del Horario
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

$page_title = 'Detalles del Horario';

// Obtener ID del horario
$horario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($horario_id <= 0) {
    $_SESSION['error'] = 'ID de horario no válido';
    redirect('index.php');
}

// Obtener datos del horario
try {
    $pdo = conectarDB();
    
    $sql = "
        SELECT 
            h.*,
            m.nombre as materia_nombre,
            p.nombre as profesor_nombre, p.apellido_paterno as profesor_apellido,
            g.nombre as grupo_nombre, gr.nombre as grado_nombre,
            a.nombre as aula_nombre, a.ubicacion as aula_ubicacion
        FROM horarios h
        LEFT JOIN materias m ON h.materia_id = m.id
        LEFT JOIN profesores p ON h.profesor_id = p.id
        LEFT JOIN grupos g ON h.grupo_id = g.id
        LEFT JOIN grados gr ON g.grado_id = gr.id
        LEFT JOIN aulas a ON h.aula_id = a.id
        WHERE h.id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$horario_id]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$horario) {
        $_SESSION['error'] = 'Horario no encontrado';
        redirect('index.php');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos del horario: ' . $e->getMessage();
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
        .horario-header {
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
        .dia-badge {
            background: linear-gradient(45deg, #a8e6cf, #88d8c0);
            color: #2d5a27;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
        }
        .hora-badge {
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            color: white;
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
                            <i class="fas fa-clock me-2 text-primary"></i>
                            <?php echo $page_title; ?>
                        </h2>
                        <p class="text-muted mb-0">Información detallada del horario</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
                        <a href="editar.php?id=<?php echo $horario['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    </div>
                </div>

                <!-- Perfil del horario -->
                <div class="horario-header p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-1">
                                <?php echo htmlspecialchars($horario['materia_nombre'] ?? 'Sin materia'); ?>
                            </h3>
                            <p class="mb-2">
                                <i class="fas fa-chalkboard-teacher me-2"></i>
                                Profesor: <strong><?php echo htmlspecialchars(($horario['profesor_nombre'] ?? '') . ' ' . ($horario['profesor_apellido'] ?? '')); ?></strong>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                Grupo: <strong><?php echo htmlspecialchars($horario['grado_nombre'] ?? '') . ' - ' . htmlspecialchars($horario['grupo_nombre'] ?? 'Sin grupo'); ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stat-item">
                                <div class="stat-value text-white">#<?php echo $horario['id']; ?></div>
                                <div>ID Horario</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información detallada -->
                <div class="row">
                    <!-- Información de la Clase -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-book me-2"></i>Información de la Clase
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Materia:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($horario['materia_nombre'] ?? 'Sin materia'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Profesor:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-chalkboard-teacher me-1"></i>
                                        <?php echo htmlspecialchars(($horario['profesor_nombre'] ?? '') . ' ' . ($horario['profesor_apellido'] ?? '')); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Grupo:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($horario['grado_nombre'] ?? '') . ' - ' . htmlspecialchars($horario['grupo_nombre'] ?? 'Sin grupo'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Aula:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-door-open me-1"></i>
                                        <?php echo htmlspecialchars($horario['aula_nombre'] ?? 'Sin aula'); ?>
                                        <?php if ($horario['aula_ubicacion']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($horario['aula_ubicacion']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Horario -->
                    <div class="col-lg-6 mb-4">
                        <div class="card info-card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>Información del Horario
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Día:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="badge dia-badge">
                                            <?php echo ucfirst($horario['dia_semana']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Horario:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <span class="hora-badge">
                                            <?php echo date('H:i', strtotime($horario['hora_inicio'])); ?> - 
                                            <?php echo date('H:i', strtotime($horario['hora_fin'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Duración:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php
                                        $inicio = strtotime($horario['hora_inicio']);
                                        $fin = strtotime($horario['hora_fin']);
                                        $duracion = ($fin - $inicio) / 60; // en minutos
                                        echo $duracion . ' minutos';
                                        ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Estado:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php
                                        $clase_estado = $horario['estado'] === 'activo' ? 'estado-activo' : 'estado-inactivo';
                                        ?>
                                        <span class="<?php echo $clase_estado; ?>">
                                            <i class="fas fa-circle me-1"></i>
                                            <?php echo ucfirst($horario['estado']); ?>
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
                                        #<?php echo $horario['id']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Materia ID:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        #<?php echo $horario['materia_id']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Profesor ID:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        #<?php echo $horario['profesor_id']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Grupo ID:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        #<?php echo $horario['grupo_id']; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Aula ID:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        #<?php echo $horario['aula_id']; ?>
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
                                    <div class="col-sm-4">
                                        <strong>Fecha de Creación:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($horario['fecha_creacion'])); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Duración:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo $duracion; ?> minutos
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
                            <a href="editar.php?id=<?php echo $horario['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Editar Horario
                            </a>
                            <button type="button" class="btn btn-danger" onclick="eliminarHorario(<?php echo $horario['id']; ?>, '<?php echo htmlspecialchars($horario['materia_nombre'] ?? 'Sin materia'); ?>')">
                                <i class="fas fa-trash me-2"></i>Eliminar Horario
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
        // Función para eliminar horario
        function eliminarHorario(id, materia) {
            if (confirm(`¿Estás seguro de que deseas eliminar el horario de "${materia}"?\n\nEsta acción no se puede deshacer.`)) {
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



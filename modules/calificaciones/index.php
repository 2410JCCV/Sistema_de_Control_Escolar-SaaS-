<?php
/**
 * Módulo de Calificaciones - Listado Principal
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

$page_title = 'Gestión de Calificaciones';

// Obtener parámetros de filtrado y paginación
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$estudiante_id = isset($_GET['estudiante_id']) ? (int)$_GET['estudiante_id'] : 0;
$materia_id = isset($_GET['materia_id']) ? (int)$_GET['materia_id'] : 0;
$tipo_evaluacion = isset($_GET['tipo_evaluacion']) ? trim($_GET['tipo_evaluacion']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10; // Calificaciones por página

// Obtener datos de la base de datos
try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = [];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(e.nombre LIKE :search OR e.apellido_paterno LIKE :search OR m.nombre LIKE :search OR p.nombre LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por estudiante
    if ($estudiante_id > 0) {
        $where_conditions[] = "c.estudiante_id = :estudiante_id";
        $params['estudiante_id'] = $estudiante_id;
    }
    
    // Filtro por materia
    if ($materia_id > 0) {
        $where_conditions[] = "c.materia_id = :materia_id";
        $params['materia_id'] = $materia_id;
    }
    
    // Filtro por tipo de evaluación
    if (!empty($tipo_evaluacion)) {
        $where_conditions[] = "c.tipo_evaluacion = :tipo_evaluacion";
        $params['tipo_evaluacion'] = $tipo_evaluacion;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Contar total de registros
    $count_sql = "
        SELECT COUNT(*) as total
        FROM calificaciones c
        LEFT JOIN estudiantes e ON c.estudiante_id = e.id
        LEFT JOIN materias m ON c.materia_id = m.id
        LEFT JOIN profesores p ON c.profesor_id = p.id
        {$where_clause}
    ";
    
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    
    // Calcular paginación
    $total_pages = ceil($total_records / $limit);
    $offset = ($page - 1) * $limit;
    
    // Consulta principal
    $sql = "
        SELECT 
            c.id,
            c.estudiante_id,
            c.materia_id,
            c.profesor_id,
            c.tipo_evaluacion,
            c.calificacion,
            c.fecha_evaluacion,
            c.observaciones,
            c.fecha_creacion,
            e.nombre as estudiante_nombre,
            e.apellido_paterno as estudiante_apellido,
            e.apellido_materno as estudiante_apellido_materno,
            m.nombre as materia_nombre,
            p.nombre as profesor_nombre,
            p.apellido_paterno as profesor_apellido
        FROM calificaciones c
        LEFT JOIN estudiantes e ON c.estudiante_id = e.id
        LEFT JOIN materias m ON c.materia_id = m.id
        LEFT JOIN profesores p ON c.profesor_id = p.id
        {$where_clause}
        ORDER BY c.fecha_evaluacion DESC, c.id DESC
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener estudiantes únicos para el filtro
    $estudiantes_sql = "SELECT DISTINCT e.id, CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as nombre_completo 
                        FROM estudiantes e 
                        INNER JOIN calificaciones c ON e.id = c.estudiante_id 
                        WHERE e.estado = 'activo' 
                        ORDER BY e.nombre";
    $estudiantes = $pdo->query($estudiantes_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener materias únicas para el filtro
    $materias_sql = "SELECT DISTINCT m.id, m.nombre 
                     FROM materias m 
                     INNER JOIN calificaciones c ON m.id = c.materia_id 
                     WHERE m.estado = 'activo' 
                     ORDER BY m.nombre";
    $materias = $pdo->query($materias_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Tipos de evaluación disponibles
    $tipos_evaluacion = ['examen', 'tarea', 'proyecto', 'participacion', 'practica'];
    
} catch (Exception $e) {
    $calificaciones = [];
    $total_records = 0;
    $total_pages = 0;
    $estudiantes = [];
    $materias = [];
    $tipos_evaluacion = [];
    $error_message = "Error al cargar los datos: " . $e->getMessage();
}

// Verificar mensaje de éxito
$success_message = '';
if (isset($_GET['success'])) {
    $success_message = urldecode($_GET['success']);
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
    <link href="../../assets/css/dashboard-style.css" rel="stylesheet">
    <style>
        body { font-family: 'Comic Sans MS', 'Chalkboard', 'Marker Felt', cursive; }
        .main-container {
            background: linear-gradient(135deg, rgba(224, 242, 254, 0.5) 0%, rgba(254, 243, 199, 0.5) 50%, rgba(252, 231, 243, 0.5) 100%);
            padding: 2rem;
            min-height: calc(100vh - 100px);
        }
        .page-header {
            background: linear-gradient(135deg, var(--purple) 0%, #7C3AED 100%);
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            color: white;
        }
        .page-header h2 {
            color: white;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.2);
        }
        .nota-excelente { color: #28a745; font-weight: bold; }
        .nota-buena { color: #17a2b8; font-weight: bold; }
        .nota-regular { color: #ffc107; font-weight: bold; }
        .nota-mala { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body class="dashboard-style">
    <div class="main-container">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                <?php echo $page_title; ?>
                            </h2>
                            <p class="mb-0 mt-2">Gestiona las calificaciones de los estudiantes</p>
                        </div>
                        <div>
                            <a href="agregar.php" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>Agregar Calificación
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mensaje de éxito -->
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-module alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card groups">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($total_records); ?></h3>
                            <p class="stat-label">Total Calificaciones</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card students">
                            <div class="stat-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h3 class="stat-number"><?php echo count($estudiantes); ?></h3>
                            <p class="stat-label">Estudiantes</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card subjects">
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h3 class="stat-number"><?php echo count($materias); ?></h3>
                            <p class="stat-label">Materias</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card teachers">
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="stat-number"><?php echo $total_pages; ?></h3>
                            <p class="stat-label">Páginas</p>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-filter"></i>
                        Filtros de Búsqueda
                    </div>
                    <div class="card-body p-4">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Estudiante, materia o profesor">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="estudiante_id" class="form-label">Estudiante</label>
                                <select class="form-select" id="estudiante_id" name="estudiante_id">
                                    <option value="">Todos los estudiantes</option>
                                    <?php foreach ($estudiantes as $estudiante): ?>
                                    <option value="<?php echo $estudiante['id']; ?>" 
                                            <?php echo ($estudiante_id == $estudiante['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($estudiante['nombre_completo']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="materia_id" class="form-label">Materia</label>
                                <select class="form-select" id="materia_id" name="materia_id">
                                    <option value="">Todas las materias</option>
                                    <?php foreach ($materias as $materia): ?>
                                    <option value="<?php echo $materia['id']; ?>" 
                                            <?php echo ($materia_id == $materia['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($materia['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="tipo_evaluacion" class="form-label">Tipo</label>
                                <select class="form-select" id="tipo_evaluacion" name="tipo_evaluacion">
                                    <option value="">Todos los tipos</option>
                                    <?php foreach ($tipos_evaluacion as $tipo): ?>
                                    <option value="<?php echo $tipo; ?>" 
                                            <?php echo ($tipo_evaluacion == $tipo) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($tipo); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (!empty($search) || $estudiante_id > 0 || $materia_id > 0 || !empty($tipo_evaluacion)): ?>
                        <div class="mt-3">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Limpiar filtros
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de calificaciones -->
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>
                        Lista de Calificaciones
                        <?php if ($total_records > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> registros</span>
                        <?php endif; ?>
                        <button class="btn btn-light btn-sm ms-auto" onclick="exportarCalificaciones()">
                            <i class="fas fa-download me-1"></i>Exportar
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($calificaciones)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Estudiante</th>
                                        <th>Materia</th>
                                        <th>Profesor</th>
                                        <th>Tipo</th>
                                        <th>Calificación</th>
                                        <th>Fecha</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($calificaciones as $calificacion): ?>
                                    <tr>
                                        <td><?php echo $calificacion['id']; ?></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars(($calificacion['estudiante_nombre'] ?? '') . ' ' . ($calificacion['estudiante_apellido'] ?? '') . ' ' . ($calificacion['estudiante_apellido_materno'] ?? '')); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($calificacion['materia_nombre'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(($calificacion['profesor_nombre'] ?? '') . ' ' . ($calificacion['profesor_apellido'] ?? '')); ?>
                                        </td>
                                        <td>
                                            <span class="badge tipo-badge">
                                                <?php echo ucfirst($calificacion['tipo_evaluacion']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $nota = $calificacion['calificacion'];
                                            $clase_nota = '';
                                            if ($nota >= 90) $clase_nota = 'nota-excelente';
                                            elseif ($nota >= 80) $clase_nota = 'nota-buena';
                                            elseif ($nota >= 70) $clase_nota = 'nota-regular';
                                            else $clase_nota = 'nota-mala';
                                            ?>
                                            <span class="calificacion-badge <?php echo $clase_nota; ?>">
                                                <?php echo number_format($nota, 2); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($calificacion['fecha_evaluacion'])); ?>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($calificacion['observaciones'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($calificacion['observaciones'] ?? ''); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="ver.php?id=<?php echo $calificacion['id']; ?>" 
                                                   class="btn btn-outline-info btn-sm action-btn" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $calificacion['id']; ?>" 
                                                   class="btn btn-outline-warning btn-sm action-btn" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm action-btn" 
                                                        title="Eliminar"
                                                        onclick="eliminarCalificacion(<?php echo $calificacion['id']; ?>, '<?php echo htmlspecialchars(($calificacion['estudiante_nombre'] ?? '') . ' ' . ($calificacion['estudiante_apellido'] ?? '')); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Paginación de calificaciones">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                        <i class="fas fa-chevron-left"></i> Anterior
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                        Siguiente <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>

                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron calificaciones</h5>
                            <p class="text-muted">
                                <?php if (!empty($search) || $estudiante_id > 0 || $materia_id > 0 || !empty($tipo_evaluacion)): ?>
                                No hay calificaciones que coincidan con los filtros aplicados.
                                <?php else: ?>
                                Aún no hay calificaciones registradas en el sistema.
                                <?php endif; ?>
                            </p>
                            <a href="agregar.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Agregar Primera Calificación
                            </a>
                        </div>
                        <?php endif; ?>
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

        // Función para exportar calificaciones
        function exportarCalificaciones() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', '1');
            window.location.href = 'exportar.php?' + params.toString();
        }

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>

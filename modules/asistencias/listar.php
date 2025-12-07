<?php
/**
 * Módulo de Asistencias - Listado Principal
 * Sistema de Control Escolar
 */

header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn() || (!hasPermission('admin') && !hasPermission('profesor'))) {
    redirect('index.php');
}

$page_title = 'Gestión de Asistencias';

// Obtener parámetros de filtrado y paginación
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$estudiante_id = isset($_GET['estudiante_id']) ? (int)$_GET['estudiante_id'] : 0;
$materia_id = isset($_GET['materia_id']) ? (int)$_GET['materia_id'] : 0;
$fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;

// Obtener datos de la base de datos
try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = [];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(e.nombre LIKE :search OR e.apellido_paterno LIKE :search OR m.nombre LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por estudiante
    if ($estudiante_id > 0) {
        $where_conditions[] = "a.estudiante_id = :estudiante_id";
        $params['estudiante_id'] = $estudiante_id;
    }
    
    // Filtro por materia
    if ($materia_id > 0) {
        $where_conditions[] = "a.materia_id = :materia_id";
        $params['materia_id'] = $materia_id;
    }
    
    // Filtro por fecha
    if (!empty($fecha)) {
        $where_conditions[] = "a.fecha = :fecha";
        $params['fecha'] = $fecha;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "a.estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Contar total de registros
    $count_sql = "
        SELECT COUNT(*) as total
        FROM asistencias a
        LEFT JOIN estudiantes e ON a.estudiante_id = e.id
        LEFT JOIN materias m ON a.materia_id = m.id
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
            a.id,
            a.estudiante_id,
            a.materia_id,
            a.profesor_id,
            a.fecha,
            a.estado,
            a.observaciones,
            a.fecha_creacion,
            e.nombre as estudiante_nombre,
            e.apellido_paterno as estudiante_apellido,
            e.apellido_materno as estudiante_apellido_materno,
            m.nombre as materia_nombre,
            p.nombre as profesor_nombre,
            p.apellido_paterno as profesor_apellido
        FROM asistencias a
        LEFT JOIN estudiantes e ON a.estudiante_id = e.id
        LEFT JOIN materias m ON a.materia_id = m.id
        LEFT JOIN profesores p ON a.profesor_id = p.id
        {$where_clause}
        ORDER BY a.fecha DESC, a.id DESC
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener estudiantes únicos para el filtro
    $estudiantes_sql = "SELECT DISTINCT e.id, CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as nombre_completo 
                        FROM estudiantes e 
                        INNER JOIN asistencias a ON e.id = a.estudiante_id 
                        WHERE e.estado = 'activo' 
                        ORDER BY e.nombre";
    $estudiantes = $pdo->query($estudiantes_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener materias únicas para el filtro
    $materias_sql = "SELECT DISTINCT m.id, m.nombre 
                     FROM materias m 
                     INNER JOIN asistencias a ON m.id = a.materia_id 
                     WHERE m.estado = 'activo' 
                     ORDER BY m.nombre";
    $materias = $pdo->query($materias_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular estadísticas
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as presentes,
            SUM(CASE WHEN estado = 'ausente' THEN 1 ELSE 0 END) as ausentes,
            SUM(CASE WHEN estado = 'tardanza' THEN 1 ELSE 0 END) as tardanzas,
            SUM(CASE WHEN estado = 'justificado' THEN 1 ELSE 0 END) as justificados
        FROM asistencias
        {$where_clause}
    ";
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute($params);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $asistencias = [];
    $total_records = 0;
    $total_pages = 0;
    $estudiantes = [];
    $materias = [];
    $stats = ['total' => 0, 'presentes' => 0, 'ausentes' => 0, 'tardanzas' => 0, 'justificados' => 0];
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
            background: linear-gradient(135deg, var(--coral) 0%, #EF4444 100%);
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
        .estado-presente { background: linear-gradient(135deg, var(--grass-green) 0%, #059669 100%); color: white; }
        .estado-ausente { background: linear-gradient(135deg, var(--coral) 0%, #EF4444 100%); color: white; }
        .estado-tardanza { background: linear-gradient(135deg, var(--orange) 0%, #EA580C 100%); color: white; }
        .estado-justificado { background: linear-gradient(135deg, var(--sky-blue) 0%, #2196F3 100%); color: white; }
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
                                <i class="fas fa-calendar-check me-2"></i>
                                <?php echo $page_title; ?>
                            </h2>
                            <p class="mb-0 mt-2">Gestiona las asistencias de los estudiantes</p>
                        </div>
                        <div>
                            <a href="agregar.php" class="btn btn-school btn-attendance">
                                <i class="fas fa-plus me-2"></i>Registrar Asistencia
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
                        <div class="stat-card attendance">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($stats['total']); ?></h3>
                            <p class="stat-label">Total Registros</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card students">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($stats['presentes']); ?></h3>
                            <p class="stat-label">Presentes</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card teachers">
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($stats['ausentes']); ?></h3>
                            <p class="stat-label">Ausentes</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card subjects">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($stats['tardanzas']); ?></h3>
                            <p class="stat-label">Tardanzas</p>
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
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Estudiante o materia">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="estudiante_id" class="form-label">Estudiante</label>
                                <select class="form-select" id="estudiante_id" name="estudiante_id">
                                    <option value="">Todos</option>
                                    <?php foreach ($estudiantes as $est): ?>
                                    <option value="<?php echo $est['id']; ?>" 
                                            <?php echo ($estudiante_id == $est['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($est['nombre_completo']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="materia_id" class="form-label">Materia</label>
                                <select class="form-select" id="materia_id" name="materia_id">
                                    <option value="">Todas</option>
                                    <?php foreach ($materias as $mat): ?>
                                    <option value="<?php echo $mat['id']; ?>" 
                                            <?php echo ($materia_id == $mat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($mat['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" 
                                       value="<?php echo htmlspecialchars($fecha); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos</option>
                                    <option value="presente" <?php echo ($estado == 'presente') ? 'selected' : ''; ?>>Presente</option>
                                    <option value="ausente" <?php echo ($estado == 'ausente') ? 'selected' : ''; ?>>Ausente</option>
                                    <option value="tardanza" <?php echo ($estado == 'tardanza') ? 'selected' : ''; ?>>Tardanza</option>
                                    <option value="justificado" <?php echo ($estado == 'justificado') ? 'selected' : ''; ?>>Justificado</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-school btn-attendance">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (!empty($search) || $estudiante_id > 0 || $materia_id > 0 || !empty($fecha) || !empty($estado)): ?>
                        <div class="mt-3">
                            <a href="listar.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Limpiar filtros
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de asistencias -->
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>
                        Lista de Asistencias
                        <?php if ($total_records > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> registros</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($asistencias)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Estudiante</th>
                                        <th>Materia</th>
                                        <th>Profesor</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($asistencias as $asistencia): ?>
                                    <tr>
                                        <td><?php echo $asistencia['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars(($asistencia['estudiante_nombre'] ?? '') . ' ' . ($asistencia['estudiante_apellido'] ?? '')); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-module bg-info">
                                                <?php echo htmlspecialchars($asistencia['materia_nombre'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(($asistencia['profesor_nombre'] ?? '') . ' ' . ($asistencia['profesor_apellido'] ?? '')); ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($asistencia['fecha'])); ?></td>
                                        <td>
                                            <span class="badge badge-module estado-<?php echo $asistencia['estado']; ?>">
                                                <?php echo ucfirst($asistencia['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($asistencia['observaciones'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($asistencia['observaciones'] ?? ''); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="ver.php?id=<?php echo $asistencia['id']; ?>" 
                                                   class="btn btn-outline-info btn-sm" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $asistencia['id']; ?>" 
                                                   class="btn btn-outline-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm" 
                                                        title="Eliminar"
                                                        onclick="eliminarAsistencia(<?php echo $asistencia['id']; ?>)">
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
                        <nav aria-label="Paginación">
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
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron asistencias</h5>
                            <p class="text-muted">
                                <?php if (!empty($search) || $estudiante_id > 0 || $materia_id > 0): ?>
                                No hay asistencias que coincidan con los filtros aplicados.
                                <?php else: ?>
                                Aún no hay asistencias registradas en el sistema.
                                <?php endif; ?>
                            </p>
                            <a href="agregar.php" class="btn btn-school btn-attendance">
                                <i class="fas fa-plus me-2"></i>Registrar Primera Asistencia
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function eliminarAsistencia(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este registro de asistencia?\n\nEsta acción no se puede deshacer.')) {
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




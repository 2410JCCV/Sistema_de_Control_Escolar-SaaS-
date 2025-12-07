<?php
/**
 * Módulo de Materias - Listado Principal
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

$page_title = 'Gestión de Materias';

// Obtener parámetros de filtrado y paginación
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$grado_id = isset($_GET['grado_id']) ? (int)$_GET['grado_id'] : 0;
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10; // Materias por página

// Obtener datos de la base de datos
try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = ["m.estado = 'activo'"];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(m.nombre LIKE :search OR m.codigo LIKE :search OR m.descripcion LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por grado
    if ($grado_id > 0) {
        $where_conditions[] = "m.grado_id = :grado_id";
        $params['grado_id'] = $grado_id;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "m.estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Contar total de registros
    $count_sql = "
        SELECT COUNT(*) as total
        FROM materias m
        LEFT JOIN grados g ON m.grado_id = g.id
        WHERE {$where_clause}
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
            m.id,
            m.codigo,
            m.nombre,
            m.descripcion,
            m.creditos,
            m.grado_id,
            m.estado,
            m.fecha_creacion,
            g.nombre as grado_nombre
        FROM materias m
        LEFT JOIN grados g ON m.grado_id = g.id
        WHERE {$where_clause}
        ORDER BY m.id DESC
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener grados únicos para el filtro (únicos por nombre)
    $grados_sql = "SELECT DISTINCT g.nombre, MIN(g.id) as id FROM grados g 
                   INNER JOIN materias m ON g.id = m.grado_id 
                   WHERE g.estado = 'activo' AND m.estado = 'activo' 
                   GROUP BY g.nombre
                   ORDER BY MIN(g.id)";
    $grados = $pdo->query($grados_sql)->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $materias = [];
    $total_records = 0;
    $total_pages = 0;
    $grados = [];
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
            background: linear-gradient(135deg, var(--orange) 0%, #EA580C 100%);
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
                                <i class="fas fa-book me-2"></i>
                                <?php echo $page_title; ?>
                            </h2>
                            <p class="mb-0 mt-2">Gestiona las materias del sistema escolar</p>
                        </div>
                        <div>
                            <a href="agregar.php" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>Agregar Materia
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
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_records); ?></h4>
                                <p class="mb-0">Total Materias</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                <h4><?php echo count($grados); ?></h4>
                                <p class="mb-0">Grados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-star fa-2x mb-2"></i>
                                <h4><?php echo array_sum(array_column($materias, 'creditos')); ?></h4>
                                <p class="mb-0">Total Créditos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-file-alt fa-2x mb-2"></i>
                                <h4><?php echo $total_pages; ?></h4>
                                <p class="mb-0">Páginas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Nombre, código o descripción">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="grado_id" class="form-label">Grado</label>
                                <select class="form-select" id="grado_id" name="grado_id">
                                    <option value="">Todos los grados</option>
                                    <?php foreach ($grados as $grado): ?>
                                    <option value="<?php echo $grado['id']; ?>" 
                                            <?php echo ($grado_id == $grado['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($grado['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="activo" <?php echo ($estado == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                    <option value="inactivo" <?php echo ($estado == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-school btn-subjects">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (!empty($search) || $grado_id > 0 || !empty($estado)): ?>
                        <div class="mt-3">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Limpiar filtros
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de materias -->
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>
                        Lista de Materias
                        <?php if ($total_records > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> registros</span>
                        <?php endif; ?>
                        <button class="btn btn-light btn-sm ms-auto" onclick="exportarMaterias()">
                            <i class="fas fa-download me-1"></i>Exportar
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($materias)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Grado</th>
                                        <th>Créditos</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materias as $materia): ?>
                                    <tr>
                                        <td><?php echo $materia['id']; ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($materia['codigo']); ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($materia['nombre']); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($materia['descripcion']); ?>">
                                                <?php echo htmlspecialchars($materia['descripcion']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge grado-badge">
                                                <?php echo htmlspecialchars($materia['grado_nombre'] ?? 'Sin grado'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge credits-badge">
                                                <?php echo $materia['creditos'] ?? 0; ?> créditos
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $materia['estado'] == 'activo' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($materia['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($materia['fecha_creacion'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="ver.php?id=<?php echo $materia['id']; ?>" 
                                                   class="btn btn-outline-info btn-sm action-btn" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $materia['id']; ?>" 
                                                   class="btn btn-outline-warning btn-sm action-btn" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm action-btn" 
                                                        title="Eliminar"
                                                        onclick="eliminarMateria(<?php echo $materia['id']; ?>, '<?php echo htmlspecialchars($materia['nombre']); ?>')">
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
                        <nav aria-label="Paginación de materias">
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
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron materias</h5>
                            <p class="text-muted">
                                <?php if (!empty($search) || $grado_id > 0 || !empty($estado)): ?>
                                No hay materias que coincidan con los filtros aplicados.
                                <?php else: ?>
                                Aún no hay materias registradas en el sistema.
                                <?php endif; ?>
                            </p>
                            <a href="agregar.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Agregar Primera Materia
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

        // Función para exportar materias
        function exportarMaterias() {
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

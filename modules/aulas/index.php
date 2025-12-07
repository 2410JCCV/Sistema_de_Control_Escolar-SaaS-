<?php
/**
 * Módulo de Aulas - Listado Principal
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

$page_title = 'Gestión de Aulas';

// Obtener parámetros de filtrado y paginación
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10; // Aulas por página

// Obtener datos de la base de datos
try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = [];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(nombre LIKE :search OR ubicacion LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por tipo
    if (!empty($tipo)) {
        $where_conditions[] = "tipo = :tipo";
        $params['tipo'] = $tipo;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Contar total de registros
    $count_sql = "SELECT COUNT(*) as total FROM aulas {$where_clause}";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    
    // Calcular paginación
    $total_pages = ceil($total_records / $limit);
    $offset = ($page - 1) * $limit;
    
    // Consulta principal
    $sql = "
        SELECT 
            id, nombre, ubicacion, capacidad, tipo, estado, fecha_creacion
        FROM aulas
        {$where_clause}
        ORDER BY nombre, id
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tipos disponibles
    $tipos = ['aula', 'laboratorio', 'biblioteca', 'gimnasio'];
    
    // Estados disponibles
    $estados = ['activo', 'inactivo', 'mantenimiento'];
    
} catch (Exception $e) {
    $aulas = [];
    $total_records = 0;
    $total_pages = 0;
    $tipos = [];
    $estados = [];
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
            background: linear-gradient(135deg, var(--lime) 0%, #65A30D 100%);
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
        .tipo-badge {
            background: linear-gradient(45deg, #a8e6cf, #88d8c0);
            color: #2d5a27;
        }
        .estado-activo { color: #28a745; font-weight: bold; }
        .estado-inactivo { color: #dc3545; font-weight: bold; }
        .estado-mantenimiento { color: #ffc107; font-weight: bold; }
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
                                <i class="fas fa-door-open me-2"></i>
                                <?php echo $page_title; ?>
                            </h2>
                            <p class="mb-0 mt-2">Gestiona las aulas del sistema</p>
                        </div>
                        <div>
                            <a href="agregar.php" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>Agregar Aula
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
                                <i class="fas fa-door-open"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($total_records); ?></h3>
                            <p class="stat-label">Total Aulas</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card students">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="stat-number"><?php echo count(array_filter($aulas, function($a) { return $a['estado'] === 'activo'; })); ?></h3>
                            <p class="stat-label">Aulas Activas</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card teachers">
                            <div class="stat-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h3 class="stat-number"><?php echo count(array_filter($aulas, function($a) { return $a['estado'] === 'mantenimiento'; })); ?></h3>
                            <p class="stat-label">En Mantenimiento</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card subjects">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="stat-number"><?php echo array_sum(array_column($aulas, 'capacidad')); ?></h3>
                            <p class="stat-label">Capacidad Total</p>
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
                            <div class="col-md-4">
                                <label for="search" class="form-label">Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Nombre o ubicación">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" name="tipo">
                                    <option value="">Todos los tipos</option>
                                    <?php foreach ($tipos as $t): ?>
                                    <option value="<?php echo $t; ?>" 
                                            <?php echo ($tipo == $t) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($t); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos los estados</option>
                                    <?php foreach ($estados as $e): ?>
                                    <option value="<?php echo $e; ?>" 
                                            <?php echo ($estado == $e) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($e); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-school btn-library">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (!empty($search) || !empty($tipo) || !empty($estado)): ?>
                        <div class="mt-3">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Limpiar filtros
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de aulas -->
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>
                        Lista de Aulas
                        <?php if ($total_records > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> registros</span>
                        <?php endif; ?>
                        <button class="btn btn-light btn-sm ms-auto" onclick="exportarAulas()">
                            <i class="fas fa-download me-1"></i>Exportar
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($aulas)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Ubicación</th>
                                        <th>Tipo</th>
                                        <th>Capacidad</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($aulas as $aula): ?>
                                    <tr>
                                        <td><?php echo $aula['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($aula['nombre']); ?></strong>
                                        </td>
                                        <td>
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($aula['ubicacion'] ?? 'Sin ubicación'); ?>
                                        </td>
                                        <td>
                                            <span class="badge tipo-badge">
                                                <?php echo ucfirst($aula['tipo']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo $aula['capacidad'] ?? 'Sin límite'; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $clase_estado = '';
                                            if ($aula['estado'] === 'activo') $clase_estado = 'estado-activo';
                                            elseif ($aula['estado'] === 'inactivo') $clase_estado = 'estado-inactivo';
                                            else $clase_estado = 'estado-mantenimiento';
                                            ?>
                                            <span class="<?php echo $clase_estado; ?>">
                                                <i class="fas fa-circle me-1"></i>
                                                <?php echo ucfirst($aula['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($aula['fecha_creacion'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="ver.php?id=<?php echo $aula['id']; ?>" 
                                                   class="btn btn-outline-info btn-sm action-btn" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $aula['id']; ?>" 
                                                   class="btn btn-outline-warning btn-sm action-btn" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm action-btn" 
                                                        title="Eliminar"
                                                        onclick="eliminarAula(<?php echo $aula['id']; ?>, '<?php echo htmlspecialchars($aula['nombre']); ?>')">
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
                        <nav aria-label="Paginación de aulas">
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
                            <i class="fas fa-door-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron aulas</h5>
                            <p class="text-muted">
                                <?php if (!empty($search) || !empty($tipo) || !empty($estado)): ?>
                                No hay aulas que coincidan con los filtros aplicados.
                                <?php else: ?>
                                Aún no hay aulas registradas en el sistema.
                                <?php endif; ?>
                            </p>
                            <a href="agregar.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Agregar Primera Aula
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
        // Función para eliminar aula
        function eliminarAula(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas eliminar el aula "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
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

        // Función para exportar aulas
        function exportarAulas() {
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



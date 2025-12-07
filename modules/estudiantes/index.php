<?php
/**
 * Módulo de Estudiantes - Listado Principal
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

$page_title = 'Gestión de Estudiantes';

// Obtener parámetros de filtrado y paginación
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$grado_id = isset($_GET['grado_id']) ? (int)$_GET['grado_id'] : 0;
$grupo_id = isset($_GET['grupo_id']) ? (int)$_GET['grupo_id'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10; // Estudiantes por página

// Obtener datos de la base de datos
try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = ["e.estado = 'activo'"];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(e.nombre LIKE :search OR e.apellido_paterno LIKE :search OR e.apellido_materno LIKE :search OR e.matricula LIKE :search OR e.email LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por grado
    if ($grado_id > 0) {
        $where_conditions[] = "e.grado_id = :grado_id";
        $params['grado_id'] = $grado_id;
    }
    
    // Filtro por grupo
    if ($grupo_id > 0) {
        $where_conditions[] = "e.grupo_id = :grupo_id";
        $params['grupo_id'] = $grupo_id;
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Contar total de registros
    $count_sql = "
        SELECT COUNT(*) as total
        FROM estudiantes e
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
            e.id,
            e.matricula,
            e.nombre,
            e.apellido_paterno,
            e.apellido_materno,
            e.fecha_nacimiento,
            e.email,
            e.telefono,
            e.direccion,
            e.estado,
            e.fecha_creacion,
            g.nombre as grado_nombre,
            gr.nombre as grupo_nombre
        FROM estudiantes e
        LEFT JOIN grados g ON e.grado_id = g.id
        LEFT JOIN grupos gr ON e.grupo_id = gr.id
        WHERE {$where_clause}
        ORDER BY e.id DESC
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener grados únicos para el filtro
    $grados_sql = "SELECT DISTINCT g.id, g.nombre FROM grados g 
                   INNER JOIN estudiantes e ON g.id = e.grado_id 
                   WHERE g.estado = 'activo' AND e.estado = 'activo' 
                   ORDER BY g.id";
    $grados = $pdo->query($grados_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener grupos únicos para el filtro
    $grupos_sql = "SELECT DISTINCT gr.id, gr.nombre FROM grupos gr 
                   INNER JOIN estudiantes e ON gr.id = e.grupo_id 
                   WHERE gr.estado = 'activo' AND e.estado = 'activo' 
                   ORDER BY gr.nombre";
    $grupos = $pdo->query($grupos_sql)->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $estudiantes = [];
    $total_records = 0;
    $total_pages = 0;
    $grados = [];
    $grupos = [];
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
            background: linear-gradient(135deg, var(--sky-blue) 0%, #2196F3 100%);
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
                                <i class="fas fa-user-graduate me-2"></i>
                                <?php echo $page_title; ?>
                            </h2>
                            <p class="mb-0 mt-2">Gestiona la información de los estudiantes</p>
                        </div>
                        <div>
                            <a href="agregar.php" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>Agregar Estudiante
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
                        <div class="stat-card students">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($total_records); ?></h3>
                            <p class="stat-label">Total Estudiantes</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card subjects">
                            <div class="stat-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3 class="stat-number"><?php echo count($grados); ?></h3>
                            <p class="stat-label">Grados</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card groups">
                            <div class="stat-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <h3 class="stat-number"><?php echo count($grupos); ?></h3>
                            <p class="stat-label">Grupos</p>
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
                            <div class="col-md-4">
                                <label for="search" class="form-label">Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Nombre, apellido, matrícula o email">
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
                                <label for="grupo_id" class="form-label">Grupo</label>
                                <select class="form-select" id="grupo_id" name="grupo_id">
                                    <option value="">Todos los grupos</option>
                                    <?php foreach ($grupos as $grupo): ?>
                                    <option value="<?php echo $grupo['id']; ?>" 
                                            <?php echo ($grupo_id == $grupo['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($grupo['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-school btn-students">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (!empty($search) || $grado_id > 0 || $grupo_id > 0): ?>
                        <div class="mt-3">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Limpiar filtros
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de estudiantes -->
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>
                        Lista de Estudiantes
                        <?php if ($total_records > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> registros</span>
                        <?php endif; ?>
                        <button class="btn btn-light btn-sm ms-auto" onclick="exportarEstudiantes()">
                            <i class="fas fa-download me-1"></i>Exportar
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($estudiantes)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Matrícula</th>
                                        <th>Nombre Completo</th>
                                        <th>Grado</th>
                                        <th>Grupo</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estudiantes as $estudiante): ?>
                                    <tr>
                                        <td><?php echo $estudiante['id']; ?></td>
                                        <td>
                                            <span class="badge badge-module bg-info"><?php echo htmlspecialchars($estudiante['matricula']); ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno']); ?></strong>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($estudiante['fecha_creacion'])); ?>
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($estudiante['grado_nombre'] ?? 'Sin grado'); ?></td>
                                        <td><?php echo htmlspecialchars($estudiante['grupo_nombre'] ?? 'Sin grupo'); ?></td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($estudiante['email']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($estudiante['email']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if (!empty($estudiante['telefono'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($estudiante['telefono']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($estudiante['telefono']); ?>
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">Sin teléfono</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-module bg-<?php echo $estudiante['estado'] == 'activo' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($estudiante['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="ver.php?id=<?php echo $estudiante['id']; ?>" 
                                                   class="btn btn-outline-info btn-sm action-btn" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $estudiante['id']; ?>" 
                                                   class="btn btn-outline-warning btn-sm action-btn" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm action-btn" 
                                                        title="Eliminar"
                                                        onclick="eliminarEstudiante(<?php echo $estudiante['id']; ?>, '<?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido_paterno']); ?>')">
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
                        <nav aria-label="Paginación de estudiantes">
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
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron estudiantes</h5>
                            <p class="text-muted">
                                <?php if (!empty($search) || $grado_id > 0 || $grupo_id > 0): ?>
                                No hay estudiantes que coincidan con los filtros aplicados.
                                <?php else: ?>
                                Aún no hay estudiantes registrados en el sistema.
                                <?php endif; ?>
                            </p>
                            <a href="agregar.php" class="btn btn-school btn-students">
                                <i class="fas fa-plus me-2"></i>Agregar Primer Estudiante
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
        // Función para eliminar estudiante
        function eliminarEstudiante(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas eliminar al estudiante "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
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

        // Función para exportar estudiantes
        function exportarEstudiantes() {
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

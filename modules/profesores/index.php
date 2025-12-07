<?php
/**
 * Módulo de Profesores - Listado Principal
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

$page_title = 'Gestión de Profesores';

// Obtener parámetros de filtrado y paginación
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$especialidad = isset($_GET['especialidad']) ? trim($_GET['especialidad']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10; // Profesores por página

// Obtener datos de la base de datos
try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = ["p.estado = 'activo'"];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(p.nombre LIKE :search OR p.apellido_paterno LIKE :search OR p.apellido_materno LIKE :search OR p.codigo LIKE :search OR p.email LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por especialidad
    if (!empty($especialidad)) {
        $where_conditions[] = "p.especialidad = :especialidad";
        $params['especialidad'] = $especialidad;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "p.estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Contar total de registros
    $count_sql = "
        SELECT COUNT(*) as total
        FROM profesores p
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
            p.id,
            p.codigo,
            p.nombre,
            p.apellido_paterno,
            p.apellido_materno,
            p.fecha_nacimiento,
            p.especialidad,
            p.telefono,
            p.email,
            p.direccion,
            p.fecha_ingreso,
            p.salario,
            p.estado,
            p.fecha_creacion
        FROM profesores p
        WHERE {$where_clause}
        ORDER BY p.id DESC
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener especialidades únicas para el filtro
    $especialidades_sql = "SELECT DISTINCT especialidad FROM profesores WHERE estado = 'activo' ORDER BY especialidad";
    $especialidades = $pdo->query($especialidades_sql)->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    $profesores = [];
    $total_records = 0;
    $total_pages = 0;
    $especialidades = [];
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
            background: linear-gradient(135deg, var(--grass-green) 0%, #059669 100%);
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
                                <i class="fas fa-chalkboard-teacher me-2"></i>
                                <?php echo $page_title; ?>
                            </h2>
                            <p class="mb-0 mt-2">Gestiona la información de los profesores</p>
                        </div>
                        <div>
                            <a href="agregar.php" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>Agregar Profesor
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
                        <div class="stat-card teachers">
                            <div class="stat-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($total_records); ?></h3>
                            <p class="stat-label">Total Profesores</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card subjects">
                            <div class="stat-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3 class="stat-number"><?php echo count($especialidades); ?></h3>
                            <p class="stat-label">Especialidades</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card groups">
                            <div class="stat-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <h3 class="stat-number"><?php echo date('Y'); ?></h3>
                            <p class="stat-label">Año Actual</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card students">
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
                                           placeholder="Nombre, apellido, código o email">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="especialidad" class="form-label">Especialidad</label>
                                <select class="form-select" id="especialidad" name="especialidad">
                                    <option value="">Todas las especialidades</option>
                                    <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?php echo htmlspecialchars($esp); ?>" 
                                            <?php echo ($especialidad == $esp) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($esp); ?>
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
                                    <button type="submit" class="btn btn-school btn-teachers">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (!empty($search) || !empty($especialidad) || !empty($estado)): ?>
                        <div class="mt-3">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Limpiar filtros
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de profesores -->
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>
                        Lista de Profesores
                        <?php if ($total_records > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> registros</span>
                        <?php endif; ?>
                        <button class="btn btn-light btn-sm ms-auto" onclick="exportarProfesores()">
                            <i class="fas fa-download me-1"></i>Exportar
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($profesores)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Código</th>
                                        <th>Nombre Completo</th>
                                        <th>Especialidad</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Salario</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($profesores as $profesor): ?>
                                    <tr>
                                        <td><?php echo $profesor['id']; ?></td>
                                        <td>
                                            <span class="badge badge-module bg-info"><?php echo htmlspecialchars($profesor['codigo']); ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido_paterno'] . ' ' . $profesor['apellido_materno']); ?></strong>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($profesor['fecha_creacion'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge specialty-badge">
                                                <?php echo htmlspecialchars($profesor['especialidad']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($profesor['email'])): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($profesor['email']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($profesor['email']); ?>
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">Sin email</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($profesor['telefono'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($profesor['telefono']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($profesor['telefono']); ?>
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">Sin teléfono</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($profesor['fecha_ingreso'])); ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($profesor['salario'])): ?>
                                            $<?php echo number_format($profesor['salario'], 2); ?>
                                            <?php else: ?>
                                            <span class="text-muted">No especificado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-module bg-<?php echo $profesor['estado'] == 'activo' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($profesor['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="ver.php?id=<?php echo $profesor['id']; ?>" 
                                                   class="btn btn-outline-info btn-sm action-btn" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $profesor['id']; ?>" 
                                                   class="btn btn-outline-warning btn-sm action-btn" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm action-btn" 
                                                        title="Eliminar"
                                                        onclick="eliminarProfesor(<?php echo $profesor['id']; ?>, '<?php echo htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido_paterno']); ?>')">
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
                        <nav aria-label="Paginación de profesores">
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
                            <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron profesores</h5>
                            <p class="text-muted">
                                <?php if (!empty($search) || !empty($especialidad) || !empty($estado)): ?>
                                No hay profesores que coincidan con los filtros aplicados.
                                <?php else: ?>
                                Aún no hay profesores registrados en el sistema.
                                <?php endif; ?>
                            </p>
                            <a href="agregar.php" class="btn btn-school btn-teachers">
                                <i class="fas fa-plus me-2"></i>Agregar Primer Profesor
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

        // Función para exportar profesores
        function exportarProfesores() {
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


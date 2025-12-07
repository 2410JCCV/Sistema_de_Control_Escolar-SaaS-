<?php
/**
 * Módulo de Usuarios - Listado Principal
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

$page_title = 'Gestión de Usuarios';

// Obtener parámetros de filtrado y paginación
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$rol = isset($_GET['rol']) ? trim($_GET['rol']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10; // Usuarios por página

// Obtener datos de la base de datos
try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = [];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(username LIKE :search OR email LIKE :search OR nombre LIKE :search OR apellido LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por rol
    if (!empty($rol)) {
        $where_conditions[] = "rol = :rol";
        $params['rol'] = $rol;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Contar total de registros
    $count_sql = "SELECT COUNT(*) as total FROM usuarios {$where_clause}";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    
    // Calcular paginación
    $total_pages = ceil($total_records / $limit);
    $offset = ($page - 1) * $limit;
    
    // Consulta principal
    $sql = "
        SELECT 
            id, username, email, nombre, apellido, rol, estado, 
            fecha_creacion, fecha_actualizacion
        FROM usuarios
        {$where_clause}
        ORDER BY fecha_creacion DESC, id DESC
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Roles disponibles
    $roles = ['admin', 'profesor', 'estudiante'];
    
    // Estados disponibles
    $estados = ['activo', 'inactivo'];
    
} catch (Exception $e) {
    $usuarios = [];
    $total_records = 0;
    $total_pages = 0;
    $roles = [];
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
            background: linear-gradient(135deg, var(--sky-blue) 0%, var(--purple) 100%);
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
        .rol-badge {
            background: linear-gradient(45deg, #a8e6cf, #88d8c0);
            color: #2d5a27;
        }
        .estado-activo { color: #28a745; font-weight: bold; }
        .estado-inactivo { color: #dc3545; font-weight: bold; }
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
                                <i class="fas fa-users me-2"></i>
                                <?php echo $page_title; ?>
                            </h2>
                            <p class="mb-0 mt-2">Gestiona los usuarios del sistema</p>
                        </div>
                        <div>
                            <a href="agregar.php" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>Agregar Usuario
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
                            <p class="stat-label">Total Usuarios</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card teachers">
                            <div class="stat-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h3 class="stat-number"><?php echo count(array_filter($usuarios, function($u) { return $u['rol'] === 'admin'; })); ?></h3>
                            <p class="stat-label">Administradores</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card groups">
                            <div class="stat-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h3 class="stat-number"><?php echo count(array_filter($usuarios, function($u) { return $u['rol'] === 'profesor'; })); ?></h3>
                            <p class="stat-label">Profesores</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card subjects">
                            <div class="stat-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h3 class="stat-number"><?php echo count(array_filter($usuarios, function($u) { return $u['rol'] === 'estudiante'; })); ?></h3>
                            <p class="stat-label">Estudiantes</p>
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
                                           placeholder="Usuario, email, nombre o apellido">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-select" id="rol" name="rol">
                                    <option value="">Todos los roles</option>
                                    <?php foreach ($roles as $r): ?>
                                    <option value="<?php echo $r; ?>" 
                                            <?php echo ($rol == $r) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($r); ?>
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
                                    <button type="submit" class="btn btn-school btn-groups">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (!empty($search) || !empty($rol) || !empty($estado)): ?>
                        <div class="mt-3">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Limpiar filtros
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de usuarios -->
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>
                        Lista de Usuarios
                        <?php if ($total_records > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> registros</span>
                        <?php endif; ?>
                        <button class="btn btn-light btn-sm ms-auto" onclick="exportarUsuarios()">
                            <i class="fas fa-download me-1"></i>Exportar
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($usuarios)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Nombre Completo</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($usuario['username']); ?></strong>
                                        </td>
                                        <td>
                                            <i class="fas fa-envelope me-1"></i>
                                            <?php echo htmlspecialchars($usuario['email']); ?>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge rol-badge">
                                                <?php echo ucfirst($usuario['rol']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $clase_estado = $usuario['estado'] === 'activo' ? 'estado-activo' : 'estado-inactivo';
                                            ?>
                                            <span class="<?php echo $clase_estado; ?>">
                                                <i class="fas fa-circle me-1"></i>
                                                <?php echo ucfirst($usuario['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="ver.php?id=<?php echo $usuario['id']; ?>" 
                                                   class="btn btn-outline-info btn-sm action-btn" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $usuario['id']; ?>" 
                                                   class="btn btn-outline-warning btn-sm action-btn" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm action-btn" 
                                                        title="Eliminar"
                                                        onclick="eliminarUsuario(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['username']); ?>')">
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
                        <nav aria-label="Paginación de usuarios">
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
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron usuarios</h5>
                            <p class="text-muted">
                                <?php if (!empty($search) || !empty($rol) || !empty($estado)): ?>
                                No hay usuarios que coincidan con los filtros aplicados.
                                <?php else: ?>
                                Aún no hay usuarios registrados en el sistema.
                                <?php endif; ?>
                            </p>
                            <a href="agregar.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Agregar Primer Usuario
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
        // Función para eliminar usuario
        function eliminarUsuario(id, username) {
            if (confirm(`¿Estás seguro de que deseas eliminar el usuario "${username}"?\n\nEsta acción no se puede deshacer.`)) {
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

        // Función para exportar usuarios
        function exportarUsuarios() {
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



<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Gestión de Inventario';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;

try {
    $pdo = conectarDB();
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(nombre LIKE :search OR codigo LIKE :search OR descripcion LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    if (!empty($categoria)) {
        $where_conditions[] = "categoria = :categoria";
        $params['categoria'] = $categoria;
    }
    
    if (!empty($estado)) {
        $where_conditions[] = "estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    $count_sql = "SELECT COUNT(*) as total FROM inventario {$where_clause}";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    
    $total_pages = ceil($total_records / $limit);
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT * FROM inventario {$where_clause} ORDER BY fecha_creacion DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge(array_values($params), [$limit, $offset]));
    $recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas
    $stats_sql = "SELECT 
        COUNT(*) as total,
        SUM(cantidad_disponible) as disponibles,
        SUM(precio_unitario * cantidad_disponible) as valor_total
        FROM inventario {$where_clause}";
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute($params);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    $categorias = $pdo->query("SELECT DISTINCT categoria FROM inventario WHERE categoria IS NOT NULL ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    $recursos = [];
    $total_records = 0;
    $total_pages = 0;
    $stats = ['total' => 0, 'disponibles' => 0, 'valor_total' => 0];
    $categorias = [];
}

$success_message = isset($_GET['success']) ? urldecode($_GET['success']) : '';

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
            background: linear-gradient(135deg, var(--coral) 0%, #DC2626 100%);
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            color: white;
        }
    </style>
</head>
<body class="dashboard-style">
    <div class="main-container">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><i class="fas fa-boxes me-2"></i><?php echo $page_title; ?></h2>
                            <p class="mb-0 mt-2">Gestiona el inventario de recursos escolares</p>
                        </div>
                        <div>
                            <a href="agregar.php" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>Agregar Recurso
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-module alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card inventory">
                            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                            <h3 class="stat-number"><?php echo number_format($stats['total']); ?></h3>
                            <p class="stat-label">Total Recursos</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card students">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <h3 class="stat-number"><?php echo number_format($stats['disponibles']); ?></h3>
                            <p class="stat-label">Disponibles</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card groups">
                            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                            <h3 class="stat-number">$<?php echo number_format($stats['valor_total'], 2); ?></h3>
                            <p class="stat-label">Valor Total</p>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-filter"></i>Filtros
                    </div>
                    <div class="card-body p-4">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Nombre, código o descripción">
                            </div>
                            <div class="col-md-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" name="categoria">
                                    <option value="">Todas</option>
                                    <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoria == $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos</option>
                                    <option value="disponible" <?php echo $estado == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                                    <option value="en_uso" <?php echo $estado == 'en_uso' ? 'selected' : ''; ?>>En Uso</option>
                                    <option value="mantenimiento" <?php echo $estado == 'mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                                    <option value="agotado" <?php echo $estado == 'agotado' ? 'selected' : ''; ?>>Agotado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-school btn-inventory">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>Lista de Recursos
                        <?php if ($total_records > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> registros</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($recursos)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Ubicación</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recursos as $recurso): ?>
                                    <tr>
                                        <td><?php echo $recurso['id']; ?></td>
                                        <td><span class="badge badge-module bg-info"><?php echo htmlspecialchars($recurso['codigo']); ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($recurso['nombre']); ?></strong></td>
                                        <td><span class="badge badge-module bg-secondary"><?php echo htmlspecialchars($recurso['categoria'] ?? 'N/A'); ?></span></td>
                                        <td>
                                            <span class="badge badge-module bg-success"><?php echo $recurso['cantidad_disponible']; ?></span> / 
                                            <?php echo $recurso['cantidad_total']; ?>
                                        </td>
                                        <td>$<?php echo number_format($recurso['precio_unitario'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($recurso['ubicacion'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge badge-module bg-<?php 
                                                echo $recurso['estado'] == 'disponible' ? 'success' : 
                                                    ($recurso['estado'] == 'en_uso' ? 'warning' : 
                                                    ($recurso['estado'] == 'mantenimiento' ? 'info' : 'danger')); 
                                            ?>">
                                                <?php echo ucfirst($recurso['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="ver.php?id=<?php echo $recurso['id']; ?>" class="btn btn-outline-info btn-sm" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $recurso['id']; ?>" class="btn btn-outline-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm" title="Eliminar"
                                                        onclick="eliminarRecurso(<?php echo $recurso['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

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
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
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
                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron recursos</h5>
                            <a href="agregar.php" class="btn btn-school btn-inventory">
                                <i class="fas fa-plus me-2"></i>Agregar Primer Recurso
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
        function eliminarRecurso(id) {
            if (confirm('¿Eliminar este recurso?')) {
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




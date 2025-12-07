<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Gestión de Pagos';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$estudiante_id = isset($_GET['estudiante_id']) ? (int)$_GET['estudiante_id'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;

try {
    $pdo = conectarDB();
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(p.numero_referencia LIKE :search OR CONCAT(e.nombre, ' ', e.apellido_paterno) LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    if (!empty($tipo)) {
        $where_conditions[] = "p.tipo = :tipo";
        $params['tipo'] = $tipo;
    }
    
    if (!empty($estado)) {
        $where_conditions[] = "p.estado = :estado";
        $params['estado'] = $estado;
    }
    
    if ($estudiante_id > 0) {
        $where_conditions[] = "p.estudiante_id = :estudiante_id";
        $params['estudiante_id'] = $estudiante_id;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    $count_sql = "SELECT COUNT(*) as total 
                  FROM pagos p
                  LEFT JOIN estudiantes e ON p.estudiante_id = e.id
                  {$where_clause}";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    
    $total_pages = ceil($total_records / $limit);
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT p.*, 
            CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as estudiante_nombre,
            e.codigo as estudiante_codigo
            FROM pagos p
            LEFT JOIN estudiantes e ON p.estudiante_id = e.id
            {$where_clause}
            ORDER BY p.fecha_pago DESC, p.fecha_creacion DESC
            LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge(array_values($params), [$limit, $offset]));
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas
    $stats_sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado = 'completado' THEN monto ELSE 0 END) as total_pagado,
        SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END) as total_pendiente,
        SUM(CASE WHEN estado = 'vencido' THEN monto ELSE 0 END) as total_vencido
        FROM pagos {$where_clause}";
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute($params);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    $estudiantes = $pdo->query("SELECT id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo, codigo FROM estudiantes WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $pagos = [];
    $total_records = 0;
    $total_pages = 0;
    $stats = ['total' => 0, 'total_pagado' => 0, 'total_pendiente' => 0, 'total_vencido' => 0];
    $estudiantes = [];
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
            background: linear-gradient(135deg, var(--sunny-yellow) 0%, #EAB308 100%);
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
                            <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i><?php echo $page_title; ?></h2>
                            <p class="mb-0 mt-2">Gestiona los pagos y finanzas escolares</p>
                        </div>
                        <div>
                            <a href="agregar.php" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>Agregar Pago
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
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card payments">
                            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                            <h3 class="stat-number"><?php echo number_format($stats['total']); ?></h3>
                            <p class="stat-label">Total Pagos</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card students">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <h3 class="stat-number">$<?php echo number_format($stats['total_pagado'], 2); ?></h3>
                            <p class="stat-label">Total Pagado</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card attendance">
                            <div class="stat-icon"><i class="fas fa-clock"></i></div>
                            <h3 class="stat-number">$<?php echo number_format($stats['total_pendiente'], 2); ?></h3>
                            <p class="stat-label">Pendiente</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card teachers">
                            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <h3 class="stat-number">$<?php echo number_format($stats['total_vencido'], 2); ?></h3>
                            <p class="stat-label">Vencido</p>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-filter"></i>Filtros
                    </div>
                    <div class="card-body p-4">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Referencia o estudiante">
                            </div>
                            <div class="col-md-3">
                                <label for="estudiante_id" class="form-label">Estudiante</label>
                                <select class="form-select" id="estudiante_id" name="estudiante_id">
                                    <option value="">Todos</option>
                                    <?php foreach ($estudiantes as $est): ?>
                                    <option value="<?php echo $est['id']; ?>" <?php echo $estudiante_id == $est['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($est['codigo'] . ' - ' . $est['nombre_completo']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" name="tipo">
                                    <option value="">Todos</option>
                                    <option value="matricula" <?php echo $tipo == 'matricula' ? 'selected' : ''; ?>>Matrícula</option>
                                    <option value="mensualidad" <?php echo $tipo == 'mensualidad' ? 'selected' : ''; ?>>Mensualidad</option>
                                    <option value="material" <?php echo $tipo == 'material' ? 'selected' : ''; ?>>Material</option>
                                    <option value="evento" <?php echo $tipo == 'evento' ? 'selected' : ''; ?>>Evento</option>
                                    <option value="otro" <?php echo $tipo == 'otro' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos</option>
                                    <option value="completado" <?php echo $estado == 'completado' ? 'selected' : ''; ?>>Completado</option>
                                    <option value="pendiente" <?php echo $estado == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="vencido" <?php echo $estado == 'vencido' ? 'selected' : ''; ?>>Vencido</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-school btn-payments">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>Lista de Pagos
                        <?php if ($total_records > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> registros</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($pagos)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Número Referencia</th>
                                        <th>Estudiante</th>
                                        <th>Tipo</th>
                                        <th>Monto</th>
                                        <th>Fecha Pago</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Método</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagos as $pago): ?>
                                    <tr>
                                        <td><?php echo $pago['id']; ?></td>
                                        <td><span class="badge badge-module bg-info"><?php echo htmlspecialchars($pago['numero_referencia']); ?></span></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($pago['estudiante_nombre'] ?? 'N/A'); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($pago['estudiante_codigo'] ?? ''); ?></small>
                                        </td>
                                        <td><span class="badge badge-module bg-secondary"><?php echo ucfirst($pago['tipo']); ?></span></td>
                                        <td><strong>$<?php echo number_format($pago['monto'], 2); ?></strong></td>
                                        <td><?php echo $pago['fecha_pago'] ? date('d/m/Y', strtotime($pago['fecha_pago'])) : 'N/A'; ?></td>
                                        <td><?php echo $pago['fecha_vencimiento'] ? date('d/m/Y', strtotime($pago['fecha_vencimiento'])) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($pago['metodo_pago'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge badge-module bg-<?php 
                                                echo $pago['estado'] == 'completado' ? 'success' : 
                                                    ($pago['estado'] == 'pendiente' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($pago['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="ver.php?id=<?php echo $pago['id']; ?>" class="btn btn-outline-info btn-sm" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($pago['estado'] != 'completado'): ?>
                                                <a href="editar.php?id=<?php echo $pago['id']; ?>" class="btn btn-outline-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm" title="Eliminar"
                                                        onclick="eliminarPago(<?php echo $pago['id']; ?>)">
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
                            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron pagos</h5>
                            <a href="agregar.php" class="btn btn-school btn-payments">
                                <i class="fas fa-plus me-2"></i>Agregar Primer Pago
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
        function eliminarPago(id) {
            if (confirm('¿Eliminar este pago?')) {
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




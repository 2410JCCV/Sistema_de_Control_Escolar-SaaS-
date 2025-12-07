<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Gestión de Eventos';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;

try {
    $pdo = conectarDB();
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(titulo LIKE :search OR descripcion LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    if (!empty($tipo)) {
        $where_conditions[] = "tipo = :tipo";
        $params['tipo'] = $tipo;
    }
    
    if (!empty($estado)) {
        $where_conditions[] = "estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    $count_sql = "SELECT COUNT(*) as total FROM eventos {$where_clause}";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    
    $total_pages = ceil($total_records / $limit);
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT e.*, 
            CONCAT(p.nombre, ' ', p.apellido_paterno) as organizador_nombre,
            g.nombre as grupo_nombre,
            gr.nombre as grado_nombre
            FROM eventos e
            LEFT JOIN profesores p ON e.organizador_id = p.id
            LEFT JOIN grupos g ON e.grupo_id = g.id
            LEFT JOIN grados gr ON e.grado_id = gr.id
            {$where_clause}
            ORDER BY e.fecha_inicio DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge(array_values($params), [$limit, $offset]));
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas
    $stats_sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado = 'programado' THEN 1 ELSE 0 END) as programados,
        SUM(CASE WHEN estado = 'en_curso' THEN 1 ELSE 0 END) as en_curso,
        SUM(CASE WHEN estado = 'finalizado' THEN 1 ELSE 0 END) as finalizados
        FROM eventos {$where_clause}";
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute($params);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $eventos = [];
    $total_records = 0;
    $total_pages = 0;
    $stats = ['total' => 0, 'programados' => 0, 'en_curso' => 0, 'finalizados' => 0];
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
            background: linear-gradient(135deg, var(--pink) 0%, #EC4899 100%);
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
                            <h2 class="mb-0"><i class="fas fa-calendar-alt me-2"></i><?php echo $page_title; ?></h2>
                            <p class="mb-0 mt-2">Gestiona los eventos y actividades escolares</p>
                        </div>
                        <div>
                            <a href="agregar.php" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>Agregar Evento
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
                        <div class="stat-card events">
                            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                            <h3 class="stat-number"><?php echo number_format($stats['total']); ?></h3>
                            <p class="stat-label">Total Eventos</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card students">
                            <div class="stat-icon"><i class="fas fa-clock"></i></div>
                            <h3 class="stat-number"><?php echo number_format($stats['programados']); ?></h3>
                            <p class="stat-label">Programados</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card teachers">
                            <div class="stat-icon"><i class="fas fa-play-circle"></i></div>
                            <h3 class="stat-number"><?php echo number_format($stats['en_curso']); ?></h3>
                            <p class="stat-label">En Curso</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card groups">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <h3 class="stat-number"><?php echo number_format($stats['finalizados']); ?></h3>
                            <p class="stat-label">Finalizados</p>
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
                                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Título o descripción">
                            </div>
                            <div class="col-md-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" name="tipo">
                                    <option value="">Todos</option>
                                    <option value="academico" <?php echo $tipo == 'academico' ? 'selected' : ''; ?>>Académico</option>
                                    <option value="deportivo" <?php echo $tipo == 'deportivo' ? 'selected' : ''; ?>>Deportivo</option>
                                    <option value="cultural" <?php echo $tipo == 'cultural' ? 'selected' : ''; ?>>Cultural</option>
                                    <option value="social" <?php echo $tipo == 'social' ? 'selected' : ''; ?>>Social</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos</option>
                                    <option value="programado" <?php echo $estado == 'programado' ? 'selected' : ''; ?>>Programado</option>
                                    <option value="en_curso" <?php echo $estado == 'en_curso' ? 'selected' : ''; ?>>En Curso</option>
                                    <option value="finalizado" <?php echo $estado == 'finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-school btn-events">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>Lista de Eventos
                        <?php if ($total_records > 0): ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> registros</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($eventos)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Tipo</th>
                                        <th>Fecha Inicio</th>
                                        <th>Ubicación</th>
                                        <th>Organizador</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($eventos as $evento): ?>
                                    <tr>
                                        <td><?php echo $evento['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($evento['titulo']); ?></strong></td>
                                        <td><span class="badge badge-module bg-info"><?php echo ucfirst($evento['tipo']); ?></span></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($evento['fecha_inicio'])); ?></td>
                                        <td><?php echo htmlspecialchars($evento['ubicacion'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($evento['organizador_nombre'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge badge-module bg-<?php 
                                                echo $evento['estado'] == 'programado' ? 'primary' : 
                                                    ($evento['estado'] == 'en_curso' ? 'warning' : 'success'); 
                                            ?>">
                                                <?php echo ucfirst($evento['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="ver.php?id=<?php echo $evento['id']; ?>" class="btn btn-outline-info btn-sm" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $evento['id']; ?>" class="btn btn-outline-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm" title="Eliminar"
                                                        onclick="eliminarEvento(<?php echo $evento['id']; ?>)">
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
                            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron eventos</h5>
                            <a href="agregar.php" class="btn btn-school btn-events">
                                <i class="fas fa-plus me-2"></i>Agregar Primer Evento
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
        function eliminarEvento(id) {
            if (confirm('¿Eliminar este evento?')) {
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




<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$page_title = 'Notificaciones';
$user_id = $_SESSION['user_id'];

try {
    $pdo = conectarDB();
    
    // Obtener notificaciones del usuario
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $count_sql = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ?";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute([$user_id]);
    $total_records = $stmt->fetch()['total'];
    $total_pages = ceil($total_records / $limit);
    
    $sql = "SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha_creacion DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $limit, $offset]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar no leídas
    $no_leidas_sql = "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leida = 0";
    $no_leidas = $pdo->prepare($no_leidas_sql)->execute([$user_id]);
    $no_leidas = $pdo->prepare($no_leidas_sql);
    $no_leidas->execute([$user_id]);
    $total_no_leidas = $no_leidas->fetchColumn();
    
} catch (Exception $e) {
    $notificaciones = [];
    $total_records = 0;
    $total_pages = 0;
    $total_no_leidas = 0;
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
        .notificacion-no-leida {
            background: linear-gradient(135deg, rgba(167, 139, 250, 0.1) 0%, rgba(244, 114, 182, 0.1) 100%);
            border-left: 5px solid var(--purple);
        }
        .notificacion-leida {
            background: #F9FAFB;
            opacity: 0.7;
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
                            <h2 class="mb-0">
                                <i class="fas fa-bell me-2"></i>
                                <?php echo $page_title; ?>
                            </h2>
                            <p class="mb-0 mt-2">Gestiona tus notificaciones</p>
                        </div>
                        <div>
                            <?php if ($total_no_leidas > 0): ?>
                            <span class="badge bg-light text-dark me-2"><?php echo $total_no_leidas; ?> no leídas</span>
                            <?php endif; ?>
                            <button class="btn btn-light btn-lg" onclick="marcarTodasLeidas()">
                                <i class="fas fa-check-double me-2"></i>Marcar todas como leídas
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card groups">
                            <div class="stat-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($total_records); ?></h3>
                            <p class="stat-label">Total Notificaciones</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card students">
                            <div class="stat-icon">
                                <i class="fas fa-envelope-open"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($total_records - $total_no_leidas); ?></h3>
                            <p class="stat-label">Leídas</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card attendance">
                            <div class="stat-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3 class="stat-number"><?php echo number_format($total_no_leidas); ?></h3>
                            <p class="stat-label">No Leídas</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card subjects">
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="stat-number"><?php echo $total_pages; ?></h3>
                            <p class="stat-label">Páginas</p>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>
                        Lista de Notificaciones
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($notificaciones)): ?>
                        <?php foreach ($notificaciones as $notif): ?>
                        <div class="content-card mb-3 <?php echo $notif['leida'] ? 'notificacion-leida' : 'notificacion-no-leida'; ?>">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-<?php 
                                                echo $notif['tipo'] == 'success' ? 'check-circle text-success' : 
                                                    ($notif['tipo'] == 'danger' ? 'exclamation-circle text-danger' :
                                                    ($notif['tipo'] == 'warning' ? 'exclamation-triangle text-warning' : 'info-circle text-info')); 
                                            ?> me-2"></i>
                                            <h5 class="mb-0"><?php echo htmlspecialchars($notif['titulo']); ?></h5>
                                            <?php if (!$notif['leida']): ?>
                                            <span class="badge badge-module bg-danger ms-2">Nuevo</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mb-2"><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($notif['fecha_creacion'])); ?>
                                        </small>
                                    </div>
                                    <div class="ms-3">
                                        <?php if (!$notif['leida']): ?>
                                        <button class="btn btn-sm btn-school btn-students" onclick="marcarLeida(<?php echo $notif['id']; ?>)">
                                            <i class="fas fa-check me-1"></i>Marcar leída
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarNotificacion(<?php echo $notif['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Paginación">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Anterior
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        Siguiente <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No tienes notificaciones</h5>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function marcarLeida(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'marcar_leida.php';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = id;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
        
        function marcarTodasLeidas() {
            if (confirm('¿Marcar todas las notificaciones como leídas?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'marcar_todas_leidas.php';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function eliminarNotificacion(id) {
            if (confirm('¿Eliminar esta notificación?')) {
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




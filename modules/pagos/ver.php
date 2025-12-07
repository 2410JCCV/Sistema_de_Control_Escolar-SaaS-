<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$page_title = 'Ver Pago';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) redirect('listar.php');

try {
    $pdo = conectarDB();
    $sql = "SELECT p.*, 
            CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as estudiante_nombre,
            e.codigo as estudiante_codigo
            FROM pagos p
            LEFT JOIN estudiantes e ON p.estudiante_id = e.id
            WHERE p.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $pago = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pago) redirect('listar.php');
} catch (Exception $e) {
    redirect('listar.php');
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
    </style>
</head>
<body class="dashboard-style">
    <div class="main-container">
        <div class="row">
            <div class="col-12">
                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-eye"></i>Detalles del Pago
                        <a href="listar.php" class="btn btn-light btn-sm ms-auto">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Número de Referencia:</strong><br>
                                <span class="badge badge-module bg-info"><?php echo htmlspecialchars($pago['numero_referencia']); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Estudiante:</strong><br>
                                <strong><?php echo htmlspecialchars($pago['estudiante_nombre'] ?? 'N/A'); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($pago['estudiante_codigo'] ?? ''); ?></small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Tipo:</strong><br>
                                <span class="badge badge-module bg-secondary"><?php echo ucfirst($pago['tipo']); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Monto:</strong><br>
                                <h4 class="text-success">$<?php echo number_format($pago['monto'], 2); ?></h4>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Fecha Pago:</strong><br>
                                <?php echo $pago['fecha_pago'] ? date('d/m/Y', strtotime($pago['fecha_pago'])) : 'N/A'; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Fecha Vencimiento:</strong><br>
                                <?php echo $pago['fecha_vencimiento'] ? date('d/m/Y', strtotime($pago['fecha_vencimiento'])) : 'N/A'; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Método de Pago:</strong><br>
                                <?php echo htmlspecialchars($pago['metodo_pago'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-module bg-<?php 
                                    echo $pago['estado'] == 'completado' ? 'success' : 
                                        ($pago['estado'] == 'pendiente' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo ucfirst($pago['estado']); ?>
                                </span>
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Descripción:</strong><br>
                                <?php echo nl2br(htmlspecialchars($pago['descripcion'] ?? 'Sin descripción')); ?>
                            </div>
                        </div>
                        <?php if (hasPermission('admin') && $pago['estado'] != 'completado'): ?>
                        <div class="mt-4">
                            <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-school btn-payments">
                                <i class="fas fa-edit me-2"></i>Editar
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$page_title = 'Ver Recurso';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) redirect('listar.php');

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT * FROM inventario WHERE id = ?");
    $stmt->execute([$id]);
    $recurso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recurso) redirect('listar.php');
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
                        <i class="fas fa-eye"></i>Detalles del Recurso
                        <a href="listar.php" class="btn btn-light btn-sm ms-auto">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Código:</strong><br>
                                <span class="badge badge-module bg-info"><?php echo htmlspecialchars($recurso['codigo']); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Nombre:</strong><br>
                                <?php echo htmlspecialchars($recurso['nombre']); ?>
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Descripción:</strong><br>
                                <?php echo nl2br(htmlspecialchars($recurso['descripcion'] ?? 'Sin descripción')); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Categoría:</strong><br>
                                <span class="badge badge-module bg-secondary"><?php echo htmlspecialchars($recurso['categoria'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-module bg-<?php 
                                    echo $recurso['estado'] == 'disponible' ? 'success' : 
                                        ($recurso['estado'] == 'en_uso' ? 'warning' : 
                                        ($recurso['estado'] == 'mantenimiento' ? 'info' : 'danger')); 
                                ?>">
                                    <?php echo ucfirst($recurso['estado']); ?>
                                </span>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Cantidad Total:</strong><br>
                                <?php echo $recurso['cantidad_total']; ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Cantidad Disponible:</strong><br>
                                <span class="badge badge-module bg-success"><?php echo $recurso['cantidad_disponible']; ?></span>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Precio Unitario:</strong><br>
                                $<?php echo number_format($recurso['precio_unitario'], 2); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Ubicación:</strong><br>
                                <?php echo htmlspecialchars($recurso['ubicacion'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Proveedor:</strong><br>
                                <?php echo htmlspecialchars($recurso['proveedor'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Fecha Adquisición:</strong><br>
                                <?php echo $recurso['fecha_adquisicion'] ? date('d/m/Y', strtotime($recurso['fecha_adquisicion'])) : 'N/A'; ?>
                            </div>
                        </div>
                        <?php if (hasPermission('admin')): ?>
                        <div class="mt-4">
                            <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-school btn-inventory">
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




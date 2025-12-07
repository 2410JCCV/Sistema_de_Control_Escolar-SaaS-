<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$page_title = 'Ver Libro';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) redirect('listar.php');

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT * FROM libros WHERE id = ?");
    $stmt->execute([$id]);
    $libro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$libro) redirect('listar.php');
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
                        <i class="fas fa-eye"></i>Detalles del Libro
                        <a href="listar.php" class="btn btn-light btn-sm ms-auto">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Código:</strong><br>
                                <span class="badge badge-module bg-info"><?php echo htmlspecialchars($libro['codigo']); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Título:</strong><br>
                                <?php echo htmlspecialchars($libro['titulo']); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Autor:</strong><br>
                                <?php echo htmlspecialchars($libro['autor']); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Editorial:</strong><br>
                                <?php echo htmlspecialchars($libro['editorial'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>ISBN:</strong><br>
                                <?php echo htmlspecialchars($libro['isbn'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Categoría:</strong><br>
                                <span class="badge badge-module bg-secondary"><?php echo htmlspecialchars($libro['categoria'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Cantidad Total:</strong><br>
                                <?php echo $libro['cantidad_total']; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Cantidad Disponible:</strong><br>
                                <span class="badge badge-module bg-success"><?php echo $libro['cantidad_disponible']; ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-module bg-<?php 
                                    echo $libro['estado'] == 'disponible' ? 'success' : 
                                        ($libro['estado'] == 'prestado' ? 'warning' : 
                                        ($libro['estado'] == 'reservado' ? 'info' : 'secondary')); 
                                ?>">
                                    <?php echo ucfirst($libro['estado']); ?>
                                </span>
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Descripción:</strong><br>
                                <?php echo nl2br(htmlspecialchars($libro['descripcion'] ?? 'Sin descripción')); ?>
                            </div>
                        </div>
                        <?php if (hasPermission('admin')): ?>
                        <div class="mt-4">
                            <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-school btn-library">
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




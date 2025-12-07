<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$page_title = 'Ver Evento';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) redirect('listar.php');

try {
    $pdo = conectarDB();
    $sql = "SELECT e.*, 
            CONCAT(p.nombre, ' ', p.apellido_paterno) as organizador_nombre,
            g.nombre as grupo_nombre,
            gr.nombre as grado_nombre
            FROM eventos e
            LEFT JOIN profesores p ON e.organizador_id = p.id
            LEFT JOIN grupos g ON e.grupo_id = g.id
            LEFT JOIN grados gr ON e.grado_id = gr.id
            WHERE e.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) redirect('listar.php');
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
                        <i class="fas fa-eye"></i>Detalles del Evento
                        <a href="listar.php" class="btn btn-light btn-sm ms-auto">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Título:</strong><br>
                                <?php echo htmlspecialchars($evento['titulo']); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Tipo:</strong><br>
                                <span class="badge badge-module bg-info"><?php echo ucfirst($evento['tipo']); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Fecha Inicio:</strong><br>
                                <?php echo date('d/m/Y H:i', strtotime($evento['fecha_inicio'])); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Fecha Fin:</strong><br>
                                <?php echo $evento['fecha_fin'] ? date('d/m/Y H:i', strtotime($evento['fecha_fin'])) : 'N/A'; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Ubicación:</strong><br>
                                <?php echo htmlspecialchars($evento['ubicacion'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Organizador:</strong><br>
                                <?php echo htmlspecialchars($evento['organizador_nombre'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-module bg-<?php 
                                    echo $evento['estado'] == 'programado' ? 'primary' : 
                                        ($evento['estado'] == 'en_curso' ? 'warning' : 'success'); 
                                ?>">
                                    <?php echo ucfirst($evento['estado']); ?>
                                </span>
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Descripción:</strong><br>
                                <?php echo nl2br(htmlspecialchars($evento['descripcion'] ?? 'Sin descripción')); ?>
                            </div>
                        </div>
                        <?php if (hasPermission('admin')): ?>
                        <div class="mt-4">
                            <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-school btn-events">
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




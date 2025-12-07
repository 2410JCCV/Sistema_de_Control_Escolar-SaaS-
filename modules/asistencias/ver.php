<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$page_title = 'Ver Asistencia';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('listar.php');
}

try {
    $pdo = conectarDB();
    $sql = "SELECT a.*, 
            CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as estudiante_nombre,
            m.nombre as materia_nombre,
            CONCAT(p.nombre, ' ', p.apellido_paterno) as profesor_nombre
            FROM asistencias a
            LEFT JOIN estudiantes e ON a.estudiante_id = e.id
            LEFT JOIN materias m ON a.materia_id = m.id
            LEFT JOIN profesores p ON a.profesor_id = p.id
            WHERE a.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$asistencia) {
        redirect('listar.php');
    }
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
                        <i class="fas fa-eye"></i>Detalles de Asistencia
                        <a href="listar.php" class="btn btn-light btn-sm ms-auto">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Estudiante:</strong><br>
                                <?php echo htmlspecialchars($asistencia['estudiante_nombre']); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Materia:</strong><br>
                                <?php echo htmlspecialchars($asistencia['materia_nombre']); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Profesor:</strong><br>
                                <?php echo htmlspecialchars($asistencia['profesor_nombre']); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Fecha:</strong><br>
                                <?php echo date('d/m/Y', strtotime($asistencia['fecha'])); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-module estado-<?php echo $asistencia['estado']; ?>">
                                    <?php echo ucfirst($asistencia['estado']); ?>
                                </span>
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Observaciones:</strong><br>
                                <?php echo htmlspecialchars($asistencia['observaciones'] ?? 'Sin observaciones'); ?>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-school btn-attendance">
                                <i class="fas fa-edit me-2"></i>Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




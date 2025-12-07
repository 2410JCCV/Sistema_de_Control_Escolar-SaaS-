<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Enviar Notificación';
$errors = [];
$form_data = [];

try {
    $pdo = conectarDB();
    $usuarios_sql = "SELECT id, CONCAT(nombre, ' ', apellido) as nombre_completo, rol FROM usuarios WHERE estado = 'activo' ORDER BY nombre";
    $usuarios = $pdo->query($usuarios_sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $usuarios = [];
    $errors[] = "Error al cargar usuarios: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_notificacion'])) {
    $form_data = [
        'usuario_id' => (int)($_POST['usuario_id'] ?? 0),
        'titulo' => trim($_POST['titulo'] ?? ''),
        'mensaje' => trim($_POST['mensaje'] ?? ''),
        'tipo' => trim($_POST['tipo'] ?? 'info')
    ];
    
    if ($form_data['usuario_id'] <= 0) $errors[] = "Debe seleccionar un usuario";
    if (empty($form_data['titulo'])) $errors[] = "El título es requerido";
    if (empty($form_data['mensaje'])) $errors[] = "El mensaje es requerido";
    
    if (empty($errors)) {
        try {
            $insert_sql = "INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo, fecha_creacion) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($insert_sql);
            if ($stmt->execute([$form_data['usuario_id'], $form_data['titulo'], $form_data['mensaje'], $form_data['tipo']])) {
                header('Location: listar.php?success=' . urlencode('Notificación enviada exitosamente'));
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Error al enviar: " . $e->getMessage();
        }
    }
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
    </style>
</head>
<body class="dashboard-style">
    <div class="main-container">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><i class="fas fa-plus me-2"></i><?php echo $page_title; ?></h2>
                            <p class="mb-0 mt-2">Envía una notificación a un usuario</p>
                        </div>
                        <a href="listar.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-module">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-edit"></i>Formulario de Notificación
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="agregar_notificacion" value="1">
                            
                            <div class="mb-3">
                                <label for="usuario_id" class="form-label">Usuario <span class="text-danger">*</span></label>
                                <select class="form-select" id="usuario_id" name="usuario_id" required>
                                    <option value="">Seleccionar usuario</option>
                                    <?php foreach ($usuarios as $usr): ?>
                                    <option value="<?php echo $usr['id']; ?>" <?php echo ($form_data['usuario_id'] ?? 0) == $usr['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($usr['nombre_completo']); ?> (<?php echo $usr['rol']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="titulo" name="titulo" 
                                       value="<?php echo htmlspecialchars($form_data['titulo'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mensaje" class="form-label">Mensaje <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="mensaje" name="mensaje" rows="5" required><?php echo htmlspecialchars($form_data['mensaje'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" name="tipo">
                                    <option value="info" <?php echo ($form_data['tipo'] ?? 'info') == 'info' ? 'selected' : ''; ?>>Información</option>
                                    <option value="success" <?php echo ($form_data['tipo'] ?? '') == 'success' ? 'selected' : ''; ?>>Éxito</option>
                                    <option value="warning" <?php echo ($form_data['tipo'] ?? '') == 'warning' ? 'selected' : ''; ?>>Advertencia</option>
                                    <option value="danger" <?php echo ($form_data['tipo'] ?? '') == 'danger' ? 'selected' : ''; ?>>Error</option>
                                </select>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="listar.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-school btn-events btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Enviar Notificación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




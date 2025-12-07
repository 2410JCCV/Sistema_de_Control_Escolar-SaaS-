<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Agregar Pago';
$errors = [];
$form_data = [];

try {
    $pdo = conectarDB();
    $estudiantes = $pdo->query("SELECT id, CONCAT(codigo, ' - ', nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo FROM estudiantes WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $estudiantes = [];
    $errors[] = "Error al cargar estudiantes: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_pago'])) {
    $form_data = [
        'estudiante_id' => !empty($_POST['estudiante_id']) ? (int)$_POST['estudiante_id'] : null,
        'tipo' => trim($_POST['tipo'] ?? ''),
        'monto' => !empty($_POST['monto']) ? (float)$_POST['monto'] : 0.00,
        'fecha_pago' => trim($_POST['fecha_pago'] ?? ''),
        'fecha_vencimiento' => trim($_POST['fecha_vencimiento'] ?? ''),
        'metodo_pago' => trim($_POST['metodo_pago'] ?? ''),
        'numero_referencia' => trim($_POST['numero_referencia'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'estado' => trim($_POST['estado'] ?? 'pendiente')
    ];
    
    if (empty($form_data['estudiante_id'])) $errors[] = "Debe seleccionar un estudiante";
    if (empty($form_data['tipo'])) $errors[] = "El tipo es requerido";
    if ($form_data['monto'] <= 0) $errors[] = "El monto debe ser mayor a 0";
    
    if (empty($errors)) {
        try {
            $pdo = conectarDB();
            
            // Generar número de referencia si no existe
            if (empty($form_data['numero_referencia'])) {
                $form_data['numero_referencia'] = 'PAG-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            
            $sql = "INSERT INTO pagos (estudiante_id, tipo, monto, fecha_pago, fecha_vencimiento, metodo_pago, numero_referencia, descripcion, estado, fecha_creacion) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([
                $form_data['estudiante_id'], $form_data['tipo'], $form_data['monto'],
                $form_data['fecha_pago'] ? $form_data['fecha_pago'] : null,
                $form_data['fecha_vencimiento'], $form_data['metodo_pago'],
                $form_data['numero_referencia'], $form_data['descripcion'], $form_data['estado']
            ])) {
                header('Location: listar.php?success=' . urlencode('Pago agregado exitosamente'));
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Error al guardar: " . $e->getMessage();
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
                            <h2 class="mb-0"><i class="fas fa-plus me-2"></i><?php echo $page_title; ?></h2>
                            <p class="mb-0 mt-2">Registra un nuevo pago</p>
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
                        <i class="fas fa-edit"></i>Formulario de Pago
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="agregar_pago" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="estudiante_id" class="form-label">Estudiante <span class="text-danger">*</span></label>
                                    <select class="form-select" id="estudiante_id" name="estudiante_id" required>
                                        <option value="">Seleccionar estudiante</option>
                                        <?php foreach ($estudiantes as $est): ?>
                                        <option value="<?php echo $est['id']; ?>" <?php echo ($form_data['estudiante_id'] ?? null) == $est['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($est['nombre_completo']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="">Seleccionar</option>
                                        <option value="matricula" <?php echo ($form_data['tipo'] ?? '') == 'matricula' ? 'selected' : ''; ?>>Matrícula</option>
                                        <option value="mensualidad" <?php echo ($form_data['tipo'] ?? '') == 'mensualidad' ? 'selected' : ''; ?>>Mensualidad</option>
                                        <option value="material" <?php echo ($form_data['tipo'] ?? '') == 'material' ? 'selected' : ''; ?>>Material</option>
                                        <option value="evento" <?php echo ($form_data['tipo'] ?? '') == 'evento' ? 'selected' : ''; ?>>Evento</option>
                                        <option value="otro" <?php echo ($form_data['tipo'] ?? '') == 'otro' ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="monto" class="form-label">Monto <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="monto" name="monto" 
                                           value="<?php echo htmlspecialchars($form_data['monto'] ?? ''); ?>" step="0.01" min="0.01" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="fecha_pago" class="form-label">Fecha Pago</label>
                                    <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                                           value="<?php echo htmlspecialchars($form_data['fecha_pago'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="fecha_vencimiento" class="form-label">Fecha Vencimiento</label>
                                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" 
                                           value="<?php echo htmlspecialchars($form_data['fecha_vencimiento'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                                    <select class="form-select" id="metodo_pago" name="metodo_pago">
                                        <option value="">Seleccionar</option>
                                        <option value="efectivo" <?php echo ($form_data['metodo_pago'] ?? '') == 'efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                                        <option value="transferencia" <?php echo ($form_data['metodo_pago'] ?? '') == 'transferencia' ? 'selected' : ''; ?>>Transferencia</option>
                                        <option value="tarjeta" <?php echo ($form_data['metodo_pago'] ?? '') == 'tarjeta' ? 'selected' : ''; ?>>Tarjeta</option>
                                        <option value="cheque" <?php echo ($form_data['metodo_pago'] ?? '') == 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="numero_referencia" class="form-label">Número de Referencia</label>
                                    <input type="text" class="form-control" id="numero_referencia" name="numero_referencia" 
                                           value="<?php echo htmlspecialchars($form_data['numero_referencia'] ?? ''); ?>" 
                                           placeholder="Se genera automáticamente si se deja vacío">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="pendiente" <?php echo ($form_data['estado'] ?? 'pendiente') == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="completado" <?php echo ($form_data['estado'] ?? '') == 'completado' ? 'selected' : ''; ?>>Completado</option>
                                        <option value="vencido" <?php echo ($form_data['estado'] ?? '') == 'vencido' ? 'selected' : ''; ?>>Vencido</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($form_data['descripcion'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="listar.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-school btn-payments btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Pago
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




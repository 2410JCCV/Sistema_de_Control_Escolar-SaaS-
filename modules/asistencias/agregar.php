<?php
/**
 * Agregar Asistencia
 * Sistema de Control Escolar
 */

header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || (!hasPermission('admin') && !hasPermission('profesor'))) {
    redirect('index.php');
}

$page_title = 'Registrar Asistencia';
$errors = [];
$form_data = [];

try {
    $pdo = conectarDB();
    
    // Obtener estudiantes activos
    $estudiantes_sql = "SELECT id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo 
                        FROM estudiantes WHERE estado = 'activo' ORDER BY nombre";
    $estudiantes = $pdo->query($estudiantes_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener materias activas
    $materias_sql = "SELECT id, nombre FROM materias WHERE estado = 'activo' ORDER BY nombre";
    $materias = $pdo->query($materias_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener profesores activos
    $profesores_sql = "SELECT id, CONCAT(nombre, ' ', apellido_paterno) as nombre_completo 
                       FROM profesores WHERE estado = 'activo' ORDER BY nombre";
    $profesores = $pdo->query($profesores_sql)->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $estudiantes = [];
    $materias = [];
    $profesores = [];
    $errors[] = "Error al cargar datos: " . $e->getMessage();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_asistencia'])) {
    $form_data = [
        'estudiante_id' => (int)($_POST['estudiante_id'] ?? 0),
        'materia_id' => (int)($_POST['materia_id'] ?? 0),
        'profesor_id' => (int)($_POST['profesor_id'] ?? 0),
        'fecha' => trim($_POST['fecha'] ?? ''),
        'estado' => trim($_POST['estado'] ?? 'presente'),
        'observaciones' => trim($_POST['observaciones'] ?? '')
    ];
    
    // Validaciones
    if ($form_data['estudiante_id'] <= 0) $errors[] = "Debe seleccionar un estudiante";
    if ($form_data['materia_id'] <= 0) $errors[] = "Debe seleccionar una materia";
    if ($form_data['profesor_id'] <= 0) $errors[] = "Debe seleccionar un profesor";
    if (empty($form_data['fecha'])) $errors[] = "La fecha es requerida";
    
    if (empty($errors)) {
        try {
            $insert_sql = "INSERT INTO asistencias (estudiante_id, materia_id, profesor_id, fecha, estado, observaciones, fecha_creacion) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($insert_sql);
            $resultado = $stmt->execute([
                $form_data['estudiante_id'],
                $form_data['materia_id'],
                $form_data['profesor_id'],
                $form_data['fecha'],
                $form_data['estado'],
                $form_data['observaciones']
            ]);
            
            if ($resultado) {
                $mensaje = urlencode("Asistencia registrada exitosamente");
                header("Location: listar.php?success={$mensaje}");
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
            background: linear-gradient(135deg, var(--coral) 0%, #EF4444 100%);
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
                            <h2 class="mb-0"><i class="fas fa-calendar-check me-2"></i><?php echo $page_title; ?></h2>
                            <p class="mb-0 mt-2">Registra una nueva asistencia</p>
                        </div>
                        <a href="listar.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-module">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Errores:</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-edit"></i>Formulario de Asistencia
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="agregar_asistencia" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="estudiante_id" class="form-label">Estudiante <span class="text-danger">*</span></label>
                                    <select class="form-select" id="estudiante_id" name="estudiante_id" required>
                                        <option value="">Seleccionar estudiante</option>
                                        <?php foreach ($estudiantes as $est): ?>
                                        <option value="<?php echo $est['id']; ?>" 
                                                <?php echo ($form_data['estudiante_id'] ?? 0) == $est['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($est['nombre_completo']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="materia_id" class="form-label">Materia <span class="text-danger">*</span></label>
                                    <select class="form-select" id="materia_id" name="materia_id" required>
                                        <option value="">Seleccionar materia</option>
                                        <?php foreach ($materias as $mat): ?>
                                        <option value="<?php echo $mat['id']; ?>" 
                                                <?php echo ($form_data['materia_id'] ?? 0) == $mat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($mat['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="profesor_id" class="form-label">Profesor <span class="text-danger">*</span></label>
                                    <select class="form-select" id="profesor_id" name="profesor_id" required>
                                        <option value="">Seleccionar profesor</option>
                                        <?php foreach ($profesores as $prof): ?>
                                        <option value="<?php echo $prof['id']; ?>" 
                                                <?php echo ($form_data['profesor_id'] ?? 0) == $prof['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($prof['nombre_completo']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" 
                                           value="<?php echo htmlspecialchars($form_data['fecha'] ?? date('Y-m-d')); ?>" required>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="presente" <?php echo ($form_data['estado'] ?? 'presente') == 'presente' ? 'selected' : ''; ?>>Presente</option>
                                        <option value="ausente" <?php echo ($form_data['estado'] ?? '') == 'ausente' ? 'selected' : ''; ?>>Ausente</option>
                                        <option value="tardanza" <?php echo ($form_data['estado'] ?? '') == 'tardanza' ? 'selected' : ''; ?>>Tardanza</option>
                                        <option value="justificado" <?php echo ($form_data['estado'] ?? '') == 'justificado' ? 'selected' : ''; ?>>Justificado</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($form_data['observaciones'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="listar.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-school btn-attendance btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Asistencia
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




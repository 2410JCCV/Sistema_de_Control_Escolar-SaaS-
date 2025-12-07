<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Agregar Evento';
$errors = [];
$form_data = [];

try {
    $pdo = conectarDB();
    $profesores = $pdo->query("SELECT id, CONCAT(nombre, ' ', apellido_paterno) as nombre_completo FROM profesores WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $grupos = $pdo->query("SELECT id, nombre FROM grupos WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $grados = $pdo->query("SELECT id, nombre FROM grados WHERE estado = 'activo' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $profesores = [];
    $grupos = [];
    $grados = [];
    $errors[] = "Error al cargar datos: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_evento'])) {
    $form_data = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'tipo' => trim($_POST['tipo'] ?? 'academico'),
        'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
        'fecha_fin' => trim($_POST['fecha_fin'] ?? ''),
        'ubicacion' => trim($_POST['ubicacion'] ?? ''),
        'organizador_id' => !empty($_POST['organizador_id']) ? (int)$_POST['organizador_id'] : null,
        'grupo_id' => !empty($_POST['grupo_id']) ? (int)$_POST['grupo_id'] : null,
        'grado_id' => !empty($_POST['grado_id']) ? (int)$_POST['grado_id'] : null,
        'participantes_max' => !empty($_POST['participantes_max']) ? (int)$_POST['participantes_max'] : null,
        'costo' => !empty($_POST['costo']) ? (float)$_POST['costo'] : 0.00,
        'estado' => trim($_POST['estado'] ?? 'programado')
    ];
    
    if (empty($form_data['titulo'])) $errors[] = "El título es requerido";
    if (empty($form_data['fecha_inicio'])) $errors[] = "La fecha de inicio es requerida";
    
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO eventos (titulo, descripcion, tipo, fecha_inicio, fecha_fin, ubicacion, organizador_id, grupo_id, grado_id, participantes_max, costo, estado, fecha_creacion) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([
                $form_data['titulo'], $form_data['descripcion'], $form_data['tipo'], 
                $form_data['fecha_inicio'], $form_data['fecha_fin'], $form_data['ubicacion'],
                $form_data['organizador_id'], $form_data['grupo_id'], $form_data['grado_id'],
                $form_data['participantes_max'], $form_data['costo'], $form_data['estado']
            ])) {
                header('Location: listar.php?success=' . urlencode('Evento agregado exitosamente'));
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
                            <h2 class="mb-0"><i class="fas fa-plus me-2"></i><?php echo $page_title; ?></h2>
                            <p class="mb-0 mt-2">Crea un nuevo evento o actividad</p>
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
                        <i class="fas fa-edit"></i>Formulario de Evento
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="agregar_evento" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?php echo htmlspecialchars($form_data['titulo'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="tipo" class="form-label">Tipo</label>
                                    <select class="form-select" id="tipo" name="tipo">
                                        <option value="academico" <?php echo ($form_data['tipo'] ?? 'academico') == 'academico' ? 'selected' : ''; ?>>Académico</option>
                                        <option value="deportivo" <?php echo ($form_data['tipo'] ?? '') == 'deportivo' ? 'selected' : ''; ?>>Deportivo</option>
                                        <option value="cultural" <?php echo ($form_data['tipo'] ?? '') == 'cultural' ? 'selected' : ''; ?>>Cultural</option>
                                        <option value="social" <?php echo ($form_data['tipo'] ?? '') == 'social' ? 'selected' : ''; ?>>Social</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="programado" <?php echo ($form_data['estado'] ?? 'programado') == 'programado' ? 'selected' : ''; ?>>Programado</option>
                                        <option value="en_curso" <?php echo ($form_data['estado'] ?? '') == 'en_curso' ? 'selected' : ''; ?>>En Curso</option>
                                        <option value="finalizado" <?php echo ($form_data['estado'] ?? '') == 'finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($form_data['descripcion'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                           value="<?php echo htmlspecialchars($form_data['fecha_inicio'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin" 
                                           value="<?php echo htmlspecialchars($form_data['fecha_fin'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="ubicacion" class="form-label">Ubicación</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" 
                                           value="<?php echo htmlspecialchars($form_data['ubicacion'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="organizador_id" class="form-label">Organizador</label>
                                    <select class="form-select" id="organizador_id" name="organizador_id">
                                        <option value="">Seleccionar</option>
                                        <?php foreach ($profesores as $prof): ?>
                                        <option value="<?php echo $prof['id']; ?>" 
                                                <?php echo ($form_data['organizador_id'] ?? null) == $prof['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($prof['nombre_completo']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="participantes_max" class="form-label">Participantes Máx.</label>
                                    <input type="number" class="form-control" id="participantes_max" name="participantes_max" 
                                           value="<?php echo htmlspecialchars($form_data['participantes_max'] ?? ''); ?>" min="1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="grupo_id" class="form-label">Grupo</label>
                                    <select class="form-select" id="grupo_id" name="grupo_id">
                                        <option value="">Seleccionar</option>
                                        <?php foreach ($grupos as $grupo): ?>
                                        <option value="<?php echo $grupo['id']; ?>" 
                                                <?php echo ($form_data['grupo_id'] ?? null) == $grupo['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($grupo['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="grado_id" class="form-label">Grado</label>
                                    <select class="form-select" id="grado_id" name="grado_id">
                                        <option value="">Seleccionar</option>
                                        <?php foreach ($grados as $grado): ?>
                                        <option value="<?php echo $grado['id']; ?>" 
                                                <?php echo ($form_data['grado_id'] ?? null) == $grado['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($grado['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="costo" class="form-label">Costo</label>
                                    <input type="number" class="form-control" id="costo" name="costo" step="0.01" min="0" 
                                           value="<?php echo htmlspecialchars($form_data['costo'] ?? '0.00'); ?>">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="listar.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-school btn-events btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Evento
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




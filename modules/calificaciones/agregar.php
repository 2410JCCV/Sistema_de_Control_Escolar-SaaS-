<?php
/**
 * Agregar Calificación - Formulario Completo
 * Sistema de Control Escolar
 */

// Configurar codificación UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn() || (!hasPermission('admin') && !hasPermission('profesor'))) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Agregar Calificación';

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [
    'estudiante_id' => '',
    'materia_id' => '',
    'profesor_id' => '',
    'tipo_evaluacion' => 'examen',
    'calificacion' => '',
    'fecha_evaluacion' => '',
    'observaciones' => ''
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_calificacion'])) {
    try {
        // Obtener y sanitizar datos
        $form_data = [
            'estudiante_id' => trim($_POST['estudiante_id'] ?? ''),
            'materia_id' => trim($_POST['materia_id'] ?? ''),
            'profesor_id' => trim($_POST['profesor_id'] ?? ''),
            'tipo_evaluacion' => trim($_POST['tipo_evaluacion'] ?? 'examen'),
            'calificacion' => trim($_POST['calificacion'] ?? ''),
            'fecha_evaluacion' => trim($_POST['fecha_evaluacion'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];
        
        // Validaciones
        if (empty($form_data['estudiante_id'])) {
            $errors[] = "El estudiante es requerido";
        } elseif (!is_numeric($form_data['estudiante_id']) || $form_data['estudiante_id'] <= 0) {
            $errors[] = "El estudiante seleccionado no es válido";
        }
        
        if (empty($form_data['materia_id'])) {
            $errors[] = "La materia es requerida";
        } elseif (!is_numeric($form_data['materia_id']) || $form_data['materia_id'] <= 0) {
            $errors[] = "La materia seleccionada no es válida";
        }
        
        if (empty($form_data['profesor_id'])) {
            $errors[] = "El profesor es requerido";
        } elseif (!is_numeric($form_data['profesor_id']) || $form_data['profesor_id'] <= 0) {
            $errors[] = "El profesor seleccionado no es válido";
        }
        
        if (empty($form_data['calificacion'])) {
            $errors[] = "La calificación es requerida";
        } elseif (!is_numeric($form_data['calificacion']) || $form_data['calificacion'] < 0 || $form_data['calificacion'] > 100) {
            $errors[] = "La calificación debe ser un número entre 0 y 100";
        }
        
        if (empty($form_data['fecha_evaluacion'])) {
            $errors[] = "La fecha de evaluación es requerida";
        } else {
            $fecha_evaluacion = DateTime::createFromFormat('Y-m-d', $form_data['fecha_evaluacion']);
            if (!$fecha_evaluacion || $fecha_evaluacion->format('Y-m-d') !== $form_data['fecha_evaluacion']) {
                $errors[] = "La fecha de evaluación no es válida";
            }
        }
        
        if (!in_array($form_data['tipo_evaluacion'], ['examen', 'tarea', 'proyecto', 'participacion', 'practica'])) {
            $errors[] = "El tipo de evaluación no es válido";
        }
        
        // Si no hay errores, insertar en la base de datos
        if (empty($errors)) {
            try {
                $pdo = conectarDB();
                
                // Insertar calificación
                $insert_sql = "
                    INSERT INTO calificaciones (
                        estudiante_id, materia_id, profesor_id, tipo_evaluacion, 
                        calificacion, fecha_evaluacion, observaciones, fecha_creacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ";
                
                $stmt = $pdo->prepare($insert_sql);
                $resultado = $stmt->execute([
                    $form_data['estudiante_id'],
                    $form_data['materia_id'],
                    $form_data['profesor_id'],
                    $form_data['tipo_evaluacion'],
                    $form_data['calificacion'],
                    $form_data['fecha_evaluacion'],
                    $form_data['observaciones'] ?: null
                ]);
                
                if ($resultado) {
                    $calificacion_id = $pdo->lastInsertId();
                    $success_message = "Calificación agregada exitosamente";
                    
                    // Redirigir a la lista con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: index.php?success={$mensaje_url}");
                    exit();
                } else {
                    $errors[] = "Error al guardar la calificación en la base de datos";
                }
                
            } catch (PDOException $e) {
                $errors[] = "Error de base de datos: " . $e->getMessage();
            }
        }
        
    } catch (Exception $e) {
        $errors[] = "Error inesperado: " . $e->getMessage();
    }
}

// Obtener datos para los dropdowns
try {
    $pdo = conectarDB();
    
    // Obtener estudiantes activos
    $estudiantes_sql = "SELECT id, nombre, apellido_paterno, apellido_materno FROM estudiantes WHERE estado = 'activo' ORDER BY nombre, apellido_paterno";
    $estudiantes = $pdo->query($estudiantes_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener materias activas
    $materias_sql = "SELECT id, nombre FROM materias WHERE estado = 'activo' ORDER BY nombre";
    $materias = $pdo->query($materias_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener profesores activos
    $profesores_sql = "SELECT id, nombre, apellido_paterno, apellido_materno FROM profesores WHERE estado = 'activo' ORDER BY nombre, apellido_paterno";
    $profesores = $pdo->query($profesores_sql)->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $estudiantes = [];
    $materias = [];
    $profesores = [];
    $errors[] = "Error al cargar los datos: " . $e->getMessage();
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
        .page-header h2 {
            color: white;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.2);
        }
        .form-section {
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px solid var(--purple);
        }
        .required {
            color: #dc3545;
        }
        .calificacion-info {
            background: linear-gradient(135deg, var(--purple) 0%, #7C3AED 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="dashboard-style">
    <div class="main-container">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>
                                <?php echo $page_title; ?>
                            </h2>
                            <p class="mb-0 mt-2">Registra una nueva calificación en el sistema</p>
                        </div>
                        <div>
                            <a href="index.php" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mensajes de error -->
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Por favor corrige los siguientes errores:</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Formulario -->
                <div class="row">
                    <div class="col-lg-8">
                        <form method="POST" id="formAgregarCalificacion" novalidate>
                            <input type="hidden" name="agregar_calificacion" value="1">
                            
                            <!-- Información de la Evaluación -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-chart-line me-2"></i>Información de la Evaluación
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select <?php echo (isset($errors) && in_array('El estudiante es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                    id="estudiante_id" 
                                                    name="estudiante_id" 
                                                    required>
                                                <option value="">Selecciona un estudiante</option>
                                                <?php foreach ($estudiantes as $estudiante): ?>
                                                <option value="<?php echo $estudiante['id']; ?>" 
                                                        <?php echo (isset($form_data['estudiante_id']) && $form_data['estudiante_id'] == $estudiante['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="estudiante_id">Estudiante <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select <?php echo (isset($errors) && in_array('La materia es requerida', $errors)) ? 'is-invalid' : ''; ?>" 
                                                    id="materia_id" 
                                                    name="materia_id" 
                                                    required>
                                                <option value="">Selecciona una materia</option>
                                                <?php foreach ($materias as $materia): ?>
                                                <option value="<?php echo $materia['id']; ?>" 
                                                        <?php echo (isset($form_data['materia_id']) && $form_data['materia_id'] == $materia['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($materia['nombre']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="materia_id">Materia <span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select <?php echo (isset($errors) && in_array('El profesor es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                    id="profesor_id" 
                                                    name="profesor_id" 
                                                    required>
                                                <option value="">Selecciona un profesor</option>
                                                <?php foreach ($profesores as $profesor): ?>
                                                <option value="<?php echo $profesor['id']; ?>" 
                                                        <?php echo (isset($form_data['profesor_id']) && $form_data['profesor_id'] == $profesor['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido_paterno'] . ' ' . $profesor['apellido_materno']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="profesor_id">Profesor <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="tipo_evaluacion" name="tipo_evaluacion">
                                                <option value="examen" <?php echo ($form_data['tipo_evaluacion'] == 'examen') ? 'selected' : ''; ?>>Examen</option>
                                                <option value="tarea" <?php echo ($form_data['tipo_evaluacion'] == 'tarea') ? 'selected' : ''; ?>>Tarea</option>
                                                <option value="proyecto" <?php echo ($form_data['tipo_evaluacion'] == 'proyecto') ? 'selected' : ''; ?>>Proyecto</option>
                                                <option value="participacion" <?php echo ($form_data['tipo_evaluacion'] == 'participacion') ? 'selected' : ''; ?>>Participación</option>
                                                <option value="practica" <?php echo ($form_data['tipo_evaluacion'] == 'practica') ? 'selected' : ''; ?>>Práctica</option>
                                            </select>
                                            <label for="tipo_evaluacion">Tipo de Evaluación</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Calificación y Fecha -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-star me-2"></i>Calificación y Fecha
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="number" 
                                                   class="form-control <?php echo (isset($errors) && in_array('La calificación es requerida', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="calificacion" 
                                                   name="calificacion" 
                                                   value="<?php echo htmlspecialchars($form_data['calificacion'] ?? ''); ?>" 
                                                   placeholder="0.00" 
                                                   step="0.01" 
                                                   min="0" 
                                                   max="100" 
                                                   required>
                                            <label for="calificacion">Calificación (0-100) <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="date" 
                                                   class="form-control <?php echo (isset($errors) && in_array('La fecha de evaluación es requerida', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="fecha_evaluacion" 
                                                   name="fecha_evaluacion" 
                                                   value="<?php echo htmlspecialchars($form_data['fecha_evaluacion'] ?? ''); ?>" 
                                                   placeholder="Fecha de Evaluación" 
                                                   required>
                                            <label for="fecha_evaluacion">Fecha de Evaluación <span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" 
                                              id="observaciones" 
                                              name="observaciones" 
                                              placeholder="Observaciones sobre la calificación" 
                                              style="height: 100px"><?php echo htmlspecialchars($form_data['observaciones'] ?? ''); ?></textarea>
                                    <label for="observaciones">Observaciones</label>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-school btn-grades btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Calificación
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Panel de ayuda -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Información Importante
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Calificación:</strong> Escala de 0 a 100
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Fecha:</strong> Fecha en que se realizó la evaluación
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Tipo:</strong> Examen, tarea, proyecto, etc.
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Campos obligatorios:</strong> Marcados con *
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>Consejos
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Selecciona el estudiante correcto
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Verifica que la materia sea la correcta
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Las observaciones ayudan al seguimiento
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación del formulario
        document.getElementById('formAgregarCalificacion').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            // Validar campos requeridos
            const requiredFields = ['estudiante_id', 'materia_id', 'profesor_id', 'calificacion', 'fecha_evaluacion'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            // Validar calificación
            const calificacion = document.getElementById('calificacion');
            if (calificacion.value && (isNaN(calificacion.value) || parseFloat(calificacion.value) < 0 || parseFloat(calificacion.value) > 100)) {
                calificacion.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor corrige los errores en el formulario');
            }
        });
        
        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
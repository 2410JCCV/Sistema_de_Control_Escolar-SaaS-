<?php
/**
 * Editar Calificación - Formulario Completo
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

$page_title = 'Editar Calificación';

// Obtener ID de la calificación
$calificacion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($calificacion_id <= 0) {
    $_SESSION['error'] = 'ID de calificación no válido';
    redirect('index.php');
}

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [];

// Obtener datos de la calificación
try {
    $pdo = conectarDB();
    
    $sql = "SELECT * FROM calificaciones WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$calificacion_id]);
    $calificacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$calificacion) {
        $_SESSION['error'] = 'Calificación no encontrada';
        redirect('index.php');
    }
    
    // Cargar datos en el formulario
    $form_data = [
        'estudiante_id' => $calificacion['estudiante_id'],
        'materia_id' => $calificacion['materia_id'],
        'profesor_id' => $calificacion['profesor_id'],
        'tipo_evaluacion' => $calificacion['tipo_evaluacion'],
        'calificacion' => $calificacion['calificacion'],
        'fecha_evaluacion' => $calificacion['fecha_evaluacion'],
        'observaciones' => $calificacion['observaciones']
    ];
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos de la calificación: ' . $e->getMessage();
    redirect('index.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_calificacion'])) {
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
        
        // Si no hay errores, actualizar en la base de datos
        if (empty($errors)) {
            try {
                // Actualizar calificación
                $update_sql = "
                    UPDATE calificaciones SET 
                        estudiante_id = ?, materia_id = ?, profesor_id = ?, 
                        tipo_evaluacion = ?, calificacion = ?, fecha_evaluacion = ?, 
                        observaciones = ?, fecha_actualizacion = NOW()
                    WHERE id = ?
                ";
                
                $stmt = $pdo->prepare($update_sql);
                $resultado = $stmt->execute([
                    $form_data['estudiante_id'],
                    $form_data['materia_id'],
                    $form_data['profesor_id'],
                    $form_data['tipo_evaluacion'],
                    $form_data['calificacion'],
                    $form_data['fecha_evaluacion'],
                    $form_data['observaciones'] ?: null,
                    $calificacion_id
                ]);
                
                if ($resultado) {
                    $success_message = "Calificación actualizada exitosamente";
                    
                    // Redirigir a la vista de la calificación con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: ver.php?id={$calificacion_id}&success={$mensaje_url}");
                    exit();
                } else {
                    $errors[] = "Error al actualizar la calificación en la base de datos";
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
    <style>
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .required {
            color: #dc3545;
        }
        .form-floating > .form-control:focus ~ label {
            color: #0d6efd;
        }
        .calificacion-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0">
                            <i class="fas fa-edit me-2 text-primary"></i>
                            <?php echo $page_title; ?>
                        </h2>
                        <p class="text-muted mb-0">Modifica la información de la calificación</p>
                    </div>
                    <div>
                        <a href="ver.php?id=<?php echo $calificacion_id; ?>" class="btn btn-outline-info me-2">
                            <i class="fas fa-eye me-2"></i>Ver Detalles
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
                    </div>
                </div>

                <!-- Información de la calificación -->
                <div class="calificacion-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                Calificación ID: #<?php echo $calificacion['id']; ?>
                            </h4>
                            <p class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                Calificación actual: <strong><?php echo number_format($calificacion['calificacion'], 2); ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-light text-dark fs-6">
                                <?php echo ucfirst($calificacion['tipo_evaluacion']); ?>
                            </span>
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
                        <form method="POST" id="formEditarCalificacion" novalidate>
                            <input type="hidden" name="actualizar_calificacion" value="1">
                            
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
                                <a href="ver.php?id=<?php echo $calificacion_id; ?>" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-save me-2"></i>Actualizar Calificación
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
                                    Verifica que todos los datos sean correctos
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    La calificación debe estar entre 0 y 100
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Los cambios se guardarán inmediatamente
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
        document.getElementById('formEditarCalificacion').addEventListener('submit', function(e) {
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

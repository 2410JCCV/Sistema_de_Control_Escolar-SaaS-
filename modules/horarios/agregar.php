<?php
/**
 * Agregar Horario - Formulario Completo
 * Sistema de Control Escolar
 */

// Configurar codificación UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn() || !hasPermission('admin')) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Agregar Horario';

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [
    'materia_id' => '',
    'profesor_id' => '',
    'grupo_id' => '',
    'aula_id' => '',
    'dia_semana' => 'lunes',
    'hora_inicio' => '',
    'hora_fin' => '',
    'estado' => 'activo'
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_horario'])) {
    try {
        // Obtener y sanitizar datos
        $form_data = [
            'materia_id' => trim($_POST['materia_id'] ?? ''),
            'profesor_id' => trim($_POST['profesor_id'] ?? ''),
            'grupo_id' => trim($_POST['grupo_id'] ?? ''),
            'aula_id' => trim($_POST['aula_id'] ?? ''),
            'dia_semana' => trim($_POST['dia_semana'] ?? 'lunes'),
            'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
            'hora_fin' => trim($_POST['hora_fin'] ?? ''),
            'estado' => trim($_POST['estado'] ?? 'activo')
        ];
        
        // Validaciones
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
        
        if (empty($form_data['grupo_id'])) {
            $errors[] = "El grupo es requerido";
        } elseif (!is_numeric($form_data['grupo_id']) || $form_data['grupo_id'] <= 0) {
            $errors[] = "El grupo seleccionado no es válido";
        }
        
        if (empty($form_data['aula_id'])) {
            $errors[] = "El aula es requerida";
        } elseif (!is_numeric($form_data['aula_id']) || $form_data['aula_id'] <= 0) {
            $errors[] = "El aula seleccionada no es válida";
        }
        
        if (empty($form_data['hora_inicio'])) {
            $errors[] = "La hora de inicio es requerida";
        }
        
        if (empty($form_data['hora_fin'])) {
            $errors[] = "La hora de fin es requerida";
        }
        
        // Validar que la hora de fin sea mayor que la hora de inicio
        if (!empty($form_data['hora_inicio']) && !empty($form_data['hora_fin'])) {
            $hora_inicio = strtotime($form_data['hora_inicio']);
            $hora_fin = strtotime($form_data['hora_fin']);
            
            if ($hora_fin <= $hora_inicio) {
                $errors[] = "La hora de fin debe ser mayor que la hora de inicio";
            }
        }
        
        if (!in_array($form_data['dia_semana'], ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'])) {
            $errors[] = "El día de la semana seleccionado no es válido";
        }
        
        if (!in_array($form_data['estado'], ['activo', 'inactivo'])) {
            $errors[] = "El estado seleccionado no es válido";
        }
        
        // Verificar conflictos de horario
        if (empty($errors)) {
            try {
                $pdo = conectarDB();
                
                // Verificar si el profesor ya tiene una clase en ese horario
                $stmt = $pdo->prepare("
                    SELECT id FROM horarios 
                    WHERE profesor_id = ? AND dia_semana = ? 
                    AND ((hora_inicio <= ? AND hora_fin > ?) OR (hora_inicio < ? AND hora_fin >= ?))
                    AND estado = 'activo'
                ");
                $stmt->execute([
                    $form_data['profesor_id'], $form_data['dia_semana'],
                    $form_data['hora_inicio'], $form_data['hora_inicio'],
                    $form_data['hora_fin'], $form_data['hora_fin']
                ]);
                if ($stmt->fetch()) {
                    $errors[] = "El profesor ya tiene una clase en ese horario";
                }
                
                // Verificar si el grupo ya tiene una clase en ese horario
                $stmt = $pdo->prepare("
                    SELECT id FROM horarios 
                    WHERE grupo_id = ? AND dia_semana = ? 
                    AND ((hora_inicio <= ? AND hora_fin > ?) OR (hora_inicio < ? AND hora_fin >= ?))
                    AND estado = 'activo'
                ");
                $stmt->execute([
                    $form_data['grupo_id'], $form_data['dia_semana'],
                    $form_data['hora_inicio'], $form_data['hora_inicio'],
                    $form_data['hora_fin'], $form_data['hora_fin']
                ]);
                if ($stmt->fetch()) {
                    $errors[] = "El grupo ya tiene una clase en ese horario";
                }
                
                // Verificar si el aula ya está ocupada en ese horario
                $stmt = $pdo->prepare("
                    SELECT id FROM horarios 
                    WHERE aula_id = ? AND dia_semana = ? 
                    AND ((hora_inicio <= ? AND hora_fin > ?) OR (hora_inicio < ? AND hora_fin >= ?))
                    AND estado = 'activo'
                ");
                $stmt->execute([
                    $form_data['aula_id'], $form_data['dia_semana'],
                    $form_data['hora_inicio'], $form_data['hora_inicio'],
                    $form_data['hora_fin'], $form_data['hora_fin']
                ]);
                if ($stmt->fetch()) {
                    $errors[] = "El aula ya está ocupada en ese horario";
                }
                
            } catch (Exception $e) {
                $errors[] = "Error al verificar conflictos: " . $e->getMessage();
            }
        }
        
        // Si no hay errores, insertar en la base de datos
        if (empty($errors)) {
            try {
                // Insertar horario
                $insert_sql = "
                    INSERT INTO horarios (
                        materia_id, profesor_id, grupo_id, aula_id, dia_semana, 
                        hora_inicio, hora_fin, estado, fecha_creacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ";
                
                $stmt = $pdo->prepare($insert_sql);
                $resultado = $stmt->execute([
                    $form_data['materia_id'],
                    $form_data['profesor_id'],
                    $form_data['grupo_id'],
                    $form_data['aula_id'],
                    $form_data['dia_semana'],
                    $form_data['hora_inicio'],
                    $form_data['hora_fin'],
                    $form_data['estado']
                ]);
                
                if ($resultado) {
                    $horario_id = $pdo->lastInsertId();
                    $success_message = "Horario agregado exitosamente";
                    
                    // Redirigir a la lista con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: index.php?success={$mensaje_url}");
                    exit();
                } else {
                    $errors[] = "Error al guardar el horario en la base de datos";
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
    
    // Obtener materias activas
    $materias_sql = "SELECT id, nombre FROM materias WHERE estado = 'activo' ORDER BY nombre";
    $materias = $pdo->query($materias_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener profesores activos
    $profesores_sql = "SELECT id, nombre, apellido_paterno FROM profesores WHERE estado = 'activo' ORDER BY nombre, apellido_paterno";
    $profesores = $pdo->query($profesores_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener grupos activos
    $grupos_sql = "SELECT g.id, g.nombre, gr.nombre as grado_nombre 
                   FROM grupos g 
                   LEFT JOIN grados gr ON g.grado_id = gr.id 
                   WHERE g.estado = 'activo' 
                   ORDER BY gr.nombre, g.nombre";
    $grupos = $pdo->query($grupos_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener aulas activas
    $aulas_sql = "SELECT id, nombre FROM aulas WHERE estado = 'activo' ORDER BY nombre";
    $aulas = $pdo->query($aulas_sql)->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $materias = [];
    $profesores = [];
    $grupos = [];
    $aulas = [];
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
        .horario-info {
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
                            <i class="fas fa-plus-circle me-2 text-primary"></i>
                            <?php echo $page_title; ?>
                        </h2>
                        <p class="text-muted mb-0">Registra un nuevo horario en el sistema</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
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
                        <form method="POST" id="formAgregarHorario" novalidate>
                            <input type="hidden" name="agregar_horario" value="1">
                            
                            <!-- Información de la Clase -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-book me-2"></i>Información de la Clase
                                </h5>
                                
                                <div class="row">
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
                                                    <?php echo htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido_paterno']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="profesor_id">Profesor <span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select <?php echo (isset($errors) && in_array('El grupo es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                    id="grupo_id" 
                                                    name="grupo_id" 
                                                    required>
                                                <option value="">Selecciona un grupo</option>
                                                <?php foreach ($grupos as $grupo): ?>
                                                <option value="<?php echo $grupo['id']; ?>" 
                                                        <?php echo (isset($form_data['grupo_id']) && $form_data['grupo_id'] == $grupo['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($grupo['grado_nombre'] . ' - ' . $grupo['nombre']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="grupo_id">Grupo <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select <?php echo (isset($errors) && in_array('El aula es requerida', $errors)) ? 'is-invalid' : ''; ?>" 
                                                    id="aula_id" 
                                                    name="aula_id" 
                                                    required>
                                                <option value="">Selecciona un aula</option>
                                                <?php foreach ($aulas as $aula): ?>
                                                <option value="<?php echo $aula['id']; ?>" 
                                                        <?php echo (isset($form_data['aula_id']) && $form_data['aula_id'] == $aula['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($aula['nombre']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="aula_id">Aula <span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del Horario -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-clock me-2"></i>Información del Horario
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="dia_semana" name="dia_semana">
                                                <option value="lunes" <?php echo ($form_data['dia_semana'] == 'lunes') ? 'selected' : ''; ?>>Lunes</option>
                                                <option value="martes" <?php echo ($form_data['dia_semana'] == 'martes') ? 'selected' : ''; ?>>Martes</option>
                                                <option value="miercoles" <?php echo ($form_data['dia_semana'] == 'miercoles') ? 'selected' : ''; ?>>Miércoles</option>
                                                <option value="jueves" <?php echo ($form_data['dia_semana'] == 'jueves') ? 'selected' : ''; ?>>Jueves</option>
                                                <option value="viernes" <?php echo ($form_data['dia_semana'] == 'viernes') ? 'selected' : ''; ?>>Viernes</option>
                                                <option value="sabado" <?php echo ($form_data['dia_semana'] == 'sabado') ? 'selected' : ''; ?>>Sábado</option>
                                            </select>
                                            <label for="dia_semana">Día de la Semana</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <input type="time" 
                                                   class="form-control <?php echo (isset($errors) && in_array('La hora de inicio es requerida', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="hora_inicio" 
                                                   name="hora_inicio" 
                                                   value="<?php echo htmlspecialchars($form_data['hora_inicio']); ?>" 
                                                   required>
                                            <label for="hora_inicio">Hora de Inicio <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <input type="time" 
                                                   class="form-control <?php echo (isset($errors) && in_array('La hora de fin es requerida', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="hora_fin" 
                                                   name="hora_fin" 
                                                   value="<?php echo htmlspecialchars($form_data['hora_fin']); ?>" 
                                                   required>
                                            <label for="hora_fin">Hora de Fin <span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="estado" name="estado">
                                                <option value="activo" <?php echo ($form_data['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                                <option value="inactivo" <?php echo ($form_data['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                            </select>
                                            <label for="estado">Estado</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Horario
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
                                        <strong>Materia:</strong> Asignatura a impartir
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Profesor:</strong> Quien imparte la clase
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Grupo:</strong> Estudiantes que recibirán la clase
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Aula:</strong> Lugar donde se impartirá
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Horario:</strong> Día y hora de la clase
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
                                    Se verifican conflictos automáticamente
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    La hora de fin debe ser mayor que la de inicio
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    No se pueden tener clases simultáneas
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
        document.getElementById('formAgregarHorario').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            // Validar campos requeridos
            const requiredFields = ['materia_id', 'profesor_id', 'grupo_id', 'aula_id', 'hora_inicio', 'hora_fin'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            // Validar horarios
            const horaInicio = document.getElementById('hora_inicio');
            const horaFin = document.getElementById('hora_fin');
            
            if (horaInicio.value && horaFin.value) {
                const inicio = new Date('2000-01-01 ' + horaInicio.value);
                const fin = new Date('2000-01-01 ' + horaFin.value);
                
                if (fin <= inicio) {
                    horaFin.classList.add('is-invalid');
                    isValid = false;
                }
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



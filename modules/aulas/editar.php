<?php
/**
 * Editar Aula - Formulario Completo
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

$page_title = 'Editar Aula';

// Obtener ID del aula
$aula_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($aula_id <= 0) {
    $_SESSION['error'] = 'ID de aula no válido';
    redirect('index.php');
}

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [];

// Obtener datos del aula
try {
    $pdo = conectarDB();
    
    $sql = "SELECT * FROM aulas WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$aula_id]);
    $aula = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$aula) {
        $_SESSION['error'] = 'Aula no encontrada';
        redirect('index.php');
    }
    
    // Cargar datos en el formulario
    $form_data = [
        'nombre' => $aula['nombre'],
        'ubicacion' => $aula['ubicacion'],
        'capacidad' => $aula['capacidad'],
        'tipo' => $aula['tipo'],
        'estado' => $aula['estado']
    ];
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos del aula: ' . $e->getMessage();
    redirect('index.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_aula'])) {
    try {
        // Obtener y sanitizar datos
        $form_data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'ubicacion' => trim($_POST['ubicacion'] ?? ''),
            'capacidad' => trim($_POST['capacidad'] ?? ''),
            'tipo' => trim($_POST['tipo'] ?? 'aula'),
            'estado' => trim($_POST['estado'] ?? 'activo')
        ];
        
        // Validaciones
        if (empty($form_data['nombre'])) {
            $errors[] = "El nombre del aula es requerido";
        } elseif (strlen($form_data['nombre']) < 2) {
            $errors[] = "El nombre del aula debe tener al menos 2 caracteres";
        }
        
        if (!empty($form_data['capacidad']) && (!is_numeric($form_data['capacidad']) || $form_data['capacidad'] < 1)) {
            $errors[] = "La capacidad debe ser un número mayor a 0";
        }
        
        if (!in_array($form_data['tipo'], ['aula', 'laboratorio', 'biblioteca', 'gimnasio'])) {
            $errors[] = "El tipo seleccionado no es válido";
        }
        
        if (!in_array($form_data['estado'], ['activo', 'inactivo', 'mantenimiento'])) {
            $errors[] = "El estado seleccionado no es válido";
        }
        
        // Verificar que el nombre del aula no exista (excluyendo el aula actual)
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM aulas WHERE nombre = ? AND id != ?");
                $stmt->execute([$form_data['nombre'], $aula_id]);
                if ($stmt->fetch()) {
                    $errors[] = "Ya existe un aula con ese nombre";
                }
                
            } catch (Exception $e) {
                $errors[] = "Error al verificar datos: " . $e->getMessage();
            }
        }
        
        // Si no hay errores, actualizar en la base de datos
        if (empty($errors)) {
            try {
                // Actualizar aula
                $update_sql = "
                    UPDATE aulas SET 
                        nombre = ?, ubicacion = ?, capacidad = ?, tipo = ?, estado = ?
                    WHERE id = ?
                ";
                
                $stmt = $pdo->prepare($update_sql);
                $resultado = $stmt->execute([
                    $form_data['nombre'],
                    $form_data['ubicacion'] ?: null,
                    $form_data['capacidad'] ?: null,
                    $form_data['tipo'],
                    $form_data['estado'],
                    $aula_id
                ]);
                
                if ($resultado) {
                    $success_message = "Aula actualizada exitosamente";
                    
                    // Redirigir a la vista del aula con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: ver.php?id={$aula_id}&success={$mensaje_url}");
                    exit();
                } else {
                    $errors[] = "Error al actualizar el aula en la base de datos";
                }
                
            } catch (PDOException $e) {
                $errors[] = "Error de base de datos: " . $e->getMessage();
            }
        }
        
    } catch (Exception $e) {
        $errors[] = "Error inesperado: " . $e->getMessage();
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
        .aula-info {
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
                        <p class="text-muted mb-0">Modifica la información del aula</p>
                    </div>
                    <div>
                        <a href="ver.php?id=<?php echo $aula_id; ?>" class="btn btn-outline-info me-2">
                            <i class="fas fa-eye me-2"></i>Ver Detalles
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
                    </div>
                </div>

                <!-- Información del aula -->
                <div class="aula-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                Aula ID: #<?php echo $aula['id']; ?>
                            </h4>
                            <p class="mb-0">
                                <i class="fas fa-door-open me-2"></i>
                                Aula actual: <strong><?php echo htmlspecialchars($aula['nombre']); ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-light text-dark fs-6">
                                <?php echo ucfirst($aula['estado']); ?>
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
                        <form method="POST" id="formEditarAula" novalidate>
                            <input type="hidden" name="actualizar_aula" value="1">
                            
                            <!-- Información del Aula -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-door-open me-2"></i>Información del Aula
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control <?php echo (isset($errors) && in_array('El nombre del aula es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="nombre" 
                                                   name="nombre" 
                                                   value="<?php echo htmlspecialchars($form_data['nombre']); ?>" 
                                                   placeholder="Nombre del aula" 
                                                   required>
                                            <label for="nombre">Nombre del Aula <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="ubicacion" 
                                                   name="ubicacion" 
                                                   value="<?php echo htmlspecialchars($form_data['ubicacion']); ?>" 
                                                   placeholder="Ubicación">
                                            <label for="ubicacion">Ubicación (opcional)</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="number" 
                                                   class="form-control <?php echo (isset($errors) && in_array('La capacidad debe ser un número mayor a 0', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="capacidad" 
                                                   name="capacidad" 
                                                   value="<?php echo htmlspecialchars($form_data['capacidad']); ?>" 
                                                   placeholder="Capacidad" 
                                                   min="1">
                                            <label for="capacidad">Capacidad (opcional)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="tipo" name="tipo">
                                                <option value="aula" <?php echo ($form_data['tipo'] == 'aula') ? 'selected' : ''; ?>>Aula</option>
                                                <option value="laboratorio" <?php echo ($form_data['tipo'] == 'laboratorio') ? 'selected' : ''; ?>>Laboratorio</option>
                                                <option value="biblioteca" <?php echo ($form_data['tipo'] == 'biblioteca') ? 'selected' : ''; ?>>Biblioteca</option>
                                                <option value="gimnasio" <?php echo ($form_data['tipo'] == 'gimnasio') ? 'selected' : ''; ?>>Gimnasio</option>
                                            </select>
                                            <label for="tipo">Tipo de Aula</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="estado" name="estado">
                                                <option value="activo" <?php echo ($form_data['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                                <option value="inactivo" <?php echo ($form_data['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                                <option value="mantenimiento" <?php echo ($form_data['estado'] == 'mantenimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                                            </select>
                                            <label for="estado">Estado</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-between">
                                <a href="ver.php?id=<?php echo $aula_id; ?>" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-save me-2"></i>Actualizar Aula
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
                                        <strong>Nombre:</strong> Identificador único del aula
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Ubicación:</strong> Descripción de la ubicación (opcional)
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Capacidad:</strong> Número máximo de personas (opcional)
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Tipo:</strong> Aula, laboratorio, biblioteca o gimnasio
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Estado:</strong> Activo, inactivo o mantenimiento
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
                                    El nombre debe ser único
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    La ubicación ayuda a identificar el aula
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
        document.getElementById('formEditarAula').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            // Validar campos requeridos
            const requiredFields = ['nombre'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            // Validar capacidad
            const capacidad = document.getElementById('capacidad');
            if (capacidad.value && (isNaN(capacidad.value) || parseInt(capacidad.value) < 1)) {
                capacidad.classList.add('is-invalid');
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



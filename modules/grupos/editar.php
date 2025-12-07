<?php
/**
 * Editar Grupo - Formulario Completo
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

$page_title = 'Editar Grupo';

// Obtener ID del grupo
$grupo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($grupo_id <= 0) {
    $_SESSION['error'] = 'ID de grupo no válido';
    redirect('index.php');
}

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [];

// Obtener datos del grupo
try {
    $pdo = conectarDB();
    
    $sql = "SELECT * FROM grupos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$grupo_id]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$grupo) {
        $_SESSION['error'] = 'Grupo no encontrado';
        redirect('index.php');
    }
    
    // Cargar datos en el formulario
    $form_data = [
        'nombre' => $grupo['nombre'],
        'grado_id' => $grupo['grado_id'],
        'capacidad' => $grupo['capacidad'],
        'estado' => $grupo['estado']
    ];
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos del grupo: ' . $e->getMessage();
    redirect('index.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_grupo'])) {
    try {
        // Obtener y sanitizar datos
        $form_data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'grado_id' => trim($_POST['grado_id'] ?? ''),
            'capacidad' => trim($_POST['capacidad'] ?? ''),
            'estado' => trim($_POST['estado'] ?? 'activo')
        ];
        
        // Validaciones
        if (empty($form_data['nombre'])) {
            $errors[] = "El nombre del grupo es requerido";
        } elseif (strlen($form_data['nombre']) < 1) {
            $errors[] = "El nombre del grupo debe tener al menos 1 carácter";
        }
        
        if (empty($form_data['grado_id'])) {
            $errors[] = "El grado es requerido";
        } elseif (!is_numeric($form_data['grado_id']) || $form_data['grado_id'] <= 0) {
            $errors[] = "El grado seleccionado no es válido";
        }
        
        if (!empty($form_data['capacidad']) && (!is_numeric($form_data['capacidad']) || $form_data['capacidad'] < 1)) {
            $errors[] = "La capacidad debe ser un número mayor a 0";
        }
        
        if (!in_array($form_data['estado'], ['activo', 'inactivo'])) {
            $errors[] = "El estado seleccionado no es válido";
        }
        
        // Verificar que el grupo no exista para el mismo grado (excluyendo el grupo actual)
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM grupos WHERE nombre = ? AND grado_id = ? AND id != ?");
                $stmt->execute([$form_data['nombre'], $form_data['grado_id'], $grupo_id]);
                if ($stmt->fetch()) {
                    $errors[] = "Ya existe un grupo con ese nombre para el grado seleccionado";
                }
                
            } catch (Exception $e) {
                $errors[] = "Error al verificar datos: " . $e->getMessage();
            }
        }
        
        // Si no hay errores, actualizar en la base de datos
        if (empty($errors)) {
            try {
                // Actualizar grupo
                $update_sql = "
                    UPDATE grupos SET 
                        nombre = ?, grado_id = ?, capacidad = ?, estado = ?
                    WHERE id = ?
                ";
                
                $stmt = $pdo->prepare($update_sql);
                $resultado = $stmt->execute([
                    $form_data['nombre'],
                    $form_data['grado_id'],
                    $form_data['capacidad'] ?: null,
                    $form_data['estado'],
                    $grupo_id
                ]);
                
                if ($resultado) {
                    $success_message = "Grupo actualizado exitosamente";
                    
                    // Redirigir a la vista del grupo con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: ver.php?id={$grupo_id}&success={$mensaje_url}");
                    exit();
                } else {
                    $errors[] = "Error al actualizar el grupo en la base de datos";
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
    
    // Obtener grados activos
    $grados_sql = "SELECT id, nombre FROM grados WHERE estado = 'activo' ORDER BY nombre";
    $grados = $pdo->query($grados_sql)->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $grados = [];
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
        .grupo-info {
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
                        <p class="text-muted mb-0">Modifica la información del grupo</p>
                    </div>
                    <div>
                        <a href="ver.php?id=<?php echo $grupo_id; ?>" class="btn btn-outline-info me-2">
                            <i class="fas fa-eye me-2"></i>Ver Detalles
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
                    </div>
                </div>

                <!-- Información del grupo -->
                <div class="grupo-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                Grupo ID: #<?php echo $grupo['id']; ?>
                            </h4>
                            <p class="mb-0">
                                <i class="fas fa-layer-group me-2"></i>
                                Grupo actual: <strong><?php echo htmlspecialchars($grupo['nombre']); ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-light text-dark fs-6">
                                <?php echo ucfirst($grupo['estado']); ?>
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
                        <form method="POST" id="formEditarGrupo" novalidate>
                            <input type="hidden" name="actualizar_grupo" value="1">
                            
                            <!-- Información del Grupo -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-layer-group me-2"></i>Información del Grupo
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control <?php echo (isset($errors) && in_array('El nombre del grupo es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="nombre" 
                                                   name="nombre" 
                                                   value="<?php echo htmlspecialchars($form_data['nombre']); ?>" 
                                                   placeholder="Nombre del grupo" 
                                                   required>
                                            <label for="nombre">Nombre del Grupo <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select <?php echo (isset($errors) && in_array('El grado es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                    id="grado_id" 
                                                    name="grado_id" 
                                                    required>
                                                <option value="">Selecciona un grado</option>
                                                <?php foreach ($grados as $grado): ?>
                                                <option value="<?php echo $grado['id']; ?>" 
                                                        <?php echo (isset($form_data['grado_id']) && $form_data['grado_id'] == $grado['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($grado['nombre']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="grado_id">Grado <span class="required">*</span></label>
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
                                <a href="ver.php?id=<?php echo $grupo_id; ?>" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-save me-2"></i>Actualizar Grupo
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
                                        <strong>Nombre:</strong> Identificador único del grupo
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Grado:</strong> Nivel educativo del grupo
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Capacidad:</strong> Número máximo de estudiantes (opcional)
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Estado:</strong> Activo o inactivo
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
                                    El nombre debe ser único para cada grado
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    La capacidad es opcional pero recomendada
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
        document.getElementById('formEditarGrupo').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            // Validar campos requeridos
            const requiredFields = ['nombre', 'grado_id'];
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



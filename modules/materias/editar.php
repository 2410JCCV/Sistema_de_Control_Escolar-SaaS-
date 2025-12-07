<?php
/**
 * Editar Materia - Formulario Completo
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

$page_title = 'Editar Materia';

// Obtener ID de la materia
$materia_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($materia_id <= 0) {
    $_SESSION['error'] = 'ID de materia no válido';
    redirect('index.php');
}

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [];

// Obtener datos de la materia
try {
    $pdo = conectarDB();
    
    $sql = "SELECT * FROM materias WHERE id = ? AND estado = 'activo'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$materia_id]);
    $materia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$materia) {
        $_SESSION['error'] = 'Materia no encontrada';
        redirect('index.php');
    }
    
    // Cargar datos en el formulario
    $form_data = [
        'nombre' => $materia['nombre'],
        'descripcion' => $materia['descripcion'],
        'creditos' => $materia['creditos'],
        'grado_id' => $materia['grado_id'],
        'estado' => $materia['estado']
    ];
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos de la materia: ' . $e->getMessage();
    redirect('index.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_materia'])) {
    try {
        // Obtener y sanitizar datos
        $form_data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'creditos' => trim($_POST['creditos'] ?? ''),
            'grado_id' => trim($_POST['grado_id'] ?? ''),
            'estado' => trim($_POST['estado'] ?? 'activo')
        ];
        
        // Validaciones
        if (empty($form_data['nombre'])) {
            $errors[] = "El nombre es requerido";
        } elseif (strlen($form_data['nombre']) < 2) {
            $errors[] = "El nombre debe tener al menos 2 caracteres";
        }
        
        if (empty($form_data['grado_id'])) {
            $errors[] = "El grado es requerido";
        } elseif (!is_numeric($form_data['grado_id']) || $form_data['grado_id'] <= 0) {
            $errors[] = "El grado seleccionado no es válido";
        }
        
        if (!empty($form_data['creditos']) && (!is_numeric($form_data['creditos']) || $form_data['creditos'] < 0 || $form_data['creditos'] > 10)) {
            $errors[] = "Los créditos deben ser un número válido entre 0 y 10";
        }
        
        // Si no hay errores, actualizar en la base de datos
        if (empty($errors)) {
            try {
                // Actualizar materia
                $update_sql = "
                    UPDATE materias SET 
                        nombre = ?, descripcion = ?, creditos = ?, 
                        grado_id = ?, estado = ?
                    WHERE id = ?
                ";
                
                $stmt = $pdo->prepare($update_sql);
                $resultado = $stmt->execute([
                    $form_data['nombre'],
                    $form_data['descripcion'] ?: null,
                    $form_data['creditos'] ?: null,
                    $form_data['grado_id'],
                    $form_data['estado'],
                    $materia_id
                ]);
                
                if ($resultado) {
                    $success_message = "Materia actualizada exitosamente";
                    
                    // Redirigir a la vista de la materia con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: ver.php?id={$materia_id}&success={$mensaje_url}");
                    exit();
                } else {
                    $errors[] = "Error al actualizar la materia en la base de datos";
                }
                
            } catch (PDOException $e) {
                $errors[] = "Error de base de datos: " . $e->getMessage();
            }
        }
        
    } catch (Exception $e) {
        $errors[] = "Error inesperado: " . $e->getMessage();
    }
}

// Obtener grados disponibles (únicos por nombre)
try {
    $pdo = conectarDB();
    $grados_sql = "SELECT DISTINCT nombre, MIN(id) as id FROM grados WHERE estado = 'activo' GROUP BY nombre ORDER BY MIN(id)";
    $grados = $pdo->query($grados_sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $grados = [];
    $errors[] = "Error al cargar los grados: " . $e->getMessage();
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
        .materia-info {
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
                        <p class="text-muted mb-0">Modifica la información de la materia</p>
                    </div>
                    <div>
                        <a href="ver.php?id=<?php echo $materia_id; ?>" class="btn btn-outline-info me-2">
                            <i class="fas fa-eye me-2"></i>Ver Detalles
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
                    </div>
                </div>

                <!-- Información de la materia -->
                <div class="materia-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <?php echo htmlspecialchars($materia['nombre']); ?>
                            </h4>
                            <p class="mb-0">
                                <i class="fas fa-id-card me-2"></i>
                                Código: <strong><?php echo htmlspecialchars($materia['codigo']); ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-light text-dark fs-6">
                                ID: #<?php echo $materia['id']; ?>
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
                        <form method="POST" id="formEditarMateria" novalidate>
                            <input type="hidden" name="actualizar_materia" value="1">
                            
                            <!-- Información Básica -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-book me-2"></i>Información Básica
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control <?php echo (isset($errors) && in_array('El nombre es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="nombre" 
                                                   name="nombre" 
                                                   value="<?php echo htmlspecialchars($form_data['nombre'] ?? ''); ?>" 
                                                   placeholder="Nombre de la materia" 
                                                   required>
                                            <label for="nombre">Nombre de la Materia <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="creditos" 
                                                   name="creditos" 
                                                   value="<?php echo htmlspecialchars($form_data['creditos'] ?? ''); ?>" 
                                                   placeholder="0" 
                                                   min="0" 
                                                   max="10">
                                            <label for="creditos">Créditos</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" 
                                              id="descripcion" 
                                              name="descripcion" 
                                              placeholder="Descripción de la materia" 
                                              style="height: 100px"><?php echo htmlspecialchars($form_data['descripcion'] ?? ''); ?></textarea>
                                    <label for="descripcion">Descripción</label>
                                </div>
                            </div>

                            <!-- Información Académica -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-graduation-cap me-2"></i>Información Académica
                                </h5>
                                
                                <div class="row">
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
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="estado" name="estado">
                                                <option value="activo" <?php echo ($form_data['estado'] ?? 'activo') == 'activo' ? 'selected' : ''; ?>>Activo</option>
                                                <option value="inactivo" <?php echo ($form_data['estado'] ?? '') == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                            </select>
                                            <label for="estado">Estado</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-between">
                                <a href="ver.php?id=<?php echo $materia_id; ?>" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-save me-2"></i>Actualizar Materia
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
                                        <strong>Código:</strong> No se puede cambiar
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Grado:</strong> Obligatorio para asignar la materia
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Créditos:</strong> Opcional (0-10)
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
                                    El grado debe ser válido y activo
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
        document.getElementById('formEditarMateria').addEventListener('submit', function(e) {
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
            
            // Validar créditos
            const creditos = document.getElementById('creditos');
            if (creditos.value && (isNaN(creditos.value) || parseFloat(creditos.value) < 0 || parseFloat(creditos.value) > 10)) {
                creditos.classList.add('is-invalid');
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

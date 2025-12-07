<?php
/**
 * Agregar Materia - Formulario Completo
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

$page_title = 'Agregar Materia';

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [
    'nombre' => '',
    'descripcion' => '',
    'creditos' => '',
    'grado_id' => '',
    'estado' => 'activo'
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_materia'])) {
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
        
        if (!empty($form_data['creditos']) && (!is_numeric($form_data['creditos']) || $form_data['creditos'] < 0)) {
            $errors[] = "Los créditos deben ser un número válido mayor o igual a 0";
        }
        
        // Si no hay errores, insertar en la base de datos
        if (empty($errors)) {
            try {
                $pdo = conectarDB();
                
                // Generar código único
                $codigo = 'MAT-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                // Verificar que el código no exista
                $codigo_check = $pdo->prepare("SELECT COUNT(*) FROM materias WHERE codigo = ?");
                $codigo_check->execute([$codigo]);
                while ($codigo_check->fetchColumn() > 0) {
                    $codigo = 'MAT-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                    $codigo_check->execute([$codigo]);
                }
                
                // Insertar materia
                $insert_sql = "
                    INSERT INTO materias (
                        codigo, nombre, descripcion, creditos, grado_id, estado, fecha_creacion
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW())
                ";
                
                $stmt = $pdo->prepare($insert_sql);
                $resultado = $stmt->execute([
                    $codigo,
                    $form_data['nombre'],
                    $form_data['descripcion'] ?: null,
                    $form_data['creditos'] ?: null,
                    $form_data['grado_id'],
                    $form_data['estado']
                ]);
                
                if ($resultado) {
                    $materia_id = $pdo->lastInsertId();
                    $success_message = "Materia agregada exitosamente. Código: {$codigo}";
                    
                    // Redirigir a la lista con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: index.php?success={$mensaje_url}");
                    exit();
                } else {
                    $errors[] = "Error al guardar la materia en la base de datos";
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
    <link href="../../assets/css/dashboard-style.css" rel="stylesheet">
    <style>
        body { font-family: 'Comic Sans MS', 'Chalkboard', 'Marker Felt', cursive; }
        .main-container {
            background: linear-gradient(135deg, rgba(224, 242, 254, 0.5) 0%, rgba(254, 243, 199, 0.5) 50%, rgba(252, 231, 243, 0.5) 100%);
            padding: 2rem;
            min-height: calc(100vh - 100px);
        }
        .page-header {
            background: linear-gradient(135deg, var(--orange) 0%, #EA580C 100%);
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
            border: 2px solid var(--orange);
        }
        .required {
            color: #dc3545;
        }
        .materia-info {
            background: linear-gradient(135deg, var(--orange) 0%, #EA580C 100%);
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
                            <i class="fas fa-plus-circle me-2 text-primary"></i>
                            <?php echo $page_title; ?>
                </h2>
                        <p class="text-muted mb-0">Registra una nueva materia en el sistema</p>
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
                        <form method="POST" id="formAgregarMateria" novalidate>
                            <input type="hidden" name="agregar_materia" value="1">
                            
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
                                <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-school btn-subjects btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Materia
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
                                        <strong>Código:</strong> Se genera automáticamente
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
                                    Usa nombres descriptivos para las materias
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    La descripción ayuda a entender el contenido
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Los créditos indican la carga académica
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
        document.getElementById('formAgregarMateria').addEventListener('submit', function(e) {
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
<?php
/**
 * Agregar Estudiante - Formulario Completo
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

$page_title = 'Agregar Estudiante';

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [];

// Obtener grados y grupos para los select
try {
    $pdo = conectarDB();
    
    // Obtener grados únicos disponibles
    $grados_sql = "SELECT DISTINCT g.id, g.nombre FROM grados g 
                   WHERE g.estado = 'activo' 
                   ORDER BY g.id";
    $grados = $pdo->query($grados_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener grupos únicos disponibles
    $grupos_sql = "SELECT DISTINCT gr.id, gr.nombre FROM grupos gr 
                   WHERE gr.estado = 'activo' 
                   ORDER BY gr.nombre";
    $grupos = $pdo->query($grupos_sql)->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $grados = [];
    $grupos = [];
    $errors[] = "Error al cargar datos: " . $e->getMessage();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_estudiante'])) {
    try {
        // Obtener y sanitizar datos
        $form_data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
            'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'grado_id' => (int)($_POST['grado_id'] ?? 0),
            'grupo_id' => (int)($_POST['grupo_id'] ?? 0),
            'estado' => trim($_POST['estado'] ?? 'activo')
        ];
        
        // Validaciones
        if (empty($form_data['nombre'])) {
            $errors[] = "El nombre es requerido";
        } elseif (strlen($form_data['nombre']) < 2) {
            $errors[] = "El nombre debe tener al menos 2 caracteres";
        }
        
        if (empty($form_data['apellido_paterno'])) {
            $errors[] = "El apellido paterno es requerido";
        } elseif (strlen($form_data['apellido_paterno']) < 2) {
            $errors[] = "El apellido paterno debe tener al menos 2 caracteres";
        }
        
        if (empty($form_data['fecha_nacimiento'])) {
            $errors[] = "La fecha de nacimiento es requerida";
        } else {
            $fecha_nacimiento = DateTime::createFromFormat('Y-m-d', $form_data['fecha_nacimiento']);
            if (!$fecha_nacimiento || $fecha_nacimiento->format('Y-m-d') !== $form_data['fecha_nacimiento']) {
                $errors[] = "La fecha de nacimiento no es válida";
            } else {
                $hoy = new DateTime();
                $edad = $hoy->diff($fecha_nacimiento)->y;
                if ($edad < 5 || $edad > 25) {
                    $errors[] = "La edad debe estar entre 5 y 25 años";
                }
            }
        }
        
        if (empty($form_data['email'])) {
            $errors[] = "El email es requerido";
        } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email no es válido";
        } else {
            // Verificar si el email ya existe
            $email_check = $pdo->prepare("SELECT COUNT(*) FROM estudiantes WHERE email = ? AND estado = 'activo'");
            $email_check->execute([$form_data['email']]);
            if ($email_check->fetchColumn() > 0) {
                $errors[] = "Ya existe un estudiante con este email";
            }
        }
        
        if ($form_data['grado_id'] <= 0) {
            $errors[] = "Debe seleccionar un grado";
        }
        
        if ($form_data['grupo_id'] <= 0) {
            $errors[] = "Debe seleccionar un grupo";
        }
        
        if (!empty($form_data['telefono']) && !preg_match('/^[\d\-\+\(\)\s]+$/', $form_data['telefono'])) {
            $errors[] = "El formato del teléfono no es válido";
        }
        
        // Si no hay errores, insertar en la base de datos
        if (empty($errors)) {
            try {
                // Generar matrícula única
                $matricula = 'EST' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                
                // Verificar que la matrícula no exista
                $matricula_check = $pdo->prepare("SELECT COUNT(*) FROM estudiantes WHERE matricula = ?");
                $matricula_check->execute([$matricula]);
                while ($matricula_check->fetchColumn() > 0) {
                    $matricula = 'EST' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                    $matricula_check->execute([$matricula]);
                }
                
                // Insertar estudiante
                $insert_sql = "
                    INSERT INTO estudiantes (
                        matricula, nombre, apellido_paterno, apellido_materno, 
                        fecha_nacimiento, email, telefono, direccion, 
                        grado_id, grupo_id, estado, fecha_creacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ";
                
                $stmt = $pdo->prepare($insert_sql);
                $resultado = $stmt->execute([
                    $matricula,
                    $form_data['nombre'],
                    $form_data['apellido_paterno'],
                    $form_data['apellido_materno'],
                    $form_data['fecha_nacimiento'],
                    $form_data['email'],
                    $form_data['telefono'],
                    $form_data['direccion'],
                    $form_data['grado_id'],
                    $form_data['grupo_id'],
                    $form_data['estado']
                ]);
                
                if ($resultado) {
                    $estudiante_id = $pdo->lastInsertId();
                    $success_message = "Estudiante agregado exitosamente. Matrícula: {$matricula}";
                    
                    // Redirigir a la lista con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: index.php?success={$mensaje_url}");
                    exit();
            } else {
                    $errors[] = "Error al guardar el estudiante en la base de datos";
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
    <link href="../../assets/css/dashboard-style.css" rel="stylesheet">
    <style>
        body { font-family: 'Comic Sans MS', 'Chalkboard', 'Marker Felt', cursive; }
        .main-container {
            background: linear-gradient(135deg, rgba(224, 242, 254, 0.5) 0%, rgba(254, 243, 199, 0.5) 50%, rgba(252, 231, 243, 0.5) 100%);
            padding: 2rem;
            min-height: calc(100vh - 100px);
        }
        .page-header {
            background: linear-gradient(135deg, var(--sky-blue) 0%, #2196F3 100%);
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
            border: 2px solid var(--sky-blue);
        }
        .required {
            color: #dc3545;
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
                            <i class="fas fa-user-plus me-2"></i>
                            <?php echo $page_title; ?>
                </h2>
                        <p class="mb-0 mt-2">Registra un nuevo estudiante en el sistema</p>
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
                        <form method="POST" id="formAgregarEstudiante" novalidate>
                            <input type="hidden" name="agregar_estudiante" value="1">
                            
                            <!-- Información Personal -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user me-2"></i>Información Personal
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control <?php echo (isset($errors) && in_array('El nombre es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="nombre" 
                                                   name="nombre" 
                                                   value="<?php echo htmlspecialchars($form_data['nombre'] ?? ''); ?>" 
                                                   placeholder="Nombre" 
                                                   required>
                                            <label for="nombre">Nombre <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control <?php echo (isset($errors) && in_array('El apellido paterno es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="apellido_paterno" 
                                                   name="apellido_paterno" 
                                                   value="<?php echo htmlspecialchars($form_data['apellido_paterno'] ?? ''); ?>" 
                                                   placeholder="Apellido Paterno" 
                                                   required>
                                            <label for="apellido_paterno">Apellido Paterno <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="apellido_materno" 
                                                   name="apellido_materno" 
                                                   value="<?php echo htmlspecialchars($form_data['apellido_materno'] ?? ''); ?>" 
                                                   placeholder="Apellido Materno">
                                            <label for="apellido_materno">Apellido Materno</label>
                                    </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="date" 
                                                   class="form-control <?php echo (isset($errors) && in_array('La fecha de nacimiento es requerida', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="fecha_nacimiento" 
                                                   name="fecha_nacimiento" 
                                                   value="<?php echo htmlspecialchars($form_data['fecha_nacimiento'] ?? ''); ?>" 
                                                   required>
                                            <label for="fecha_nacimiento">Fecha de Nacimiento <span class="required">*</span></label>
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

                            <!-- Información de Contacto -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-envelope me-2"></i>Información de Contacto
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" 
                                                   class="form-control <?php echo (isset($errors) && in_array('El email es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" 
                                                   placeholder="correo@ejemplo.com" 
                                                   required>
                                            <label for="email">Email <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" 
                                                   class="form-control" 
                                                   id="telefono" 
                                                   name="telefono" 
                                                   value="<?php echo htmlspecialchars($form_data['telefono'] ?? ''); ?>" 
                                                   placeholder="(555) 123-4567">
                                            <label for="telefono">Teléfono</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" 
                                              id="direccion" 
                                              name="direccion" 
                                              placeholder="Dirección completa" 
                                              style="height: 100px"><?php echo htmlspecialchars($form_data['direccion'] ?? ''); ?></textarea>
                                    <label for="direccion">Dirección</label>
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
                                            <select class="form-select <?php echo (isset($errors) && in_array('Debe seleccionar un grado', $errors)) ? 'is-invalid' : ''; ?>" 
                                                    id="grado_id" 
                                                    name="grado_id" 
                                                    required>
                                            <option value="">Seleccionar grado</option>
                                            <?php foreach ($grados as $grado): ?>
                                                <option value="<?php echo $grado['id']; ?>" 
                                                        <?php echo ($form_data['grado_id'] ?? 0) == $grado['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($grado['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                            <label for="grado_id">Grado <span class="required">*</span></label>
                                    </div>
                                </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select <?php echo (isset($errors) && in_array('Debe seleccionar un grupo', $errors)) ? 'is-invalid' : ''; ?>" 
                                                    id="grupo_id" 
                                                    name="grupo_id" 
                                                    required>
                                            <option value="">Seleccionar grupo</option>
                                            <?php foreach ($grupos as $grupo): ?>
                                                <option value="<?php echo $grupo['id']; ?>" 
                                                        <?php echo ($form_data['grupo_id'] ?? 0) == $grupo['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($grupo['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                            <label for="grupo_id">Grupo <span class="required">*</span></label>
                                    </div>
                                </div>
                                    </div>
                                </div>
                                
                            <!-- Botones -->
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-school btn-students btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Estudiante
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
                                        <strong>Matrícula:</strong> Se genera automáticamente
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Email:</strong> Debe ser único en el sistema
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Edad:</strong> Entre 5 y 25 años
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
                                    Completa todos los campos obligatorios
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Verifica que el email sea correcto
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Selecciona el grado y grupo correctos
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
        document.getElementById('formAgregarEstudiante').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            // Validar campos requeridos
            const requiredFields = ['nombre', 'apellido_paterno', 'fecha_nacimiento', 'email', 'grado_id', 'grupo_id'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            // Validar email
            const email = document.getElementById('email');
            if (email.value && !isValidEmail(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validar fecha de nacimiento
            const fechaNacimiento = document.getElementById('fecha_nacimiento');
            if (fechaNacimiento.value) {
                const fecha = new Date(fechaNacimiento.value);
                const hoy = new Date();
                
                // Verificar que la fecha sea válida
                if (isNaN(fecha.getTime())) {
                    fechaNacimiento.classList.add('is-invalid');
                    isValid = false;
                } else {
                    // Calcular edad correctamente
                    let edad = hoy.getFullYear() - fecha.getFullYear();
                    const mesActual = hoy.getMonth();
                    const mesNacimiento = fecha.getMonth();
                    
                    if (mesActual < mesNacimiento || (mesActual === mesNacimiento && hoy.getDate() < fecha.getDate())) {
                        edad--;
                    }
                    
                    if (edad < 5 || edad > 25) {
                        fechaNacimiento.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor corrige los errores en el formulario');
            }
        });
        
        // Función para validar email
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
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
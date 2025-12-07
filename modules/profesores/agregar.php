<?php
/**
 * Agregar Profesor - Formulario Completo
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

$page_title = 'Agregar Profesor';

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_profesor'])) {
    try {
        // Obtener y sanitizar datos
        $form_data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
            'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
            'especialidad' => trim($_POST['especialidad'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'fecha_ingreso' => trim($_POST['fecha_ingreso'] ?? ''),
            'salario' => trim($_POST['salario'] ?? ''),
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
        
        if (empty($form_data['especialidad'])) {
            $errors[] = "La especialidad es requerida";
        }
        
        if (empty($form_data['fecha_ingreso'])) {
            $errors[] = "La fecha de ingreso es requerida";
        } else {
            $fecha_ingreso = DateTime::createFromFormat('Y-m-d', $form_data['fecha_ingreso']);
            if (!$fecha_ingreso || $fecha_ingreso->format('Y-m-d') !== $form_data['fecha_ingreso']) {
                $errors[] = "La fecha de ingreso no es válida";
            }
        }
        
        if (!empty($form_data['fecha_nacimiento'])) {
            $fecha_nacimiento = DateTime::createFromFormat('Y-m-d', $form_data['fecha_nacimiento']);
            if (!$fecha_nacimiento || $fecha_nacimiento->format('Y-m-d') !== $form_data['fecha_nacimiento']) {
                $errors[] = "La fecha de nacimiento no es válida";
            } else {
                $hoy = new DateTime();
                $edad = $hoy->diff($fecha_nacimiento)->y;
                if ($edad < 22 || $edad > 70) {
                    $errors[] = "La edad debe estar entre 22 y 70 años";
                }
            }
        }
        
        if (!empty($form_data['email'])) {
            if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El email no es válido";
            } else {
                // Verificar si el email ya existe
                $pdo = conectarDB();
                $email_check = $pdo->prepare("SELECT COUNT(*) FROM profesores WHERE email = ? AND estado = 'activo'");
                $email_check->execute([$form_data['email']]);
                if ($email_check->fetchColumn() > 0) {
                    $errors[] = "Ya existe un profesor con este email";
                }
            }
        }
        
        if (!empty($form_data['salario']) && (!is_numeric($form_data['salario']) || $form_data['salario'] < 0)) {
            $errors[] = "El salario debe ser un número válido mayor o igual a 0";
        }
        
        if (!empty($form_data['telefono']) && !preg_match('/^[\d\-\+\(\)\s]+$/', $form_data['telefono'])) {
            $errors[] = "El formato del teléfono no es válido";
        }
        
        // Si no hay errores, insertar en la base de datos
        if (empty($errors)) {
            try {
                $pdo = conectarDB();
                
                // Generar código único
                $codigo = 'PROF' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
                
                // Verificar que el código no exista
                $codigo_check = $pdo->prepare("SELECT COUNT(*) FROM profesores WHERE codigo = ?");
                $codigo_check->execute([$codigo]);
                while ($codigo_check->fetchColumn() > 0) {
                    $codigo = 'PROF' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
                    $codigo_check->execute([$codigo]);
                }
                
                // Insertar profesor
                $insert_sql = "
                    INSERT INTO profesores (
                        codigo, nombre, apellido_paterno, apellido_materno, 
                        fecha_nacimiento, especialidad, telefono, email, direccion, 
                        fecha_ingreso, salario, estado, fecha_creacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ";
                
                $stmt = $pdo->prepare($insert_sql);
                $resultado = $stmt->execute([
                    $codigo,
                    $form_data['nombre'],
                    $form_data['apellido_paterno'],
                    $form_data['apellido_materno'],
                    $form_data['fecha_nacimiento'] ?: null,
                    $form_data['especialidad'],
                    $form_data['telefono'] ?: null,
                    $form_data['email'] ?: null,
                    $form_data['direccion'] ?: null,
                    $form_data['fecha_ingreso'],
                    $form_data['salario'] ?: null,
                    $form_data['estado']
                ]);
                
                if ($resultado) {
                    $profesor_id = $pdo->lastInsertId();
                    $success_message = "Profesor agregado exitosamente. Código: {$codigo}";
                    
                    // Redirigir a la lista con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: index.php?success={$mensaje_url}");
                    exit();
                } else {
                    $errors[] = "Error al guardar el profesor en la base de datos";
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
            background: linear-gradient(135deg, var(--grass-green) 0%, #059669 100%);
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
            border: 2px solid var(--grass-green);
        }
        .required {
            color: #dc3545;
        }
        .professor-info {
            background: linear-gradient(135deg, var(--grass-green) 0%, #059669 100%);
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
                                <i class="fas fa-user-plus me-2"></i>
                                <?php echo $page_title; ?>
                            </h2>
                            <p class="mb-0 mt-2">Registra un nuevo profesor en el sistema</p>
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
                        <form method="POST" id="formAgregarProfesor" novalidate>
                            <input type="hidden" name="agregar_profesor" value="1">
                            
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
                                                   class="form-control" 
                                                   id="fecha_nacimiento" 
                                                   name="fecha_nacimiento" 
                                                   value="<?php echo htmlspecialchars($form_data['fecha_nacimiento'] ?? ''); ?>" 
                                                   placeholder="Fecha de Nacimiento">
                                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
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

                            <!-- Información Profesional -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-graduation-cap me-2"></i>Información Profesional
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control <?php echo (isset($errors) && in_array('La especialidad es requerida', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="especialidad" 
                                                   name="especialidad" 
                                                   value="<?php echo htmlspecialchars($form_data['especialidad'] ?? ''); ?>" 
                                                   placeholder="Especialidad" 
                                                   required>
                                            <label for="especialidad">Especialidad <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="date" 
                                                   class="form-control <?php echo (isset($errors) && in_array('La fecha de ingreso es requerida', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="fecha_ingreso" 
                                                   name="fecha_ingreso" 
                                                   value="<?php echo htmlspecialchars($form_data['fecha_ingreso'] ?? ''); ?>" 
                                                   placeholder="Fecha de Ingreso" 
                                                   required>
                                            <label for="fecha_ingreso">Fecha de Ingreso <span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="salario" 
                                                   name="salario" 
                                                   value="<?php echo htmlspecialchars($form_data['salario'] ?? ''); ?>" 
                                                   placeholder="0.00" 
                                                   step="0.01" 
                                                   min="0">
                                            <label for="salario">Salario</label>
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
                                                   class="form-control <?php echo (isset($errors) && in_array('El email no es válido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" 
                                                   placeholder="correo@ejemplo.com">
                                            <label for="email">Email</label>
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

                            <!-- Botones -->
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-school btn-teachers btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Profesor
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
                                        <strong>Email:</strong> Debe ser único en el sistema
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Edad:</strong> Entre 22 y 70 años
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
                                    Especifica la especialidad del profesor
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
        document.getElementById('formAgregarProfesor').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            // Validar campos requeridos
            const requiredFields = ['nombre', 'apellido_paterno', 'especialidad', 'fecha_ingreso'];
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
                
                if (isNaN(fecha.getTime())) {
                    fechaNacimiento.classList.add('is-invalid');
                    isValid = false;
                } else {
                    let edad = hoy.getFullYear() - fecha.getFullYear();
                    const mesActual = hoy.getMonth();
                    const mesNacimiento = fecha.getMonth();
                    
                    if (mesActual < mesNacimiento || (mesActual === mesNacimiento && hoy.getDate() < fecha.getDate())) {
                        edad--;
                    }
                    
                    if (edad < 22 || edad > 70) {
                        fechaNacimiento.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            }
            
            // Validar salario
            const salario = document.getElementById('salario');
            if (salario.value && (isNaN(salario.value) || parseFloat(salario.value) < 0)) {
                salario.classList.add('is-invalid');
                isValid = false;
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
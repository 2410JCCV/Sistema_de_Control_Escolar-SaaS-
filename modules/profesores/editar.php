<?php
/**
 * Editar Profesor - Formulario Completo
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

$page_title = 'Editar Profesor';

// Obtener ID del profesor
$profesor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($profesor_id <= 0) {
    $_SESSION['error'] = 'ID de profesor no válido';
    redirect('index.php');
}

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [];

// Obtener datos del profesor
try {
    $pdo = conectarDB();
    
    $sql = "SELECT * FROM profesores WHERE id = ? AND estado = 'activo'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$profesor_id]);
    $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profesor) {
        $_SESSION['error'] = 'Profesor no encontrado';
        redirect('index.php');
    }
    
    // Cargar datos en el formulario
    $form_data = [
        'nombre' => $profesor['nombre'],
        'apellido_paterno' => $profesor['apellido_paterno'],
        'apellido_materno' => $profesor['apellido_materno'],
        'fecha_nacimiento' => $profesor['fecha_nacimiento'],
        'especialidad' => $profesor['especialidad'],
        'telefono' => $profesor['telefono'],
        'email' => $profesor['email'],
        'direccion' => $profesor['direccion'],
        'fecha_ingreso' => $profesor['fecha_ingreso'],
        'salario' => $profesor['salario'],
        'estado' => $profesor['estado']
    ];
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos del profesor: ' . $e->getMessage();
    redirect('index.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_profesor'])) {
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
                // Verificar si el email ya existe (excluyendo el profesor actual)
                $email_check = $pdo->prepare("SELECT COUNT(*) FROM profesores WHERE email = ? AND id != ? AND estado = 'activo'");
                $email_check->execute([$form_data['email'], $profesor_id]);
                if ($email_check->fetchColumn() > 0) {
                    $errors[] = "Ya existe otro profesor con este email";
                }
            }
        }
        
        if (!empty($form_data['salario']) && (!is_numeric($form_data['salario']) || $form_data['salario'] < 0)) {
            $errors[] = "El salario debe ser un número válido mayor o igual a 0";
        }
        
        if (!empty($form_data['telefono']) && !preg_match('/^[\d\-\+\(\)\s]+$/', $form_data['telefono'])) {
            $errors[] = "El formato del teléfono no es válido";
        }
        
        // Si no hay errores, actualizar en la base de datos
        if (empty($errors)) {
            try {
                // Actualizar profesor
                $update_sql = "
                    UPDATE profesores SET 
                        nombre = ?, apellido_paterno = ?, apellido_materno = ?, 
                        fecha_nacimiento = ?, especialidad = ?, telefono = ?, 
                        email = ?, direccion = ?, fecha_ingreso = ?, salario = ?, 
                        estado = ?, fecha_actualizacion = NOW()
                    WHERE id = ?
                ";
                
                $stmt = $pdo->prepare($update_sql);
                $resultado = $stmt->execute([
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
                    $form_data['estado'],
                    $profesor_id
                ]);
                
                if ($resultado) {
                    $success_message = "Profesor actualizado exitosamente";
                    
                    // Redirigir a la vista del profesor con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: ver.php?id={$profesor_id}&success={$mensaje_url}");
                    exit();
                } else {
                    $errors[] = "Error al actualizar el profesor en la base de datos";
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
        .professor-info {
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
                            <i class="fas fa-user-edit me-2 text-primary"></i>
                            <?php echo $page_title; ?>
                        </h2>
                        <p class="text-muted mb-0">Modifica la información del profesor</p>
                    </div>
                    <div>
                        <a href="ver.php?id=<?php echo $profesor_id; ?>" class="btn btn-outline-info me-2">
                            <i class="fas fa-eye me-2"></i>Ver Detalles
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                        </a>
                    </div>
                </div>

                <!-- Información del profesor -->
                <div class="professor-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <?php echo htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido_paterno'] . ' ' . $profesor['apellido_materno']); ?>
                            </h4>
                            <p class="mb-0">
                                <i class="fas fa-id-card me-2"></i>
                                Código: <strong><?php echo htmlspecialchars($profesor['codigo']); ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-light text-dark fs-6">
                                ID: #<?php echo $profesor['id']; ?>
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
                        <form method="POST" id="formEditarProfesor" novalidate>
                            <input type="hidden" name="actualizar_profesor" value="1">
                            
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
                                <a href="ver.php?id=<?php echo $profesor_id; ?>" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-save me-2"></i>Actualizar Profesor
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
                                    Verifica que todos los datos sean correctos
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    El email debe ser único en el sistema
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
        document.getElementById('formEditarProfesor').addEventListener('submit', function(e) {
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


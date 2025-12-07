<?php
/**
 * Agregar Usuario - Formulario Completo
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

$page_title = 'Agregar Usuario';

// Variables para el formulario
$errors = [];
$success_message = '';
$form_data = [
    'username' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => '',
    'nombre' => '',
    'apellido' => '',
    'rol' => 'estudiante',
    'estado' => 'activo'
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_usuario'])) {
    try {
        // Obtener y sanitizar datos
        $form_data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => trim($_POST['password'] ?? ''),
            'confirm_password' => trim($_POST['confirm_password'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'rol' => trim($_POST['rol'] ?? 'estudiante'),
            'estado' => trim($_POST['estado'] ?? 'activo')
        ];
        
        // Validaciones
        if (empty($form_data['username'])) {
            $errors[] = "El nombre de usuario es requerido";
        } elseif (strlen($form_data['username']) < 3) {
            $errors[] = "El nombre de usuario debe tener al menos 3 caracteres";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $form_data['username'])) {
            $errors[] = "El nombre de usuario solo puede contener letras, números y guiones bajos";
        }
        
        if (empty($form_data['email'])) {
            $errors[] = "El email es requerido";
        } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email no es válido";
        }
        
        if (empty($form_data['password'])) {
            $errors[] = "La contraseña es requerida";
        } elseif (strlen($form_data['password']) < 6) {
            $errors[] = "La contraseña debe tener al menos 6 caracteres";
        }
        
        if ($form_data['password'] !== $form_data['confirm_password']) {
            $errors[] = "Las contraseñas no coinciden";
        }
        
        if (empty($form_data['nombre'])) {
            $errors[] = "El nombre es requerido";
        }
        
        if (empty($form_data['apellido'])) {
            $errors[] = "El apellido es requerido";
        }
        
        if (!in_array($form_data['rol'], ['admin', 'profesor', 'estudiante'])) {
            $errors[] = "El rol seleccionado no es válido";
        }
        
        if (!in_array($form_data['estado'], ['activo', 'inactivo'])) {
            $errors[] = "El estado seleccionado no es válido";
        }
        
        // Verificar que el username y email no existan
        if (empty($errors)) {
            try {
                $pdo = conectarDB();
                
                // Verificar username único
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
                $stmt->execute([$form_data['username']]);
                if ($stmt->fetch()) {
                    $errors[] = "El nombre de usuario ya existe";
                }
                
                // Verificar email único
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$form_data['email']]);
                if ($stmt->fetch()) {
                    $errors[] = "El email ya está registrado";
                }
                
            } catch (Exception $e) {
                $errors[] = "Error al verificar datos: " . $e->getMessage();
            }
        }
        
        // Si no hay errores, insertar en la base de datos
        if (empty($errors)) {
            try {
                // Hash de la contraseña
                $password_hash = password_hash($form_data['password'], PASSWORD_DEFAULT);
                
                // Insertar usuario
                $insert_sql = "
                    INSERT INTO usuarios (
                        username, email, password, nombre, apellido, rol, estado, fecha_creacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ";
                
                $stmt = $pdo->prepare($insert_sql);
                $resultado = $stmt->execute([
                    $form_data['username'],
                    $form_data['email'],
                    $password_hash,
                    $form_data['nombre'],
                    $form_data['apellido'],
                    $form_data['rol'],
                    $form_data['estado']
                ]);
                
                if ($resultado) {
                    $usuario_id = $pdo->lastInsertId();
                    $success_message = "Usuario agregado exitosamente";
                    
                    // Redirigir a la lista con mensaje de éxito
                    $mensaje_url = urlencode($success_message);
                    header("Location: index.php?success={$mensaje_url}");
                    exit();
                } else {
                    $errors[] = "Error al guardar el usuario en la base de datos";
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
        .usuario-info {
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
                            <i class="fas fa-user-plus me-2 text-primary"></i>
                            <?php echo $page_title; ?>
                        </h2>
                        <p class="text-muted mb-0">Registra un nuevo usuario en el sistema</p>
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
                        <form method="POST" id="formAgregarUsuario" novalidate>
                            <input type="hidden" name="agregar_usuario" value="1">
                            
                            <!-- Información de Acceso -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-key me-2"></i>Información de Acceso
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control <?php echo (isset($errors) && in_array('El nombre de usuario es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="username" 
                                                   name="username" 
                                                   value="<?php echo htmlspecialchars($form_data['username']); ?>" 
                                                   placeholder="Nombre de usuario" 
                                                   required>
                                            <label for="username">Nombre de Usuario <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" 
                                                   class="form-control <?php echo (isset($errors) && in_array('El email es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?php echo htmlspecialchars($form_data['email']); ?>" 
                                                   placeholder="Email" 
                                                   required>
                                            <label for="email">Email <span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" 
                                                   class="form-control <?php echo (isset($errors) && in_array('La contraseña es requerida', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="Contraseña" 
                                                   required>
                                            <label for="password">Contraseña <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" 
                                                   class="form-control <?php echo (isset($errors) && in_array('Las contraseñas no coinciden', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="confirm_password" 
                                                   name="confirm_password" 
                                                   placeholder="Confirmar contraseña" 
                                                   required>
                                            <label for="confirm_password">Confirmar Contraseña <span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información Personal -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user me-2"></i>Información Personal
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control <?php echo (isset($errors) && in_array('El nombre es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="nombre" 
                                                   name="nombre" 
                                                   value="<?php echo htmlspecialchars($form_data['nombre']); ?>" 
                                                   placeholder="Nombre" 
                                                   required>
                                            <label for="nombre">Nombre <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" 
                                                   class="form-control <?php echo (isset($errors) && in_array('El apellido es requerido', $errors)) ? 'is-invalid' : ''; ?>" 
                                                   id="apellido" 
                                                   name="apellido" 
                                                   value="<?php echo htmlspecialchars($form_data['apellido']); ?>" 
                                                   placeholder="Apellido" 
                                                   required>
                                            <label for="apellido">Apellido <span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración del Usuario -->
                            <div class="form-section">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-cog me-2"></i>Configuración del Usuario
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="rol" name="rol">
                                                <option value="estudiante" <?php echo ($form_data['rol'] == 'estudiante') ? 'selected' : ''; ?>>Estudiante</option>
                                                <option value="profesor" <?php echo ($form_data['rol'] == 'profesor') ? 'selected' : ''; ?>>Profesor</option>
                                                <option value="admin" <?php echo ($form_data['rol'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                            </select>
                                            <label for="rol">Rol</label>
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
                                <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Usuario
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
                                        <strong>Usuario:</strong> Mínimo 3 caracteres, solo letras, números y _
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Email:</strong> Debe ser válido y único
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Contraseña:</strong> Mínimo 6 caracteres
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Rol:</strong> Define los permisos del usuario
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
                                    El nombre de usuario debe ser único
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    El email también debe ser único
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Los administradores tienen acceso completo
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
        document.getElementById('formAgregarUsuario').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Limpiar validaciones anteriores
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            // Validar campos requeridos
            const requiredFields = ['username', 'email', 'password', 'confirm_password', 'nombre', 'apellido'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            // Validar username
            const username = document.getElementById('username');
            if (username.value && (username.value.length < 3 || !/^[a-zA-Z0-9_]+$/.test(username.value))) {
                username.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validar email
            const email = document.getElementById('email');
            if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validar contraseña
            const password = document.getElementById('password');
            if (password.value && password.value.length < 6) {
                password.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validar confirmación de contraseña
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value && password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
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
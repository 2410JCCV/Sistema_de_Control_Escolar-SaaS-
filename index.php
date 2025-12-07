<?php
/**
 * Sistema de Control Escolar
 * Página principal
 */

require_once 'config/config.php';

// Si el usuario ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Procesar login
if ($_POST && isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "Por favor, complete todos los campos";
    } else {
        // Aquí iría la lógica de autenticación
        // Por ahora, redirigir al dashboard
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = 'admin';
        redirect('dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Estilos personalizados para el login escolar */
        :root {
            --school-blue: #1e3a8a;
            --school-light-blue: #3b82f6;
            --school-yellow: #fbbf24;
            --school-green: #10b981;
        }
        
        body {
            overflow: hidden;
        }
        
        .login-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: -1;
        }
        
        .school-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        .floating-icon {
            position: absolute;
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }
        
        .floating-icon-1 {
            top: 10%;
            left: 10%;
            font-size: 80px;
            animation-delay: 0s;
        }
        
        .floating-icon-2 {
            top: 30%;
            right: 15%;
            font-size: 60px;
            animation-delay: 3s;
        }
        
        .floating-icon-3 {
            bottom: 20%;
            left: 20%;
            font-size: 100px;
            animation-delay: 6s;
        }
        
        .floating-icon-4 {
            top: 50%;
            right: 10%;
            font-size: 70px;
            animation-delay: 9s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-30px) rotate(10deg);
            }
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem;
            max-width: 450px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideInUp 0.6s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .school-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .school-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--school-blue) 0%, var(--school-light-blue) 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .school-icon i {
            font-size: 50px;
            color: white;
        }
        
        .school-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--school-blue);
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .school-subtitle {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .input-group-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-group-school {
            position: relative;
        }
        
        .input-group-school input {
            padding: 1rem 1rem 1rem 3.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        
        .input-group-school input:focus {
            border-color: var(--school-light-blue);
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        .input-group-school .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--school-light-blue);
            font-size: 1.2rem;
            z-index: 10;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--school-blue) 0%, var(--school-light-blue) 100%);
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            margin-top: 0.5rem;
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(30, 58, 138, 0.4);
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
        }
        
        .alert-school {
            border-radius: 12px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .credentials-hint {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid var(--school-yellow);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        
        .credentials-hint .text-muted {
            color: #92400e;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .school-features {
            display: flex;
            justify-content: space-around;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .feature-item {
            text-align: center;
            flex: 1;
        }
        
        .feature-icon {
            font-size: 1.5rem;
            color: var(--school-light-blue);
            margin-bottom: 0.5rem;
        }
        
        .feature-text {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .login-card {
                padding: 2rem;
                border-radius: 20px;
            }
            
            .school-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Elementos decorativos de fondo -->
    <div class="login-background">
        <div class="school-elements">
            <i class="fas fa-book-open floating-icon floating-icon-1"></i>
            <i class="fas fa-chalkboard-teacher floating-icon floating-icon-2"></i>
            <i class="fas fa-graduation-cap floating-icon floating-icon-3"></i>
            <i class="fas fa-pencil-alt floating-icon floating-icon-4"></i>
        </div>
    </div>
    
    <!-- Contenedor principal -->
    <div class="login-container">
        <div class="login-card">
            <!-- Logo y título -->
            <div class="school-logo">
                <div class="school-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h2 class="school-title"><?php echo SITE_NAME; ?></h2>
                <p class="school-subtitle">Sistema de Gestión Escolar</p>
            </div>
            
            <!-- Mensajes de error -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-school">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de login -->
            <form method="POST">
                <div class="input-group-wrapper">
                    <label for="username" class="form-label fw-bold text-dark mb-2">
                        <i class="fas fa-user me-2 text-primary"></i>Usuario
                    </label>
                    <div class="input-group-school">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Ingresa tu usuario" required autofocus>
                        <span class="input-icon">
                            <i class="fas fa-user-circle"></i>
                        </span>
                    </div>
                </div>
                
                <div class="input-group-wrapper">
                    <label for="password" class="form-label fw-bold text-dark mb-2">
                        <i class="fas fa-lock me-2 text-primary"></i>Contraseña
                    </label>
                    <div class="input-group-school">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Ingresa tu contraseña" required>
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                </button>
            </form>
            
            <!-- Credenciales de acceso -->
            <div class="credentials-hint">
                <p class="mb-1 text-center"><i class="fas fa-info-circle me-2"></i><strong>Credenciales de Acceso</strong></p>
                <p class="text-muted text-center mb-0">
                    <strong>Usuario:</strong> admin | <strong>Contraseña:</strong> admin123
                </p>
            </div>
            
            <!-- Características del sistema -->
            <div class="school-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="feature-text">Estudiantes</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <div class="feature-text">Profesores</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="feature-text">Materias</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="feature-text">Reportes</div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


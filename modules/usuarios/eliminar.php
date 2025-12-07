<?php
/**
 * Eliminar Usuario
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

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Método no permitido';
    redirect('index.php');
}

// Obtener ID del usuario
$usuario_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($usuario_id <= 0) {
    $_SESSION['error'] = 'ID de usuario no válido';
    redirect('index.php');
}

try {
    $pdo = conectarDB();
    
    // Verificar que el usuario existe
    $sql = "SELECT username FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $_SESSION['error'] = 'Usuario no encontrado';
        redirect('index.php');
    }
    
    // Verificar que no se esté eliminando el último administrador
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'admin' AND estado = 'activo'");
    $total_admins = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario_rol = $stmt->fetch()['rol'];
    
    if ($usuario_rol === 'admin' && $total_admins <= 1) {
        $_SESSION['error'] = 'No se puede eliminar el último administrador del sistema';
        redirect('index.php');
    }
    
    // Realizar eliminación física (eliminar de la base de datos)
    $delete_sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    $resultado = $stmt->execute([$usuario_id]);
    
    if ($resultado) {
        $_SESSION['success'] = "Usuario '{$usuario['username']}' eliminado exitosamente";
    } else {
        $_SESSION['error'] = 'Error al eliminar el usuario';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error inesperado: ' . $e->getMessage();
}

// Redirigir a la lista
redirect('index.php');
?>



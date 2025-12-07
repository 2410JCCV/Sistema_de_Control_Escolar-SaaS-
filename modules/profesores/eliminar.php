<?php
/**
 * Eliminar Profesor
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

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Método no permitido';
    redirect('index.php');
}

// Obtener ID del profesor
$profesor_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($profesor_id <= 0) {
    $_SESSION['error'] = 'ID de profesor no válido';
    redirect('index.php');
}

try {
    $pdo = conectarDB();
    
    // Verificar que el profesor existe
    $sql = "SELECT id, nombre, apellido_paterno, codigo FROM profesores WHERE id = ? AND estado = 'activo'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$profesor_id]);
    $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profesor) {
        $_SESSION['error'] = 'Profesor no encontrado';
        redirect('index.php');
    }
    
    // Realizar eliminación física (eliminar de la base de datos)
    $delete_sql = "DELETE FROM profesores WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    $resultado = $stmt->execute([$profesor_id]);
    
    if ($resultado) {
        $nombre_completo = $profesor['nombre'] . ' ' . $profesor['apellido_paterno'];
        $_SESSION['success'] = "Profesor '{$nombre_completo}' eliminado exitosamente";
    } else {
        $_SESSION['error'] = 'Error al eliminar el profesor';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error inesperado: ' . $e->getMessage();
}

// Redirigir a la lista
redirect('index.php');
?>


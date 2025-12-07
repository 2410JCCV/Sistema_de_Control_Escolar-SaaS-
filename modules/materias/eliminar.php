<?php
/**
 * Eliminar Materia
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

// Obtener ID de la materia
$materia_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($materia_id <= 0) {
    $_SESSION['error'] = 'ID de materia no válido';
    redirect('index.php');
}

try {
    $pdo = conectarDB();
    
    // Verificar que la materia existe
    $sql = "SELECT id, nombre, codigo FROM materias WHERE id = ? AND estado = 'activo'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$materia_id]);
    $materia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$materia) {
        $_SESSION['error'] = 'Materia no encontrada';
        redirect('index.php');
    }
    
    // Realizar eliminación física (eliminar de la base de datos)
    $delete_sql = "DELETE FROM materias WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    $resultado = $stmt->execute([$materia_id]);
    
    if ($resultado) {
        $nombre_materia = $materia['nombre'];
        $_SESSION['success'] = "Materia '{$nombre_materia}' eliminada exitosamente";
    } else {
        $_SESSION['error'] = 'Error al eliminar la materia';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error inesperado: ' . $e->getMessage();
}

// Redirigir a la lista
redirect('index.php');
?>


<?php
/**
 * Eliminar Horario
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

// Obtener ID del horario
$horario_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($horario_id <= 0) {
    $_SESSION['error'] = 'ID de horario no válido';
    redirect('index.php');
}

try {
    $pdo = conectarDB();
    
    // Verificar que el horario existe
    $sql = "SELECT h.*, m.nombre as materia_nombre 
            FROM horarios h 
            LEFT JOIN materias m ON h.materia_id = m.id 
            WHERE h.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$horario_id]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$horario) {
        $_SESSION['error'] = 'Horario no encontrado';
        redirect('index.php');
    }
    
    // Realizar eliminación física (eliminar de la base de datos)
    $delete_sql = "DELETE FROM horarios WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    $resultado = $stmt->execute([$horario_id]);
    
    if ($resultado) {
        $_SESSION['success'] = "Horario de '{$horario['materia_nombre']}' eliminado exitosamente";
    } else {
        $_SESSION['error'] = 'Error al eliminar el horario';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error inesperado: ' . $e->getMessage();
}

// Redirigir a la lista
redirect('index.php');
?>



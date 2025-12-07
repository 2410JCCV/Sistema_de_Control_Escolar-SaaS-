<?php
/**
 * Eliminar Aula
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

// Obtener ID del aula
$aula_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($aula_id <= 0) {
    $_SESSION['error'] = 'ID de aula no válido';
    redirect('index.php');
}

try {
    $pdo = conectarDB();
    
    // Verificar que el aula existe
    $sql = "SELECT nombre FROM aulas WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$aula_id]);
    $aula = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$aula) {
        $_SESSION['error'] = 'Aula no encontrada';
        redirect('index.php');
    }
    
    // Realizar eliminación física (eliminar de la base de datos)
    $delete_sql = "DELETE FROM aulas WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    $resultado = $stmt->execute([$aula_id]);
    
    if ($resultado) {
        $_SESSION['success'] = "Aula '{$aula['nombre']}' eliminada exitosamente";
    } else {
        $_SESSION['error'] = 'Error al eliminar el aula';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error inesperado: ' . $e->getMessage();
}

// Redirigir a la lista
redirect('index.php');
?>



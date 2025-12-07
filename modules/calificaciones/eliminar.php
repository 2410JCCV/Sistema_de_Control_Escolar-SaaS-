<?php
/**
 * Eliminar Calificación
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

// Obtener ID de la calificación
$calificacion_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($calificacion_id <= 0) {
    $_SESSION['error'] = 'ID de calificación no válido';
    redirect('index.php');
}

try {
    $pdo = conectarDB();
    
    // Verificar que la calificación existe
    $sql = "SELECT c.id, e.nombre as estudiante_nombre, e.apellido_paterno as estudiante_apellido 
            FROM calificaciones c
            LEFT JOIN estudiantes e ON c.estudiante_id = e.id
            WHERE c.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$calificacion_id]);
    $calificacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$calificacion) {
        $_SESSION['error'] = 'Calificación no encontrada';
        redirect('index.php');
    }
    
    // Realizar eliminación física (eliminar de la base de datos)
    $delete_sql = "DELETE FROM calificaciones WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    $resultado = $stmt->execute([$calificacion_id]);
    
    if ($resultado) {
        $estudiante_nombre = $calificacion['estudiante_nombre'] . ' ' . $calificacion['estudiante_apellido'];
        $_SESSION['success'] = "Calificación de '{$estudiante_nombre}' eliminada exitosamente";
    } else {
        $_SESSION['error'] = 'Error al eliminar la calificación';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error inesperado: ' . $e->getMessage();
}

// Redirigir a la lista
redirect('index.php');
?>



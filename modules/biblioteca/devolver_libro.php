<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    try {
        $pdo = conectarDB();
        
        // Obtener información del préstamo
        $stmt = $pdo->prepare("SELECT libro_id FROM prestamos_libros WHERE id = ?");
        $stmt->execute([$id]);
        $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($prestamo) {
            // Marcar como devuelto
            $update = $pdo->prepare("UPDATE prestamos_libros SET estado = 'devuelto', fecha_devolucion_real = CURDATE() WHERE id = ?");
            $update->execute([$id]);
            
            // Actualizar cantidad disponible
            $update_libro = $pdo->prepare("UPDATE libros SET cantidad_disponible = cantidad_disponible + 1, estado = 'disponible' WHERE id = ?");
            $update_libro->execute([$prestamo['libro_id']]);
            
            header('Location: prestamos.php?success=' . urlencode('Libro devuelto exitosamente'));
            exit();
        }
    } catch (Exception $e) {
        header('Location: prestamos.php?error=' . urlencode('Error al devolver libro: ' . $e->getMessage()));
        exit();
    }
}

redirect('prestamos.php');




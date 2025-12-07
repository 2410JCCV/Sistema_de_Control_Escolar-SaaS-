<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['libro_id'])) {
    $libro_id = (int)$_POST['libro_id'];
    $estudiante_id = !empty($_POST['estudiante_id']) ? (int)$_POST['estudiante_id'] : null;
    $profesor_id = !empty($_POST['profesor_id']) ? (int)$_POST['profesor_id'] : null;
    $fecha_devolucion_esperada = trim($_POST['fecha_devolucion_esperada'] ?? '');
    
    if ($libro_id <= 0) {
        header('Location: prestamos.php?error=' . urlencode('Debe seleccionar un libro'));
        exit();
    }
    
    if (empty($estudiante_id) && empty($profesor_id)) {
        header('Location: prestamos.php?error=' . urlencode('Debe seleccionar un estudiante o profesor'));
        exit();
    }
    
    if (empty($fecha_devolucion_esperada)) {
        header('Location: prestamos.php?error=' . urlencode('Debe especificar la fecha de devolución'));
        exit();
    }
    
    try {
        $pdo = conectarDB();
        
        // Verificar disponibilidad
        $check = $pdo->prepare("SELECT cantidad_disponible FROM libros WHERE id = ?");
        $check->execute([$libro_id]);
        $disponible = $check->fetchColumn();
        
        if ($disponible <= 0) {
            header('Location: prestamos.php?error=' . urlencode('No hay ejemplares disponibles de este libro'));
            exit();
        }
        
        // Crear préstamo
        $sql = "INSERT INTO prestamos_libros (libro_id, estudiante_id, profesor_id, fecha_prestamo, fecha_devolucion_esperada, estado, fecha_creacion) 
                VALUES (?, ?, ?, CURDATE(), ?, 'prestado', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$libro_id, $estudiante_id, $profesor_id, $fecha_devolucion_esperada]);
        
        // Actualizar cantidad disponible
        $update = $pdo->prepare("UPDATE libros SET cantidad_disponible = cantidad_disponible - 1, estado = CASE WHEN cantidad_disponible - 1 <= 0 THEN 'prestado' ELSE estado END WHERE id = ?");
        $update->execute([$libro_id]);
        
        header('Location: prestamos.php?success=' . urlencode('Préstamo realizado exitosamente'));
        exit();
    } catch (Exception $e) {
        header('Location: prestamos.php?error=' . urlencode('Error al realizar préstamo: ' . $e->getMessage()));
        exit();
    }
}

redirect('prestamos.php');




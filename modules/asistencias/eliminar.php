<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || (!hasPermission('admin') && !hasPermission('profesor'))) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("DELETE FROM asistencias WHERE id = ?");
        $stmt->execute([$id]);
        
        $mensaje = urlencode("Asistencia eliminada exitosamente");
        header("Location: listar.php?success={$mensaje}");
        exit();
    } catch (Exception $e) {
        $mensaje = urlencode("Error al eliminar: " . $e->getMessage());
        header("Location: listar.php?error={$mensaje}");
        exit();
    }
}

redirect('listar.php');




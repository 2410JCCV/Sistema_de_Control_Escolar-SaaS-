<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("DELETE FROM notificaciones WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $user_id]);
        
        header('Location: listar.php?success=' . urlencode('Notificación eliminada'));
        exit();
    } catch (Exception $e) {
        header('Location: listar.php?error=' . urlencode('Error al eliminar notificación'));
        exit();
    }
}

redirect('listar.php');




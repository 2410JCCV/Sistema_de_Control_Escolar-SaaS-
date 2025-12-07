<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE usuario_id = ?");
        $stmt->execute([$user_id]);
        
        header('Location: listar.php?success=' . urlencode('Todas las notificaciones marcadas como leídas'));
        exit();
    } catch (Exception $e) {
        header('Location: listar.php?error=' . urlencode('Error al actualizar notificaciones'));
        exit();
    }
}

redirect('listar.php');




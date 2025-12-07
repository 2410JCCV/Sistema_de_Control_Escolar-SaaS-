<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("DELETE FROM inventario WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: listar.php?success=' . urlencode('Recurso eliminado exitosamente'));
        exit();
    } catch (Exception $e) {
        header('Location: listar.php?error=' . urlencode('Error al eliminar: ' . $e->getMessage()));
        exit();
    }
}

redirect('listar.php');




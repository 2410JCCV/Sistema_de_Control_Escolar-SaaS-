<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Editar Recurso';
$errors = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) redirect('listar.php');

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT * FROM inventario WHERE id = ?");
    $stmt->execute([$id]);
    $recurso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recurso) redirect('listar.php');
    
    $form_data = $recurso;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_recurso'])) {
        $form_data = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'cantidad_total' => !empty($_POST['cantidad_total']) ? (int)$_POST['cantidad_total'] : 1,
            'precio_unitario' => !empty($_POST['precio_unitario']) ? (float)$_POST['precio_unitario'] : 0.00,
            'proveedor' => trim($_POST['proveedor'] ?? ''),
            'ubicacion' => trim($_POST['ubicacion'] ?? ''),
            'fecha_adquisicion' => trim($_POST['fecha_adquisicion'] ?? ''),
            'estado' => trim($_POST['estado'] ?? 'disponible')
        ];
        
        if (empty($form_data['codigo'])) $errors[] = "El código es requerido";
        if (empty($form_data['nombre'])) $errors[] = "El nombre es requerido";
        
        if (empty($errors)) {
            // Verificar código único
            $check = $pdo->prepare("SELECT COUNT(*) FROM inventario WHERE codigo = ? AND id != ?");
            $check->execute([$form_data['codigo'], $id]);
            if ($check->fetchColumn() > 0) {
                $errors[] = "El código ya existe";
            } else {
                $sql = "UPDATE inventario SET codigo = ?, nombre = ?, descripcion = ?, categoria = ?, cantidad_total = ?, precio_unitario = ?, proveedor = ?, ubicacion = ?, fecha_adquisicion = ?, estado = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([
                    $form_data['codigo'], $form_data['nombre'], $form_data['descripcion'], 
                    $form_data['categoria'], $form_data['cantidad_total'], $form_data['precio_unitario'],
                    $form_data['proveedor'], $form_data['ubicacion'], $form_data['fecha_adquisicion'], 
                    $form_data['estado'], $id
                ])) {
                    header('Location: listar.php?success=' . urlencode('Recurso actualizado exitosamente'));
                    exit();
                }
            }
        }
    }
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}

include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/agregar.php';




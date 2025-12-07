<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Editar Pago';
$errors = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) redirect('listar.php');

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT * FROM pagos WHERE id = ?");
    $stmt->execute([$id]);
    $pago = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pago) redirect('listar.php');
    
    $form_data = $pago;
    
    $estudiantes = $pdo->query("SELECT id, CONCAT(codigo, ' - ', nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo FROM estudiantes WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_pago'])) {
        $form_data = [
            'estudiante_id' => !empty($_POST['estudiante_id']) ? (int)$_POST['estudiante_id'] : null,
            'tipo' => trim($_POST['tipo'] ?? ''),
            'monto' => !empty($_POST['monto']) ? (float)$_POST['monto'] : 0.00,
            'fecha_pago' => trim($_POST['fecha_pago'] ?? ''),
            'fecha_vencimiento' => trim($_POST['fecha_vencimiento'] ?? ''),
            'metodo_pago' => trim($_POST['metodo_pago'] ?? ''),
            'numero_referencia' => trim($_POST['numero_referencia'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado' => trim($_POST['estado'] ?? 'pendiente')
        ];
        
        if (empty($form_data['estudiante_id'])) $errors[] = "Debe seleccionar un estudiante";
        if (empty($form_data['tipo'])) $errors[] = "El tipo es requerido";
        if ($form_data['monto'] <= 0) $errors[] = "El monto debe ser mayor a 0";
        
        if (empty($errors)) {
            $sql = "UPDATE pagos SET estudiante_id = ?, tipo = ?, monto = ?, fecha_pago = ?, fecha_vencimiento = ?, metodo_pago = ?, numero_referencia = ?, descripcion = ?, estado = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([
                $form_data['estudiante_id'], $form_data['tipo'], $form_data['monto'],
                $form_data['fecha_pago'] ? $form_data['fecha_pago'] : null,
                $form_data['fecha_vencimiento'], $form_data['metodo_pago'],
                $form_data['numero_referencia'], $form_data['descripcion'], $form_data['estado'], $id
            ])) {
                header('Location: listar.php?success=' . urlencode('Pago actualizado exitosamente'));
                exit();
            }
        }
    }
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}

include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/agregar.php';




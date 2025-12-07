<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || (!hasPermission('admin') && !hasPermission('profesor'))) {
    redirect('index.php');
}

$page_title = 'Editar Asistencia';
$errors = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('listar.php');
}

try {
    $pdo = conectarDB();
    
    // Obtener asistencia
    $stmt = $pdo->prepare("SELECT * FROM asistencias WHERE id = ?");
    $stmt->execute([$id]);
    $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$asistencia) {
        redirect('listar.php');
    }
    
    $form_data = $asistencia;
    
    // Obtener datos para selects
    $estudiantes = $pdo->query("SELECT id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo FROM estudiantes WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $materias = $pdo->query("SELECT id, nombre FROM materias WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $profesores = $pdo->query("SELECT id, CONCAT(nombre, ' ', apellido_paterno) as nombre_completo FROM profesores WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_asistencia'])) {
        $form_data = [
            'estudiante_id' => (int)($_POST['estudiante_id'] ?? 0),
            'materia_id' => (int)($_POST['materia_id'] ?? 0),
            'profesor_id' => (int)($_POST['profesor_id'] ?? 0),
            'fecha' => trim($_POST['fecha'] ?? ''),
            'estado' => trim($_POST['estado'] ?? 'presente'),
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];
        
        if ($form_data['estudiante_id'] <= 0) $errors[] = "Debe seleccionar un estudiante";
        if ($form_data['materia_id'] <= 0) $errors[] = "Debe seleccionar una materia";
        if ($form_data['profesor_id'] <= 0) $errors[] = "Debe seleccionar un profesor";
        if (empty($form_data['fecha'])) $errors[] = "La fecha es requerida";
        
        if (empty($errors)) {
            $update_sql = "UPDATE asistencias SET estudiante_id = ?, materia_id = ?, profesor_id = ?, fecha = ?, estado = ?, observaciones = ? WHERE id = ?";
            $stmt = $pdo->prepare($update_sql);
            if ($stmt->execute([$form_data['estudiante_id'], $form_data['materia_id'], $form_data['profesor_id'], $form_data['fecha'], $form_data['estado'], $form_data['observaciones'], $id])) {
                $mensaje = urlencode("Asistencia actualizada exitosamente");
                header("Location: listar.php?success={$mensaje}");
                exit();
            }
        }
    }
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}

include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/agregar.php'; // Reutilizar formulario




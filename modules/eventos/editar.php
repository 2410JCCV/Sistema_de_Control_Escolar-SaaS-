<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Editar Evento';
$errors = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) redirect('listar.php');

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT * FROM eventos WHERE id = ?");
    $stmt->execute([$id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) redirect('listar.php');
    
    $form_data = $evento;
    
    $profesores = $pdo->query("SELECT id, CONCAT(nombre, ' ', apellido_paterno) as nombre_completo FROM profesores WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $grupos = $pdo->query("SELECT id, nombre FROM grupos WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $grados = $pdo->query("SELECT id, nombre FROM grados WHERE estado = 'activo' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_evento'])) {
        $form_data = [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'tipo' => trim($_POST['tipo'] ?? 'academico'),
            'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
            'fecha_fin' => trim($_POST['fecha_fin'] ?? ''),
            'ubicacion' => trim($_POST['ubicacion'] ?? ''),
            'organizador_id' => !empty($_POST['organizador_id']) ? (int)$_POST['organizador_id'] : null,
            'grupo_id' => !empty($_POST['grupo_id']) ? (int)$_POST['grupo_id'] : null,
            'grado_id' => !empty($_POST['grado_id']) ? (int)$_POST['grado_id'] : null,
            'participantes_max' => !empty($_POST['participantes_max']) ? (int)$_POST['participantes_max'] : null,
            'costo' => !empty($_POST['costo']) ? (float)$_POST['costo'] : 0.00,
            'estado' => trim($_POST['estado'] ?? 'programado')
        ];
        
        if (empty($form_data['titulo'])) $errors[] = "El título es requerido";
        if (empty($form_data['fecha_inicio'])) $errors[] = "La fecha de inicio es requerida";
        
        if (empty($errors)) {
            $sql = "UPDATE eventos SET titulo = ?, descripcion = ?, tipo = ?, fecha_inicio = ?, fecha_fin = ?, ubicacion = ?, organizador_id = ?, grupo_id = ?, grado_id = ?, participantes_max = ?, costo = ?, estado = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([
                $form_data['titulo'], $form_data['descripcion'], $form_data['tipo'], 
                $form_data['fecha_inicio'], $form_data['fecha_fin'], $form_data['ubicacion'],
                $form_data['organizador_id'], $form_data['grupo_id'], $form_data['grado_id'],
                $form_data['participantes_max'], $form_data['costo'], $form_data['estado'], $id
            ])) {
                header('Location: listar.php?success=' . urlencode('Evento actualizado exitosamente'));
                exit();
            }
        }
    }
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}

include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/agregar.php';




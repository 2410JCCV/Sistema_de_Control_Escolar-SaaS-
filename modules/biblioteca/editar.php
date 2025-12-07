<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Editar Libro';
$errors = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) redirect('listar.php');

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT * FROM libros WHERE id = ?");
    $stmt->execute([$id]);
    $libro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$libro) redirect('listar.php');
    
    $form_data = $libro;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_libro'])) {
        $form_data = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'titulo' => trim($_POST['titulo'] ?? ''),
            'autor' => trim($_POST['autor'] ?? ''),
            'editorial' => trim($_POST['editorial'] ?? ''),
            'isbn' => trim($_POST['isbn'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'año_publicacion' => !empty($_POST['año_publicacion']) ? (int)$_POST['año_publicacion'] : null,
            'cantidad_total' => !empty($_POST['cantidad_total']) ? (int)$_POST['cantidad_total'] : 1,
            'ubicacion' => trim($_POST['ubicacion'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado' => trim($_POST['estado'] ?? 'disponible')
        ];
        
        if (empty($form_data['codigo'])) $errors[] = "El código es requerido";
        if (empty($form_data['titulo'])) $errors[] = "El título es requerido";
        if (empty($form_data['autor'])) $errors[] = "El autor es requerido";
        
        if (empty($errors)) {
            // Verificar código único
            $check = $pdo->prepare("SELECT COUNT(*) FROM libros WHERE codigo = ? AND id != ?");
            $check->execute([$form_data['codigo'], $id]);
            if ($check->fetchColumn() > 0) {
                $errors[] = "El código ya existe";
            } else {
                $sql = "UPDATE libros SET codigo = ?, titulo = ?, autor = ?, editorial = ?, isbn = ?, categoria = ?, año_publicacion = ?, cantidad_total = ?, ubicacion = ?, descripcion = ?, estado = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([
                    $form_data['codigo'], $form_data['titulo'], $form_data['autor'], 
                    $form_data['editorial'], $form_data['isbn'], $form_data['categoria'],
                    $form_data['año_publicacion'], $form_data['cantidad_total'],
                    $form_data['ubicacion'], $form_data['descripcion'], $form_data['estado'], $id
                ])) {
                    header('Location: listar.php?success=' . urlencode('Libro actualizado exitosamente'));
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




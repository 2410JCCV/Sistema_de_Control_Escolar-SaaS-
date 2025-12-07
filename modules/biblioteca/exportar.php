<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=libros_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['ID', 'Código', 'Título', 'Autor', 'Editorial', 'ISBN', 'Categoría', 'Cantidad Total', 'Cantidad Disponible', 'Estado']);

try {
    $pdo = conectarDB();
    $sql = "SELECT id, codigo, titulo, autor, editorial, isbn, categoria, cantidad_total, cantidad_disponible, estado FROM libros ORDER BY titulo";
    $stmt = $pdo->query($sql);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} catch (Exception $e) {
    fputcsv($output, ['Error', $e->getMessage()]);
}

fclose($output);
exit();




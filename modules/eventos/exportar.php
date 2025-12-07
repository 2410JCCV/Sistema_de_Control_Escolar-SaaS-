<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=eventos_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['ID', 'Título', 'Tipo', 'Fecha Inicio', 'Fecha Fin', 'Ubicación', 'Estado']);

try {
    $pdo = conectarDB();
    $sql = "SELECT id, titulo, tipo, fecha_inicio, fecha_fin, ubicacion, estado FROM eventos ORDER BY fecha_inicio DESC";
    $stmt = $pdo->query($sql);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} catch (Exception $e) {
    fputcsv($output, ['Error', $e->getMessage()]);
}

fclose($output);
exit();




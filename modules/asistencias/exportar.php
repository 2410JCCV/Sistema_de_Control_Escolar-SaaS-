<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=asistencias_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

fputcsv($output, ['ID', 'Estudiante', 'Materia', 'Profesor', 'Fecha', 'Estado', 'Observaciones']);

try {
    $pdo = conectarDB();
    $sql = "SELECT a.id, 
            CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as estudiante,
            m.nombre as materia,
            CONCAT(p.nombre, ' ', p.apellido_paterno) as profesor,
            a.fecha, a.estado, a.observaciones
            FROM asistencias a
            LEFT JOIN estudiantes e ON a.estudiante_id = e.id
            LEFT JOIN materias m ON a.materia_id = m.id
            LEFT JOIN profesores p ON a.profesor_id = p.id
            ORDER BY a.fecha DESC";
    $stmt = $pdo->query($sql);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} catch (Exception $e) {
    fputcsv($output, ['Error', $e->getMessage()]);
}

fclose($output);
exit();




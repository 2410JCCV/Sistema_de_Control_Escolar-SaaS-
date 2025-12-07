<?php
/**
 * Exportar Calificaciones
 * Sistema de Control Escolar
 */

// Configurar codificación UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn() || (!hasPermission('admin') && !hasPermission('profesor'))) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

// Obtener parámetros de filtrado
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$estudiante_id = isset($_GET['estudiante_id']) ? (int)$_GET['estudiante_id'] : 0;
$materia_id = isset($_GET['materia_id']) ? (int)$_GET['materia_id'] : 0;
$tipo_evaluacion = isset($_GET['tipo_evaluacion']) ? trim($_GET['tipo_evaluacion']) : '';

try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = [];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(e.nombre LIKE :search OR e.apellido_paterno LIKE :search OR m.nombre LIKE :search OR p.nombre LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por estudiante
    if ($estudiante_id > 0) {
        $where_conditions[] = "c.estudiante_id = :estudiante_id";
        $params['estudiante_id'] = $estudiante_id;
    }
    
    // Filtro por materia
    if ($materia_id > 0) {
        $where_conditions[] = "c.materia_id = :materia_id";
        $params['materia_id'] = $materia_id;
    }
    
    // Filtro por tipo de evaluación
    if (!empty($tipo_evaluacion)) {
        $where_conditions[] = "c.tipo_evaluacion = :tipo_evaluacion";
        $params['tipo_evaluacion'] = $tipo_evaluacion;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Consulta para obtener todas las calificaciones
    $sql = "
        SELECT 
            c.id,
            c.estudiante_id,
            c.materia_id,
            c.profesor_id,
            c.tipo_evaluacion,
            c.calificacion,
            c.fecha_evaluacion,
            c.observaciones,
            c.fecha_creacion,
            e.nombre as estudiante_nombre,
            e.apellido_paterno as estudiante_apellido,
            e.apellido_materno as estudiante_apellido_materno,
            m.nombre as materia_nombre,
            p.nombre as profesor_nombre,
            p.apellido_paterno as profesor_apellido,
            p.apellido_materno as profesor_apellido_materno
        FROM calificaciones c
        LEFT JOIN estudiantes e ON c.estudiante_id = e.id
        LEFT JOIN materias m ON c.materia_id = m.id
        LEFT JOIN profesores p ON c.profesor_id = p.id
        {$where_clause}
        ORDER BY c.fecha_evaluacion DESC, c.id DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para descarga
    $filename = 'calificaciones_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    // Crear archivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados
    fputcsv($output, [
        'ID',
        'Estudiante',
        'Materia',
        'Profesor',
        'Tipo de Evaluación',
        'Calificación',
        'Fecha de Evaluación',
        'Observaciones',
        'Fecha de Registro'
    ], ';');
    
    // Datos
    foreach ($calificaciones as $calificacion) {
        fputcsv($output, [
            $calificacion['id'],
            $calificacion['estudiante_nombre'] . ' ' . $calificacion['estudiante_apellido'] . ' ' . $calificacion['estudiante_apellido_materno'],
            $calificacion['materia_nombre'],
            $calificacion['profesor_nombre'] . ' ' . $calificacion['profesor_apellido'] . ' ' . $calificacion['profesor_apellido_materno'],
            ucfirst($calificacion['tipo_evaluacion']),
            number_format($calificacion['calificacion'], 2),
            date('d/m/Y', strtotime($calificacion['fecha_evaluacion'])),
            $calificacion['observaciones'] ?: 'Sin observaciones',
            $calificacion['fecha_creacion'] ? date('d/m/Y H:i', strtotime($calificacion['fecha_creacion'])) : 'Sin fecha'
        ], ';');
    }
    
    fclose($output);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al exportar los datos: ' . $e->getMessage();
    redirect('index.php');
}
?>



<?php
/**
 * Exportar Materias
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
$grado_id = isset($_GET['grado_id']) ? (int)$_GET['grado_id'] : 0;
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';

try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = ["m.estado = 'activo'"];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(m.nombre LIKE :search OR m.codigo LIKE :search OR m.descripcion LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por grado
    if ($grado_id > 0) {
        $where_conditions[] = "m.grado_id = :grado_id";
        $params['grado_id'] = $grado_id;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "m.estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Consulta para obtener todas las materias
    $sql = "
        SELECT 
            m.id,
            m.codigo,
            m.nombre,
            m.descripcion,
            m.creditos,
            m.grado_id,
            m.estado,
            m.fecha_creacion,
            g.nombre as grado_nombre
        FROM materias m
        LEFT JOIN grados g ON m.grado_id = g.id
        WHERE {$where_clause}
        ORDER BY m.id DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para descarga
    $filename = 'materias_' . date('Y-m-d_H-i-s') . '.csv';
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
        'Código',
        'Nombre',
        'Descripción',
        'Grado',
        'Créditos',
        'Estado',
        'Fecha de Registro'
    ], ';');
    
    // Datos
    foreach ($materias as $materia) {
        fputcsv($output, [
            $materia['id'],
            $materia['codigo'],
            $materia['nombre'],
            $materia['descripcion'] ?: 'Sin descripción',
            $materia['grado_nombre'] ?: 'Sin grado',
            $materia['creditos'] ?: 0,
            ucfirst($materia['estado']),
            $materia['fecha_creacion'] ? date('d/m/Y H:i', strtotime($materia['fecha_creacion'])) : 'Sin fecha'
        ], ';');
    }
    
    fclose($output);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al exportar los datos: ' . $e->getMessage();
    redirect('index.php');
}
?>


<?php
/**
 * Exportar Grupos
 * Sistema de Control Escolar
 */

// Configurar codificación UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn() || !hasPermission('admin')) {
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
    $where_conditions = [];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(g.nombre LIKE :search OR gr.nombre LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por grado
    if ($grado_id > 0) {
        $where_conditions[] = "g.grado_id = :grado_id";
        $params['grado_id'] = $grado_id;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "g.estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Consulta para obtener todos los grupos
    $sql = "
        SELECT 
            g.id, g.nombre, g.grado_id, g.capacidad, g.estado, g.fecha_creacion,
            gr.nombre as grado_nombre
        FROM grupos g
        LEFT JOIN grados gr ON g.grado_id = gr.id
        {$where_clause}
        ORDER BY gr.nombre, g.nombre, g.id
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para descarga
    $filename = 'grupos_' . date('Y-m-d_H-i-s') . '.csv';
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
        'Nombre del Grupo',
        'Grado',
        'Grado ID',
        'Capacidad',
        'Estado',
        'Fecha de Creación'
    ], ';');
    
    // Datos
    foreach ($grupos as $grupo) {
        fputcsv($output, [
            $grupo['id'],
            $grupo['nombre'],
            $grupo['grado_nombre'] ?? 'Sin grado',
            $grupo['grado_id'],
            $grupo['capacidad'] ?? 'Sin límite',
            ucfirst($grupo['estado']),
            date('d/m/Y H:i', strtotime($grupo['fecha_creacion']))
        ], ';');
    }
    
    fclose($output);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al exportar los datos: ' . $e->getMessage();
    redirect('index.php');
}
?>



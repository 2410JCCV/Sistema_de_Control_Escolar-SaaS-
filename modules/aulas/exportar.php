<?php
/**
 * Exportar Aulas
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
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';

try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = [];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(nombre LIKE :search OR ubicacion LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por tipo
    if (!empty($tipo)) {
        $where_conditions[] = "tipo = :tipo";
        $params['tipo'] = $tipo;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Consulta para obtener todas las aulas
    $sql = "
        SELECT 
            id, nombre, ubicacion, capacidad, tipo, estado, fecha_creacion
        FROM aulas
        {$where_clause}
        ORDER BY nombre, id
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para descarga
    $filename = 'aulas_' . date('Y-m-d_H-i-s') . '.csv';
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
        'Nombre del Aula',
        'Ubicación',
        'Capacidad',
        'Tipo',
        'Estado',
        'Fecha de Creación'
    ], ';');
    
    // Datos
    foreach ($aulas as $aula) {
        fputcsv($output, [
            $aula['id'],
            $aula['nombre'],
            $aula['ubicacion'] ?? 'Sin ubicación',
            $aula['capacidad'] ?? 'Sin límite',
            ucfirst($aula['tipo']),
            ucfirst($aula['estado']),
            date('d/m/Y H:i', strtotime($aula['fecha_creacion']))
        ], ';');
    }
    
    fclose($output);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al exportar los datos: ' . $e->getMessage();
    redirect('index.php');
}
?>



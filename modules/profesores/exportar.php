<?php
/**
 * Exportar Profesores
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
$especialidad = isset($_GET['especialidad']) ? trim($_GET['especialidad']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';

try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = ["p.estado = 'activo'"];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(p.nombre LIKE :search OR p.apellido_paterno LIKE :search OR p.apellido_materno LIKE :search OR p.codigo LIKE :search OR p.email LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por especialidad
    if (!empty($especialidad)) {
        $where_conditions[] = "p.especialidad = :especialidad";
        $params['especialidad'] = $especialidad;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "p.estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Consulta para obtener todos los profesores
    $sql = "
        SELECT 
            p.id,
            p.codigo,
            p.nombre,
            p.apellido_paterno,
            p.apellido_materno,
            p.fecha_nacimiento,
            p.especialidad,
            p.telefono,
            p.email,
            p.direccion,
            p.fecha_ingreso,
            p.salario,
            p.estado,
            p.fecha_creacion
        FROM profesores p
        WHERE {$where_clause}
        ORDER BY p.id DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para descarga
    $filename = 'profesores_' . date('Y-m-d_H-i-s') . '.csv';
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
        'Apellido Paterno',
        'Apellido Materno',
        'Fecha de Nacimiento',
        'Especialidad',
        'Email',
        'Teléfono',
        'Dirección',
        'Fecha de Ingreso',
        'Salario',
        'Estado',
        'Fecha de Registro'
    ], ';');
    
    // Datos
    foreach ($profesores as $profesor) {
        fputcsv($output, [
            $profesor['id'],
            $profesor['codigo'],
            $profesor['nombre'],
            $profesor['apellido_paterno'],
            $profesor['apellido_materno'],
            $profesor['fecha_nacimiento'] ? date('d/m/Y', strtotime($profesor['fecha_nacimiento'])) : 'Sin fecha',
            $profesor['especialidad'],
            $profesor['email'] ?: 'Sin email',
            $profesor['telefono'] ?: 'Sin teléfono',
            $profesor['direccion'] ?: 'Sin dirección',
            date('d/m/Y', strtotime($profesor['fecha_ingreso'])),
            $profesor['salario'] ? '$' . number_format($profesor['salario'], 2) : 'No especificado',
            ucfirst($profesor['estado']),
            $profesor['fecha_creacion'] ? date('d/m/Y H:i', strtotime($profesor['fecha_creacion'])) : 'Sin fecha'
        ], ';');
    }
    
    fclose($output);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al exportar los datos: ' . $e->getMessage();
    redirect('index.php');
}
?>


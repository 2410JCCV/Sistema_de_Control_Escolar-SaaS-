<?php
/**
 * Exportar Estudiantes
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
$grupo_id = isset($_GET['grupo_id']) ? (int)$_GET['grupo_id'] : 0;

try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = ["e.estado = 'activo'"];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(e.nombre LIKE :search OR e.apellido_paterno LIKE :search OR e.apellido_materno LIKE :search OR e.matricula LIKE :search OR e.email LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por grado
    if ($grado_id > 0) {
        $where_conditions[] = "e.grado_id = :grado_id";
        $params['grado_id'] = $grado_id;
    }
    
    // Filtro por grupo
    if ($grupo_id > 0) {
        $where_conditions[] = "e.grupo_id = :grupo_id";
        $params['grupo_id'] = $grupo_id;
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Consulta para obtener todos los estudiantes
    $sql = "
        SELECT 
            e.id,
            e.matricula,
            e.nombre,
            e.apellido_paterno,
            e.apellido_materno,
            e.fecha_nacimiento,
            e.email,
            e.telefono,
            e.direccion,
            e.estado,
            e.fecha_creacion,
            g.nombre as grado_nombre,
            gr.nombre as grupo_nombre
        FROM estudiantes e
        LEFT JOIN grados g ON e.grado_id = g.id
        LEFT JOIN grupos gr ON e.grupo_id = gr.id
        WHERE {$where_clause}
        ORDER BY e.id DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para descarga
    $filename = 'estudiantes_' . date('Y-m-d_H-i-s') . '.csv';
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
        'Matrícula',
        'Nombre',
        'Apellido Paterno',
        'Apellido Materno',
        'Fecha de Nacimiento',
        'Email',
        'Teléfono',
        'Dirección',
        'Grado',
        'Grupo',
        'Estado',
        'Fecha de Registro'
    ], ';');
    
    // Datos
    foreach ($estudiantes as $estudiante) {
        fputcsv($output, [
            $estudiante['id'],
            $estudiante['matricula'],
            $estudiante['nombre'],
            $estudiante['apellido_paterno'],
            $estudiante['apellido_materno'],
            $estudiante['fecha_nacimiento'] ? date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) : 'Sin fecha',
            $estudiante['email'],
            $estudiante['telefono'],
            $estudiante['direccion'],
            $estudiante['grado_nombre'] ?? 'Sin grado',
            $estudiante['grupo_nombre'] ?? 'Sin grupo',
            ucfirst($estudiante['estado']),
            $estudiante['fecha_creacion'] ? date('d/m/Y H:i', strtotime($estudiante['fecha_creacion'])) : 'Sin fecha'
        ], ';');
    }
    
    fclose($output);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al exportar los datos: ' . $e->getMessage();
    redirect('index.php');
}
?>

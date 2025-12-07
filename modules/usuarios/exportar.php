<?php
/**
 * Exportar Usuarios
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
$rol = isset($_GET['rol']) ? trim($_GET['rol']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';

try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = [];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(username LIKE :search OR email LIKE :search OR nombre LIKE :search OR apellido LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por rol
    if (!empty($rol)) {
        $where_conditions[] = "rol = :rol";
        $params['rol'] = $rol;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Consulta para obtener todos los usuarios
    $sql = "
        SELECT 
            id, username, email, nombre, apellido, rol, estado, 
            fecha_creacion, fecha_actualizacion
        FROM usuarios
        {$where_clause}
        ORDER BY fecha_creacion DESC, id DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para descarga
    $filename = 'usuarios_' . date('Y-m-d_H-i-s') . '.csv';
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
        'Usuario',
        'Email',
        'Nombre',
        'Apellido',
        'Nombre Completo',
        'Rol',
        'Estado',
        'Fecha de Registro',
        'Última Actualización'
    ], ';');
    
    // Datos
    foreach ($usuarios as $usuario) {
        fputcsv($output, [
            $usuario['id'],
            $usuario['username'],
            $usuario['email'],
            $usuario['nombre'],
            $usuario['apellido'],
            $usuario['nombre'] . ' ' . $usuario['apellido'],
            ucfirst($usuario['rol']),
            ucfirst($usuario['estado']),
            date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])),
            $usuario['fecha_actualizacion'] ? date('d/m/Y H:i', strtotime($usuario['fecha_actualizacion'])) : 'Sin actualizar'
        ], ';');
    }
    
    fclose($output);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al exportar los datos: ' . $e->getMessage();
    redirect('index.php');
}
?>



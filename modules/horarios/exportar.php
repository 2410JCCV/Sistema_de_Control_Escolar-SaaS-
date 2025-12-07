<?php
/**
 * Exportar Horarios
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
$dia_semana = isset($_GET['dia_semana']) ? trim($_GET['dia_semana']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';

try {
    $pdo = conectarDB();
    
    // Construir consulta base
    $where_conditions = [];
    $params = [];
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(m.nombre LIKE :search OR p.nombre LIKE :search OR g.nombre LIKE :search OR a.nombre LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // Filtro por día de la semana
    if (!empty($dia_semana)) {
        $where_conditions[] = "h.dia_semana = :dia_semana";
        $params['dia_semana'] = $dia_semana;
    }
    
    // Filtro por estado
    if (!empty($estado)) {
        $where_conditions[] = "h.estado = :estado";
        $params['estado'] = $estado;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Consulta para obtener todos los horarios
    $sql = "
        SELECT 
            h.id, h.materia_id, h.profesor_id, h.grupo_id, h.aula_id,
            h.dia_semana, h.hora_inicio, h.hora_fin, h.estado, h.fecha_creacion,
            m.nombre as materia_nombre,
            p.nombre as profesor_nombre, p.apellido_paterno as profesor_apellido,
            g.nombre as grupo_nombre, gr.nombre as grado_nombre,
            a.nombre as aula_nombre
        FROM horarios h
        LEFT JOIN materias m ON h.materia_id = m.id
        LEFT JOIN profesores p ON h.profesor_id = p.id
        LEFT JOIN grupos g ON h.grupo_id = g.id
        LEFT JOIN grados gr ON g.grado_id = gr.id
        LEFT JOIN aulas a ON h.aula_id = a.id
        {$where_clause}
        ORDER BY h.dia_semana, h.hora_inicio, h.id
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para descarga
    $filename = 'horarios_' . date('Y-m-d_H-i-s') . '.csv';
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
        'Materia',
        'Profesor',
        'Grupo',
        'Grado',
        'Aula',
        'Día de la Semana',
        'Hora de Inicio',
        'Hora de Fin',
        'Duración (min)',
        'Estado',
        'Fecha de Creación'
    ], ';');
    
    // Datos
    foreach ($horarios as $horario) {
        $inicio = strtotime($horario['hora_inicio']);
        $fin = strtotime($horario['hora_fin']);
        $duracion = ($fin - $inicio) / 60; // en minutos
        
        fputcsv($output, [
            $horario['id'],
            $horario['materia_nombre'] ?? 'Sin materia',
            ($horario['profesor_nombre'] ?? '') . ' ' . ($horario['profesor_apellido'] ?? ''),
            $horario['grupo_nombre'] ?? 'Sin grupo',
            $horario['grado_nombre'] ?? 'Sin grado',
            $horario['aula_nombre'] ?? 'Sin aula',
            ucfirst($horario['dia_semana']),
            date('H:i', strtotime($horario['hora_inicio'])),
            date('H:i', strtotime($horario['hora_fin'])),
            $duracion,
            ucfirst($horario['estado']),
            date('d/m/Y H:i', strtotime($horario['fecha_creacion']))
        ], ';');
    }
    
    fclose($output);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al exportar los datos: ' . $e->getMessage();
    redirect('index.php');
}
?>



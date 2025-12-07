<?php
/**
 * Funciones auxiliares del sistema
 * Sistema de Control Escolar
 */

/**
 * Sanitizar entrada de datos
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Verificar si el usuario está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verificar permisos de usuario
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'];
    
    switch($permission) {
        case 'admin':
            return $user_role === 'admin';
        case 'profesor':
            return in_array($user_role, ['admin', 'profesor']);
        case 'estudiante':
            return in_array($user_role, ['admin', 'profesor', 'estudiante']);
        default:
            return false;
    }
}

/**
 * Redirigir a una página
 */
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

/**
 * Mostrar mensaje de error
 */
function showError($message) {
    return "<div class='alert alert-danger'>" . $message . "</div>";
}

/**
 * Mostrar mensaje de éxito
 */
function showSuccess($message) {
    return "<div class='alert alert-success'>" . $message . "</div>";
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

/**
 * Generar código único
 */
function generateCode($prefix = '', $length = 6) {
    return $prefix . strtoupper(substr(uniqid(), -$length));
}

/**
 * Validar email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Cargar vista
 */
function loadView($view, $data = []) {
    extract($data);
    include "../views/{$view}.php";
}

/**
 * Cargar modelo
 */
function loadModel($model) {
    include "../models/{$model}.php";
}

/**
 * Paginación
 */
function paginate($total_records, $current_page = 1, $records_per_page = RECORDS_PER_PAGE) {
    $total_pages = ceil($total_records / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;
    
    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'records_per_page' => $records_per_page
    ];
}

/**
 * Obtener calificaciones por materia
 */
function obtenerCalificacionesPorMateria($grupo_id = null, $materia_id = null, $periodo = null) {
    $pdo = conectarDB();
    try {
        $where_conditions = ["c.calificacion IS NOT NULL"];
        $params = [];
        
        if ($grupo_id) {
            $where_conditions[] = "e.grupo_id = :grupo_id";
            $params['grupo_id'] = $grupo_id;
        }
        
        if ($materia_id) {
            $where_conditions[] = "c.materia_id = :materia_id";
            $params['materia_id'] = $materia_id;
        }
        
        $where_clause = implode(" AND ", $where_conditions);
        
        $stmt = $pdo->prepare("
            SELECT 
                m.nombre as materia,
                CONCAT(g.nombre, ' - ', gr.nombre) as grupo,
                COUNT(DISTINCT e.id) as total_estudiantes,
                AVG(c.calificacion) as promedio,
                MAX(c.calificacion) as mas_alta,
                MIN(c.calificacion) as mas_baja,
                COUNT(CASE WHEN c.calificacion >= 6 THEN 1 END) as aprobados,
                COUNT(CASE WHEN c.calificacion < 6 THEN 1 END) as reprobados,
                ROUND((COUNT(CASE WHEN c.calificacion >= 6 THEN 1 END) * 100.0 / COUNT(*)), 0) as porcentaje_aprobacion
            FROM calificaciones c
            JOIN estudiantes e ON c.estudiante_id = e.id
            JOIN materias m ON c.materia_id = m.id
            JOIN grupos gr ON e.grupo_id = gr.id
            JOIN grados g ON e.grado_id = g.id
            WHERE {$where_clause}
            GROUP BY m.id, m.nombre, g.nombre, gr.nombre
            ORDER BY m.nombre
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>


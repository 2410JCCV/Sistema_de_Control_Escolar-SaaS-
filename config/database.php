<?php
/**
 * Configuración de Base de Datos
 * Sistema de Control Escolar
 */

// Configuración de la base de datos (las constantes ya están definidas en config.php)
define('DB_CHARSET', 'utf8mb4');

// Función para conectar a la base de datos
function conectarDB() {
    try {
        // Construir DSN con puerto solo si está definido y es diferente del puerto estándar
        $dsn = "mysql:host=" . DB_HOST;
        if (defined('DB_PORT') && DB_PORT != '3306' && DB_PORT != '') {
            $dsn .= ";port=" . DB_PORT;
        }
        $dsn .= ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Configurar UTF-8 explícitamente
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("SET CHARACTER SET utf8mb4");
        
        return $pdo;
    } catch (PDOException $e) {
        // Si falla con el puerto especificado, intentar sin puerto (puerto por defecto)
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("SET CHARACTER SET utf8mb4");
            return $pdo;
        } catch (PDOException $e2) {
            die("Error de conexión: " . $e2->getMessage() . "<br>Verifica que MySQL esté corriendo en WAMP y que el puerto sea correcto.");
        }
    }
}

// Función para obtener datos de estudiantes
function obtenerEstudiantes($filtros = []) {
    $pdo = conectarDB();
    
    // Parámetros de paginación
    $page = isset($filtros['page']) ? (int)$filtros['page'] : 1;
    $limit = isset($filtros['limit']) ? (int)$filtros['limit'] : 10; // 10 estudiantes por página
    $offset = ($page - 1) * $limit;
    
    try {
        // Construir la consulta base
        $where_conditions = ["e.estado = 'activo'"];
        $params = [];
        
        // Filtro de búsqueda
        if (!empty($filtros['busqueda'])) {
            $where_conditions[] = "(e.nombre LIKE :busqueda OR e.apellido_paterno LIKE :busqueda OR e.matricula LIKE :busqueda OR e.email LIKE :busqueda)";
            $params['busqueda'] = '%' . $filtros['busqueda'] . '%';
        }
        
        // Filtro por grado
        if (!empty($filtros['grado'])) {
            $where_conditions[] = "e.grado_id = :grado_id";
            $params['grado_id'] = $filtros['grado'];
        }
        
        // Filtro por grupo
        if (!empty($filtros['grupo'])) {
            $where_conditions[] = "e.grupo_id = :grupo_id";
            $params['grupo_id'] = $filtros['grupo'];
        }
        
        $where_clause = implode(" AND ", $where_conditions);
        
        // Consulta principal - SIMPLIFICADA
        $sql = "
            SELECT 
                e.id,
                e.matricula,
                CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', IFNULL(e.apellido_materno, '')) as nombre,
                COALESCE(g.nombre, 'Sin grado') as grado,
                COALESCE(gr.nombre, 'Sin grupo') as grupo,
                e.telefono,
                e.email,
                e.estado,
                0 as promedio
            FROM estudiantes e
            LEFT JOIN grados g ON e.grado_id = g.id
            LEFT JOIN grupos gr ON e.grupo_id = gr.id
            WHERE {$where_clause}
            ORDER BY e.id DESC
        ";
        
        // Contar total de registros
        $count_sql = "
            SELECT COUNT(DISTINCT e.id) as total
            FROM estudiantes e
            LEFT JOIN grados g ON e.grado_id = g.id
            LEFT JOIN grupos gr ON e.grupo_id = gr.id
            WHERE {$where_clause}
        ";
        
        $stmt = $pdo->prepare($count_sql);
        $stmt->execute($params);
        $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Aplicar paginación directamente en la consulta
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_pages = ceil($total_records / $limit);
        
        return [
            'data' => $estudiantes,
            'total_records' => $total_records,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'records_per_page' => $limit
        ];
        
    } catch (PDOException $e) {
        return [
            'data' => [],
            'total_records' => 0,
            'current_page' => 1,
            'total_pages' => 0,
            'records_per_page' => $limit,
            'error' => $e->getMessage()
        ];
    }
}

// Función para agregar un nuevo estudiante
function agregarEstudiante($datos) {
    $pdo = conectarDB();
    try {
        // Generar matrícula automática
        $matricula = 'EST' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Verificar que la matrícula no exista
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM estudiantes WHERE matricula = ?");
        $stmt->execute([$matricula]);
        while ($stmt->fetchColumn() > 0) {
            $matricula = 'EST' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $stmt->execute([$matricula]);
        }
        
        // Insertar estudiante
        $sql = "INSERT INTO estudiantes (matricula, nombre, apellido_paterno, apellido_materno, fecha_nacimiento, grado_id, grupo_id, telefono, email, direccion, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            $matricula,
            $datos['nombre'],
            $datos['apellido_paterno'],
            $datos['apellido_materno'] ?? null,
            $datos['fecha_nacimiento'],
            $datos['grado_id'],
            $datos['grupo_id'],
            $datos['telefono'] ?? null,
            $datos['email'],
            $datos['direccion'] ?? null,
            $datos['estado'] ?? 'activo'
        ]);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Estudiante agregado correctamente',
                'matricula' => $matricula,
                'id' => $pdo->lastInsertId()
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al agregar el estudiante'
            ];
        }
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage()
        ];
    }
}

// Función para obtener un estudiante por ID
function obtenerEstudiantePorId($id) {
    $pdo = conectarDB();
    try {
        $stmt = $pdo->prepare("
            SELECT 
                e.*,
                g.nombre as grado_nombre,
                gr.nombre as grupo_nombre
            FROM estudiantes e
            LEFT JOIN grados g ON e.grado_id = g.id
            LEFT JOIN grupos gr ON e.grupo_id = gr.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

// Función para actualizar un estudiante
function actualizarEstudiante($id, $datos) {
    $pdo = conectarDB();
    try {
        $sql = "UPDATE estudiantes SET 
                nombre = ?, 
                apellido_paterno = ?, 
                apellido_materno = ?, 
                fecha_nacimiento = ?, 
                grado_id = ?, 
                grupo_id = ?, 
                telefono = ?, 
                email = ?, 
                direccion = ?, 
                estado = ?,
                fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            $datos['nombre'],
            $datos['apellido_paterno'],
            $datos['apellido_materno'] ?? null,
            $datos['fecha_nacimiento'],
            $datos['grado_id'],
            $datos['grupo_id'],
            $datos['telefono'] ?? null,
            $datos['email'],
            $datos['direccion'] ?? null,
            $datos['estado'] ?? 'activo',
            $id
        ]);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Estudiante actualizado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar el estudiante'
            ];
        }
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage()
        ];
    }
}

// Función para eliminar un estudiante (cambiar estado a inactivo)
function eliminarEstudiante($id) {
    $pdo = conectarDB();
    try {
        $sql = "UPDATE estudiantes SET estado = 'inactivo', fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([$id]);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Estudiante eliminado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al eliminar el estudiante'
            ];
        }
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage()
        ];
    }
}

// Función para eliminar permanentemente un estudiante
function eliminarEstudiantePermanente($id) {
    $pdo = conectarDB();
    try {
        // Primero eliminar calificaciones relacionadas
        $stmt = $pdo->prepare("DELETE FROM calificaciones WHERE estudiante_id = ?");
        $stmt->execute([$id]);
        
        // Luego eliminar asistencias relacionadas
        $stmt = $pdo->prepare("DELETE FROM asistencias WHERE estudiante_id = ?");
        $stmt->execute([$id]);
        
        // Finalmente eliminar el estudiante
        $stmt = $pdo->prepare("DELETE FROM estudiantes WHERE id = ?");
        $resultado = $stmt->execute([$id]);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Estudiante eliminado permanentemente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al eliminar el estudiante'
            ];
        }
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage()
        ];
    }
}

// Función para obtener grados disponibles
function obtenerGrados() {
    $pdo = conectarDB();
    try {
        $stmt = $pdo->query("SELECT id, nombre FROM grados WHERE estado = 'activo' ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Función para obtener grupos disponibles
function obtenerGruposDisponibles() {
    $pdo = conectarDB();
    try {
        $stmt = $pdo->query("
            SELECT g.id, g.nombre as grupo, gr.nombre as grado, gr.id as grado_id 
            FROM grupos g 
            JOIN grados gr ON g.grado_id = gr.id 
            WHERE g.estado = 'activo' AND gr.estado = 'activo' 
            ORDER BY gr.id, g.nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Función para obtener grupos que tienen estudiantes
function obtenerGruposConEstudiantes() {
    $pdo = conectarDB();
    try {
        $stmt = $pdo->query("
            SELECT DISTINCT 
                g.id, 
                g.nombre as grupo, 
                gr.nombre as grado, 
                gr.id as grado_id,
                COUNT(e.id) as total_estudiantes
            FROM grupos g 
            JOIN grados gr ON g.grado_id = gr.id 
            JOIN estudiantes e ON g.id = e.grupo_id
            WHERE g.estado = 'activo' 
                AND gr.estado = 'activo' 
                AND e.estado = 'activo'
            GROUP BY g.id, g.nombre, gr.nombre, gr.id
            ORDER BY gr.id, g.nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Función para obtener datos de profesores
function obtenerProfesores($filtros = []) {
    $pdo = conectarDB();
    try {
        $stmt = $pdo->query("
            SELECT 
                p.id,
                CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) as nombre,
                p.especialidad as materia,
                COUNT(DISTINCT h.grupo_id) as grupos,
                COUNT(DISTINCT e.id) as estudiantes,
                p.email
            FROM profesores p
            LEFT JOIN horarios h ON p.id = h.profesor_id AND h.estado = 'activo'
            LEFT JOIN estudiantes e ON h.grupo_id = e.grupo_id AND e.estado = 'activo'
            WHERE p.estado = 'activo'
            GROUP BY p.id, p.nombre, p.apellido_paterno, p.apellido_materno, p.especialidad, p.email
            ORDER BY p.nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Función para obtener datos de materias
function obtenerMaterias($filtros = []) {
    $pdo = conectarDB();
    try {
        $stmt = $pdo->query("
            SELECT 
                m.id,
                m.nombre as materia,
                m.codigo,
                g.nombre as nivel,
                m.creditos,
                COALESCE(AVG(c.calificacion), 0) as promedio,
                COUNT(CASE WHEN COALESCE(AVG(c.calificacion), 0) >= 6 THEN 1 END) as aprobados
            FROM materias m
            LEFT JOIN grados g ON m.grado_id = g.id
            LEFT JOIN calificaciones c ON m.id = c.materia_id
            WHERE m.estado = 'activo'
            GROUP BY m.id, m.nombre, m.codigo, g.nombre, m.creditos
            ORDER BY m.nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Función para obtener datos de grupos
function obtenerGrupos($filtros = []) {
    $pdo = conectarDB();
    try {
        $stmt = $pdo->query("
            SELECT 
                gr.id,
                CONCAT(g.nombre, ' - ', gr.nombre) as grupo,
                COUNT(e.id) as estudiantes,
                COALESCE(AVG(c.calificacion), 0) as promedio,
                COUNT(CASE WHEN COALESCE(AVG(c.calificacion), 0) >= 6 THEN 1 END) as aprobados
            FROM grupos gr
            LEFT JOIN grados g ON gr.grado_id = g.id
            LEFT JOIN estudiantes e ON gr.id = e.grupo_id AND e.estado = 'activo'
            LEFT JOIN calificaciones c ON e.id = c.estudiante_id
            WHERE gr.estado = 'activo'
            GROUP BY gr.id, g.nombre, gr.nombre
            ORDER BY g.nombre, gr.nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Función para obtener estadísticas generales
function obtenerEstadisticasGenerales() {
    $pdo = conectarDB();
    try {
        // Total estudiantes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM estudiantes WHERE estado = 'activo'");
        $total_estudiantes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total profesores
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM profesores WHERE estado = 'activo'");
        $total_profesores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total grupos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM grupos WHERE estado = 'activo'");
        $total_grupos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total materias
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM materias WHERE estado = 'activo'");
        $total_materias = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Promedio general
        $stmt = $pdo->query("SELECT AVG(calificacion) as promedio FROM calificaciones");
        $promedio_general = $stmt->fetch(PDO::FETCH_ASSOC)['promedio'] ?? 0;
        
        // Porcentaje de aprobación
        $stmt = $pdo->query("
            SELECT 
                (COUNT(CASE WHEN calificacion >= 6 THEN 1 END) * 100.0 / COUNT(*)) as porcentaje
            FROM calificaciones
        ");
        $porcentaje_aprobacion = $stmt->fetch(PDO::FETCH_ASSOC)['porcentaje'] ?? 0;
        
        return [
            'total_estudiantes' => $total_estudiantes,
            'total_profesores' => $total_profesores,
            'total_grupos' => $total_grupos,
            'total_materias' => $total_materias,
            'promedio_general' => round($promedio_general, 1),
            'porcentaje_aprobacion' => round($porcentaje_aprobacion, 0)
        ];
    } catch (PDOException $e) {
        return [
            'total_estudiantes' => 0,
            'total_profesores' => 0,
            'total_grupos' => 0,
            'total_materias' => 0,
            'promedio_general' => 0,
            'porcentaje_aprobacion' => 0
        ];
    }
}
?>
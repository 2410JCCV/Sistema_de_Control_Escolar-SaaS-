<?php
/**
 * Sistema de Búsqueda Global
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../config/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

// Función de búsqueda global
function buscarGlobal($termino, $modulo = 'todos') {
    $resultados = [];
    $termino = sanitize($termino);
    
    if (empty($termino)) {
        return $resultados;
    }
    
    // Datos de ejemplo (en un sistema real, esto vendría de la base de datos)
    $datos = [
        'estudiantes' => [
            ['id' => 1, 'nombre' => 'Juan Pérez García', 'grupo' => '1° A', 'promedio' => 8.5, 'email' => 'juan.perez@escuela.com'],
            ['id' => 2, 'nombre' => 'María González López', 'grupo' => '1° A', 'promedio' => 9.2, 'email' => 'maria.gonzalez@escuela.com'],
            ['id' => 3, 'nombre' => 'Carlos Rodríguez Martín', 'grupo' => '1° B', 'promedio' => 7.8, 'email' => 'carlos.rodriguez@escuela.com'],
            ['id' => 4, 'nombre' => 'Ana Martínez Silva', 'grupo' => '2° A', 'promedio' => 8.9, 'email' => 'ana.martinez@escuela.com'],
            ['id' => 5, 'nombre' => 'Luis Fernández Torres', 'grupo' => '2° B', 'promedio' => 8.1, 'email' => 'luis.fernandez@escuela.com'],
            ['id' => 6, 'nombre' => 'Sofía Hernández Ruiz', 'grupo' => '3° A', 'promedio' => 9.5, 'email' => 'sofia.hernandez@escuela.com'],
            ['id' => 7, 'nombre' => 'Diego Morales Castro', 'grupo' => '3° B', 'promedio' => 8.3, 'email' => 'diego.morales@escuela.com'],
            ['id' => 8, 'nombre' => 'Laura Sánchez Jiménez', 'grupo' => '1° A', 'promedio' => 9.0, 'email' => 'laura.sanchez@escuela.com'],
            ['id' => 9, 'nombre' => 'Pedro Vargas Moreno', 'grupo' => '2° A', 'promedio' => 7.5, 'email' => 'pedro.vargas@escuela.com'],
            ['id' => 10, 'nombre' => 'Carmen Ruiz Díaz', 'grupo' => '3° A', 'promedio' => 8.7, 'email' => 'carmen.ruiz@escuela.com'],
            ['id' => 11, 'nombre' => 'Juan Carlos López', 'grupo' => '1° B', 'promedio' => 8.2, 'email' => 'juan.carlos@escuela.com'],
            ['id' => 12, 'nombre' => 'Juan Manuel Torres', 'grupo' => '2° B', 'promedio' => 7.9, 'email' => 'juan.manuel@escuela.com'],
            ['id' => 13, 'nombre' => 'Juan Esteban Ruiz', 'grupo' => '3° A', 'promedio' => 8.8, 'email' => 'juan.esteban@escuela.com'],
            ['id' => 14, 'nombre' => 'Juan Pablo Morales', 'grupo' => '1° A', 'promedio' => 9.1, 'email' => 'juan.pablo@escuela.com'],
            ['id' => 15, 'nombre' => 'Juan Diego Sánchez', 'grupo' => '2° A', 'promedio' => 8.4, 'email' => 'juan.diego@escuela.com']
        ],
        'profesores' => [
            ['id' => 1, 'nombre' => 'María González', 'materia' => 'Matemáticas', 'grupos' => 2, 'email' => 'maria.gonzalez@escuela.com'],
            ['id' => 2, 'nombre' => 'Juan Pérez', 'materia' => 'Ciencias', 'grupos' => 2, 'email' => 'juan.perez@escuela.com'],
            ['id' => 3, 'nombre' => 'Ana Martínez', 'materia' => 'Historia', 'grupos' => 2, 'email' => 'ana.martinez@escuela.com'],
            ['id' => 4, 'nombre' => 'Carlos López', 'materia' => 'Educación Física', 'grupos' => 2, 'email' => 'carlos.lopez@escuela.com'],
            ['id' => 5, 'nombre' => 'Laura Sánchez', 'materia' => 'Español', 'grupos' => 2, 'email' => 'laura.sanchez@escuela.com'],
            ['id' => 6, 'nombre' => 'Pedro Vargas', 'materia' => 'Matemáticas', 'grupos' => 1, 'email' => 'pedro.vargas@escuela.com'],
            ['id' => 7, 'nombre' => 'Carmen Ruiz', 'materia' => 'Ciencias', 'grupos' => 1, 'email' => 'carmen.ruiz@escuela.com']
        ],
        'materias' => [
            ['id' => 1, 'nombre' => 'Matemáticas', 'codigo' => 'MAT-101', 'nivel' => 'Primaria', 'creditos' => 4],
            ['id' => 2, 'nombre' => 'Español', 'codigo' => 'ESP-102', 'nivel' => 'Primaria', 'creditos' => 4],
            ['id' => 3, 'nombre' => 'Ciencias Naturales', 'codigo' => 'CIE-103', 'nivel' => 'Primaria', 'creditos' => 3],
            ['id' => 4, 'nombre' => 'Historia', 'codigo' => 'HIS-104', 'nivel' => 'Primaria', 'creditos' => 3],
            ['id' => 5, 'nombre' => 'Educación Física', 'codigo' => 'EDF-105', 'nivel' => 'Primaria', 'creditos' => 2],
            ['id' => 6, 'nombre' => 'Matemáticas Avanzadas', 'codigo' => 'MAT-201', 'nivel' => 'Secundaria', 'creditos' => 5],
            ['id' => 7, 'nombre' => 'Ciencias Físicas', 'codigo' => 'CIE-201', 'nivel' => 'Secundaria', 'creditos' => 4],
            ['id' => 8, 'nombre' => 'Matemáticas Básicas', 'codigo' => 'MAT-001', 'nivel' => 'Primaria', 'creditos' => 3],
            ['id' => 9, 'nombre' => 'Matemáticas Aplicadas', 'codigo' => 'MAT-301', 'nivel' => 'Preparatoria', 'creditos' => 6],
            ['id' => 10, 'nombre' => 'Matemáticas Discretas', 'codigo' => 'MAT-401', 'nivel' => 'Universidad', 'creditos' => 4]
        ],
        'grupos' => [
            ['id' => 1, 'nombre' => '1° A - Primaria', 'nivel' => 'Primaria', 'estudiantes' => 25, 'profesor' => 'María González'],
            ['id' => 2, 'nombre' => '1° B - Primaria', 'nivel' => 'Primaria', 'estudiantes' => 23, 'profesor' => 'Juan Pérez'],
            ['id' => 3, 'nombre' => '2° A - Primaria', 'nivel' => 'Primaria', 'estudiantes' => 28, 'profesor' => 'Ana Martínez'],
            ['id' => 4, 'nombre' => '2° B - Primaria', 'nivel' => 'Primaria', 'estudiantes' => 24, 'profesor' => 'Carlos López'],
            ['id' => 5, 'nombre' => '3° A - Primaria', 'nivel' => 'Primaria', 'estudiantes' => 26, 'profesor' => 'Laura Sánchez'],
            ['id' => 6, 'nombre' => '3° B - Primaria', 'nivel' => 'Primaria', 'estudiantes' => 25, 'profesor' => 'Pedro Vargas']
        ]
    ];
    
    // Buscar en todos los módulos o en uno específico
    $modulos_a_buscar = ($modulo === 'todos') ? array_keys($datos) : [$modulo];
    
    foreach ($modulos_a_buscar as $mod) {
        if (isset($datos[$mod])) {
            foreach ($datos[$mod] as $item) {
                $encontrado = false;
                
                // Buscar en todos los campos del item
                foreach ($item as $campo => $valor) {
                    if (is_string($valor) && stripos($valor, $termino) !== false) {
                        $encontrado = true;
                        break;
                    }
                }
                
                if ($encontrado) {
                    $item['modulo'] = $mod;
                    $resultados[] = $item;
                }
            }
        }
    }
    
    return $resultados;
}

// Función para obtener sugerencias de búsqueda
function obtenerSugerencias($termino) {
    $termino = sanitize($termino);
    $sugerencias = [];
    
    if (strlen($termino) < 2) {
        return $sugerencias;
    }
    
    $resultados = buscarGlobal($termino);
    
    foreach ($resultados as $item) {
        $sugerencia = '';
        switch ($item['modulo']) {
            case 'estudiantes':
                $sugerencia = $item['nombre'] . ' (Estudiante - ' . $item['grupo'] . ')';
                break;
            case 'profesores':
                $sugerencia = $item['nombre'] . ' (Profesor - ' . $item['materia'] . ')';
                break;
            case 'materias':
                $sugerencia = $item['nombre'] . ' (Materia - ' . $item['codigo'] . ')';
                break;
            case 'grupos':
                $sugerencia = $item['nombre'] . ' (Grupo - ' . $item['nivel'] . ')';
                break;
        }
        
        if (!in_array($sugerencia, $sugerencias)) {
            $sugerencias[] = $sugerencia;
        }
    }
    
    return array_slice($sugerencias, 0, 10); // Máximo 10 sugerencias
}
?>

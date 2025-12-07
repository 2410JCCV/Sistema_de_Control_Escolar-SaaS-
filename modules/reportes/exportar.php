<?php
/**
 * Exportar Reportes
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$tipo = isset($_GET['tipo']) ? sanitize($_GET['tipo']) : '';
$reporte = isset($_GET['reporte']) ? sanitize($_GET['reporte']) : 'generales';
$termino_busqueda = isset($_GET['termino']) ? sanitize($_GET['termino']) : '';
$modulo_busqueda = isset($_GET['modulo']) ? sanitize($_GET['modulo']) : 'todos';

// Obtener datos de la base de datos
$datos = [
    'estudiantes' => obtenerEstudiantes(),
    'profesores' => obtenerProfesores(),
    'grupos' => obtenerGrupos(),
    'materias' => obtenerMaterias(),
    'generales' => obtenerEstadisticasGenerales(),
    'calificaciones' => obtenerCalificacionesPorMateria()
];

switch ($tipo) {
    case 'pdf':
        exportarPDF($reporte, $datos);
        break;
    case 'excel':
        exportarExcel($reporte, $datos);
        break;
    case 'word':
        exportarWord($reporte, $datos);
        break;
    case 'notepad':
        exportarNotepad($reporte, $datos);
        break;
    case 'busqueda':
        // Manejar exportación de búsquedas
        if (!empty($termino_busqueda)) {
            require_once __DIR__ . '/../../includes/search.php';
            $resultados = buscarGlobal($termino_busqueda, $modulo_busqueda);
            $datos_busqueda = ['busqueda' => $resultados, 'termino' => $termino_busqueda];
            exportarPDF('busqueda', $datos_busqueda);
        } else {
            header('Location: index.php');
            exit;
        }
    default:
        header('Location: index.php');
        exit;
}

function exportarPDF($reporte, $datos) {
    $filename = "reporte_{$reporte}_" . date('Y-m-d_H-i-s') . ".html";
    
    // Generar HTML que se puede convertir a PDF
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo generarContenidoPDF($reporte, $datos);
    exit;
}

function exportarExcel($reporte, $datos) {
    $filename = "reporte_{$reporte}_" . date('Y-m-d_H-i-s') . ".csv";
    
    // Generar CSV mejorado que se abre en Excel
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo generarContenidoExcel($reporte, $datos);
    exit;
}

function exportarWord($reporte, $datos) {
    $filename = "reporte_{$reporte}_" . date('Y-m-d_H-i-s') . ".html";
    
    // Generar HTML que se abre en Word
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo generarContenidoWord($reporte, $datos);
    exit;
}

function exportarNotepad($reporte, $datos) {
    $filename = "reporte_{$reporte}_" . date('Y-m-d_H-i-s') . ".txt";
    
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo generarContenidoNotepad($reporte, $datos);
    exit;
}

function generarContenidoPDF($reporte, $datos) {
    $contenido = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Reporte de " . ucfirst($reporte) . "</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            color: #333;
            line-height: 1.6;
        }
        .header { 
            text-align: center; 
            border: 3px solid #2c3e50; 
            padding: 20px; 
            margin-bottom: 30px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .header h1 { 
            margin: 0; 
            font-size: 28px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .header p { 
            margin: 10px 0 0 0; 
            font-size: 16px;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        tr:hover {
            background-color: #e3f2fd;
        }
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            color: #6c757d; 
            border-top: 2px solid #dee2e6;
            padding-top: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
    </style>
</head>
<body>";
    $contenido .= "<div class='header'>";
    $contenido .= "<h1>REPORTE DE " . strtoupper($reporte) . "</h1>";
    $contenido .= "<p><strong>Sistema de Control Escolar</strong></p>";
    $contenido .= "<p>Fecha: " . date('d/m/Y H:i:s') . "</p>";
    $contenido .= "</div>";
    
    switch ($reporte) {
        case 'estudiantes':
            $contenido .= "<h2>📚 ESTUDIANTES</h2>";
            $contenido .= "<table>";
            $contenido .= "<thead><tr><th>Nombre</th><th>Grupo</th><th>Promedio</th><th>Email</th></tr></thead>";
            $contenido .= "<tbody>";
            foreach ($datos['estudiantes'] as $estudiante) {
                $contenido .= "<tr>";
                $contenido .= "<td><strong>" . $estudiante['nombre'] . "</strong></td>";
                $contenido .= "<td>" . $estudiante['grupo'] . "</td>";
                $contenido .= "<td>" . $estudiante['promedio'] . "</td>";
                $contenido .= "<td>" . $estudiante['email'] . "</td>";
                $contenido .= "</tr>";
            }
            $contenido .= "</tbody></table>";
            break;
        case 'profesores':
            $contenido .= "<h2>👨‍🏫 PROFESORES</h2>";
            $contenido .= "<table>";
            $contenido .= "<thead><tr><th>Nombre</th><th>Materia</th><th>Grupos</th><th>Estudiantes</th><th>Email</th></tr></thead>";
            $contenido .= "<tbody>";
            foreach ($datos['profesores'] as $profesor) {
                $contenido .= "<tr>";
                $contenido .= "<td><strong>" . $profesor['nombre'] . "</strong></td>";
                $contenido .= "<td>" . $profesor['materia'] . "</td>";
                $contenido .= "<td>" . $profesor['grupos'] . "</td>";
                $contenido .= "<td>" . $profesor['estudiantes'] . "</td>";
                $contenido .= "<td>" . $profesor['email'] . "</td>";
                $contenido .= "</tr>";
            }
            $contenido .= "</tbody></table>";
            break;
        case 'grupos':
            $contenido .= "GRUPOS\n";
            $contenido .= str_repeat("-", 30) . "\n";
            foreach ($datos['grupos'] as $grupo) {
                $contenido .= "Grupo: " . $grupo['grupo'] . "\n";
                $contenido .= "Estudiantes: " . $grupo['estudiantes'] . "\n";
                $contenido .= "Promedio: " . $grupo['promedio'] . "\n";
                $contenido .= "Aprobados: " . $grupo['aprobados'] . "\n\n";
            }
            break;
        case 'materias':
            $contenido .= "MATERIAS\n";
            $contenido .= str_repeat("-", 30) . "\n";
            foreach ($datos['materias'] as $materia) {
                $contenido .= "Materia: " . $materia['materia'] . "\n";
                $contenido .= "Código: " . $materia['codigo'] . "\n";
                $contenido .= "Promedio: " . $materia['promedio'] . "\n";
                $contenido .= "% Aprobados: " . $materia['aprobados'] . "\n\n";
            }
            break;
        case 'generales':
            $contenido .= "REPORTE GENERAL\n";
            $contenido .= str_repeat("-", 30) . "\n";
            $contenido .= "Total Estudiantes: " . $datos['generales']['total_estudiantes'] . "\n";
            $contenido .= "Total Profesores: " . $datos['generales']['total_profesores'] . "\n";
            $contenido .= "Total Grupos: " . $datos['generales']['total_grupos'] . "\n";
            $contenido .= "Total Materias: " . $datos['generales']['total_materias'] . "\n";
            $contenido .= "Promedio General: " . $datos['generales']['promedio_general'] . "\n";
            $contenido .= "% Aprobación: " . $datos['generales']['porcentaje_aprobacion'] . "%\n";
            break;
    }
    
    return $contenido;
}

function generarContenidoExcel($reporte, $datos) {
    // Generar CSV mejorado con formato para Excel
    $contenido = "REPORTE DE " . strtoupper($reporte) . "\n";
    $contenido .= "Sistema de Control Escolar\n";
    $contenido .= "Fecha: " . date('d/m/Y H:i:s') . "\n\n";
    
    switch ($reporte) {
        case 'estudiantes':
            $contenido .= "Nombre,Grupo,Promedio,Email,Estado\n";
            foreach ($datos['estudiantes'] as $estudiante) {
                $estado = $estudiante['promedio'] >= 6 ? 'Aprobado' : 'Reprobado';
                $contenido .= '"' . $estudiante['nombre'] . '","' . $estudiante['grupo'] . '",' . $estudiante['promedio'] . ',"' . $estudiante['email'] . '","' . $estado . '"\n';
            }
            break;
        case 'profesores':
            $contenido .= "Nombre,Materia,Grupos,Estudiantes\n";
            foreach ($datos['profesores'] as $profesor) {
                $contenido .= $profesor['nombre'] . "," . $profesor['materia'] . "," . $profesor['grupos'] . "," . $profesor['estudiantes'] . "\n";
            }
            break;
        case 'grupos':
            $contenido .= "Grupo,Estudiantes,Promedio,Aprobados\n";
            foreach ($datos['grupos'] as $grupo) {
                $contenido .= $grupo['grupo'] . "," . $grupo['estudiantes'] . "," . $grupo['promedio'] . "," . $grupo['aprobados'] . "\n";
            }
            break;
        case 'materias':
            $contenido .= "Materia,Código,Promedio,% Aprobados\n";
            foreach ($datos['materias'] as $materia) {
                $contenido .= $materia['materia'] . "," . $materia['codigo'] . "," . $materia['promedio'] . "," . $materia['aprobados'] . "\n";
            }
            break;
        case 'generales':
            $contenido .= "Métrica,Valor\n";
            $contenido .= "Total Estudiantes," . $datos['generales']['total_estudiantes'] . "\n";
            $contenido .= "Total Profesores," . $datos['generales']['total_profesores'] . "\n";
            $contenido .= "Total Grupos," . $datos['generales']['total_grupos'] . "\n";
            $contenido .= "Total Materias," . $datos['generales']['total_materias'] . "\n";
            $contenido .= "Promedio General," . $datos['generales']['promedio_general'] . "\n";
            $contenido .= "% Aprobación," . $datos['generales']['porcentaje_aprobacion'] . "\n";
            break;
        case 'busqueda':
            $contenido .= "RESULTADOS DE BÚSQUEDA\n";
            $contenido .= "Término buscado," . $datos['termino'] . "\n";
            $contenido .= "Total de resultados," . count($datos['busqueda']) . "\n\n";
            
            if (!empty($datos['busqueda'])) {
                // Agrupar resultados por módulo
                $agrupados = [];
                foreach ($datos['busqueda'] as $item) {
                    $agrupados[$item['modulo']][] = $item;
                }
                
                foreach ($agrupados as $modulo => $items) {
                    $contenido .= strtoupper($modulo) . " (" . count($items) . ")\n";
                    
                    if ($modulo === 'estudiantes') {
                        $contenido .= "Nombre,Grupo,Promedio,Email,Estado\n";
                        foreach ($items as $item) {
                            $estado = $item['promedio'] >= 6 ? 'Aprobado' : 'Reprobado';
                            $contenido .= '"' . $item['nombre'] . '","' . $item['grupo'] . '",' . $item['promedio'] . ',"' . $item['email'] . '","' . $estado . '"\n';
                        }
                    } elseif ($modulo === 'profesores') {
                        $contenido .= "Nombre,Materia,Grupos,Estudiantes,Email\n";
                        foreach ($items as $item) {
                            $contenido .= '"' . $item['nombre'] . '","' . $item['materia'] . '",' . $item['grupos'] . ',' . $item['estudiantes'] . ',"' . $item['email'] . '"\n';
                        }
                    } elseif ($modulo === 'materias') {
                        $contenido .= "Nombre,Código,Nivel,Créditos\n";
                        foreach ($items as $item) {
                            $contenido .= '"' . $item['nombre'] . '","' . $item['codigo'] . '","' . $item['nivel'] . '",' . $item['creditos'] . '\n';
                        }
                    } elseif ($modulo === 'grupos') {
                        $contenido .= "Nombre,Nivel,Estudiantes,Profesor\n";
                        foreach ($items as $item) {
                            $contenido .= '"' . $item['nombre'] . '","' . $item['nivel'] . '",' . $item['estudiantes'] . ',"' . $item['profesor'] . '"\n';
                        }
                    }
                    $contenido .= "\n";
                }
            } else {
                $contenido .= "No se encontraron resultados para la búsqueda.\n";
            }
            break;
    }
    
    return $contenido;
}

function generarContenidoWord($reporte, $datos) {
    // Generar contenido HTML que se puede abrir en Word
    $contenido = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Reporte de " . ucfirst($reporte) . "</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border: 2px solid #333; padding: 20px; margin-bottom: 30px; }
        .header h1 { color: #2c3e50; margin: 0; }
        .header p { margin: 5px 0; color: #7f8c8d; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #3498db; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .footer { margin-top: 30px; text-align: center; color: #7f8c8d; }
    </style>
</head>
<body>";
    $contenido .= "<div class='header'>";
    $contenido .= "<h1>REPORTE DE " . strtoupper($reporte) . "</h1>";
    $contenido .= "<p><strong>Sistema de Control Escolar</strong></p>";
    $contenido .= "<p>Fecha: " . date('d/m/Y H:i:s') . "</p>";
    $contenido .= "</div>";
    
    switch ($reporte) {
        case 'estudiantes':
            $contenido .= "<h2>ESTUDIANTES</h2>";
            $contenido .= "<table border='1' cellpadding='5'>";
            $contenido .= "<tr><th>Nombre</th><th>Grupo</th><th>Promedio</th></tr>";
            foreach ($datos['estudiantes'] as $estudiante) {
                $contenido .= "<tr><td>" . $estudiante['nombre'] . "</td><td>" . $estudiante['grupo'] . "</td><td>" . $estudiante['promedio'] . "</td></tr>";
            }
            $contenido .= "</table>";
            break;
        case 'profesores':
            $contenido .= "<h2>PROFESORES</h2>";
            $contenido .= "<table border='1' cellpadding='5'>";
            $contenido .= "<tr><th>Nombre</th><th>Materia</th><th>Grupos</th><th>Estudiantes</th></tr>";
            foreach ($datos['profesores'] as $profesor) {
                $contenido .= "<tr><td>" . $profesor['nombre'] . "</td><td>" . $profesor['materia'] . "</td><td>" . $profesor['grupos'] . "</td><td>" . $profesor['estudiantes'] . "</td></tr>";
            }
            $contenido .= "</table>";
            break;
        case 'grupos':
            $contenido .= "<h2>GRUPOS</h2>";
            $contenido .= "<table border='1' cellpadding='5'>";
            $contenido .= "<tr><th>Grupo</th><th>Estudiantes</th><th>Promedio</th><th>Aprobados</th></tr>";
            foreach ($datos['grupos'] as $grupo) {
                $contenido .= "<tr><td>" . $grupo['grupo'] . "</td><td>" . $grupo['estudiantes'] . "</td><td>" . $grupo['promedio'] . "</td><td>" . $grupo['aprobados'] . "</td></tr>";
            }
            $contenido .= "</table>";
            break;
        case 'materias':
            $contenido .= "<h2>MATERIAS</h2>";
            $contenido .= "<table border='1' cellpadding='5'>";
            $contenido .= "<tr><th>Materia</th><th>Código</th><th>Promedio</th><th>% Aprobados</th></tr>";
            foreach ($datos['materias'] as $materia) {
                $contenido .= "<tr><td>" . $materia['materia'] . "</td><td>" . $materia['codigo'] . "</td><td>" . $materia['promedio'] . "</td><td>" . $materia['aprobados'] . "</td></tr>";
            }
            $contenido .= "</table>";
            break;
        case 'generales':
            $contenido .= "<h2>REPORTE GENERAL</h2>";
            $contenido .= "<table border='1' cellpadding='5'>";
            $contenido .= "<tr><th>Métrica</th><th>Valor</th></tr>";
            $contenido .= "<tr><td>Total Estudiantes</td><td>" . $datos['generales']['total_estudiantes'] . "</td></tr>";
            $contenido .= "<tr><td>Total Profesores</td><td>" . $datos['generales']['total_profesores'] . "</td></tr>";
            $contenido .= "<tr><td>Total Grupos</td><td>" . $datos['generales']['total_grupos'] . "</td></tr>";
            $contenido .= "<tr><td>Total Materias</td><td>" . $datos['generales']['total_materias'] . "</td></tr>";
            $contenido .= "<tr><td>Promedio General</td><td>" . $datos['generales']['promedio_general'] . "</td></tr>";
            $contenido .= "<tr><td>% Aprobación</td><td>" . $datos['generales']['porcentaje_aprobacion'] . "%</td></tr>";
            $contenido .= "</table>";
            break;
        case 'busqueda':
            $contenido .= "<h2>RESULTADOS DE BÚSQUEDA</h2>";
            $contenido .= "<p><strong>Término buscado:</strong> " . htmlspecialchars($datos['termino']) . "</p>";
            $contenido .= "<p><strong>Total de resultados:</strong> " . count($datos['busqueda']) . "</p>";
            
            if (!empty($datos['busqueda'])) {
                // Agrupar resultados por módulo
                $agrupados = [];
                foreach ($datos['busqueda'] as $item) {
                    $agrupados[$item['modulo']][] = $item;
                }
                
                foreach ($agrupados as $modulo => $items) {
                    $titulo = ucfirst($modulo);
                    $contenido .= "<h3>" . $titulo . " (" . count($items) . ")</h3>";
                    $contenido .= "<table border='1' cellpadding='5'>";
                    
                    if ($modulo === 'estudiantes') {
                        $contenido .= "<tr><th>Nombre</th><th>Grupo</th><th>Promedio</th><th>Email</th></tr>";
                        foreach ($items as $item) {
                            $contenido .= "<tr><td>" . htmlspecialchars($item['nombre']) . "</td><td>" . htmlspecialchars($item['grupo']) . "</td><td>" . $item['promedio'] . "</td><td>" . htmlspecialchars($item['email']) . "</td></tr>";
                        }
                    } elseif ($modulo === 'profesores') {
                        $contenido .= "<tr><th>Nombre</th><th>Materia</th><th>Grupos</th><th>Estudiantes</th><th>Email</th></tr>";
                        foreach ($items as $item) {
                            $contenido .= "<tr><td>" . htmlspecialchars($item['nombre']) . "</td><td>" . htmlspecialchars($item['materia']) . "</td><td>" . $item['grupos'] . "</td><td>" . $item['estudiantes'] . "</td><td>" . htmlspecialchars($item['email']) . "</td></tr>";
                        }
                    } elseif ($modulo === 'materias') {
                        $contenido .= "<tr><th>Nombre</th><th>Código</th><th>Nivel</th><th>Créditos</th></tr>";
                        foreach ($items as $item) {
                            $contenido .= "<tr><td>" . htmlspecialchars($item['nombre']) . "</td><td>" . htmlspecialchars($item['codigo']) . "</td><td>" . htmlspecialchars($item['nivel']) . "</td><td>" . $item['creditos'] . "</td></tr>";
                        }
                    } elseif ($modulo === 'grupos') {
                        $contenido .= "<tr><th>Nombre</th><th>Nivel</th><th>Estudiantes</th><th>Profesor</th></tr>";
                        foreach ($items as $item) {
                            $contenido .= "<tr><td>" . htmlspecialchars($item['nombre']) . "</td><td>" . htmlspecialchars($item['nivel']) . "</td><td>" . $item['estudiantes'] . "</td><td>" . htmlspecialchars($item['profesor']) . "</td></tr>";
                        }
                    }
                    
                    $contenido .= "</table>";
                }
            } else {
                $contenido .= "<p>No se encontraron resultados para la búsqueda.</p>";
            }
            break;
    }
    
    $contenido .= "<div class='footer'>";
    $contenido .= "<p><strong>Generado por Sistema de Control Escolar</strong></p>";
    $contenido .= "<p>Fecha de generación: " . date('d/m/Y H:i:s') . "</p>";
    $contenido .= "<p class='no-print'>Para imprimir este reporte, use Ctrl+P o el menú de impresión de su navegador</p>";
    $contenido .= "</div>";
    $contenido .= "</body></html>";
    return $contenido;
}

function generarContenidoNotepad($reporte, $datos) {
    $contenido = "╔══════════════════════════════════════════════════════════╗\n";
    $contenido .= "║                SISTEMA DE CONTROL ESCOLAR                ║\n";
    $contenido .= "║                                                          ║\n";
    $contenido .= "║           REPORTE DE " . strtoupper($reporte) . str_repeat(" ", 20 - strlen($reporte)) . "           ║\n";
    $contenido .= "║                                                          ║\n";
    $contenido .= "║  Fecha: " . date('d/m/Y H:i:s') . str_repeat(" ", 30 - strlen(date('d/m/Y H:i:s'))) . "  ║\n";
    $contenido .= "╚══════════════════════════════════════════════════════════╝\n\n";
    
    switch ($reporte) {
        case 'estudiantes':
            $contenido .= "ESTUDIANTES\n";
            $contenido .= str_repeat("-", 30) . "\n";
            foreach ($datos['estudiantes'] as $estudiante) {
                $contenido .= "Nombre: " . $estudiante['nombre'] . "\n";
                $contenido .= "Grupo: " . $estudiante['grupo'] . "\n";
                $contenido .= "Promedio: " . $estudiante['promedio'] . "\n\n";
            }
            break;
        case 'profesores':
            $contenido .= "PROFESORES\n";
            $contenido .= str_repeat("-", 30) . "\n";
            foreach ($datos['profesores'] as $profesor) {
                $contenido .= "Nombre: " . $profesor['nombre'] . "\n";
                $contenido .= "Materia: " . $profesor['materia'] . "\n";
                $contenido .= "Grupos: " . $profesor['grupos'] . "\n";
                $contenido .= "Estudiantes: " . $profesor['estudiantes'] . "\n\n";
            }
            break;
        case 'grupos':
            $contenido .= "GRUPOS\n";
            $contenido .= str_repeat("-", 30) . "\n";
            foreach ($datos['grupos'] as $grupo) {
                $contenido .= "Grupo: " . $grupo['grupo'] . "\n";
                $contenido .= "Estudiantes: " . $grupo['estudiantes'] . "\n";
                $contenido .= "Promedio: " . $grupo['promedio'] . "\n";
                $contenido .= "Aprobados: " . $grupo['aprobados'] . "\n\n";
            }
            break;
        case 'materias':
            $contenido .= "MATERIAS\n";
            $contenido .= str_repeat("-", 30) . "\n";
            foreach ($datos['materias'] as $materia) {
                $contenido .= "Materia: " . $materia['materia'] . "\n";
                $contenido .= "Código: " . $materia['codigo'] . "\n";
                $contenido .= "Promedio: " . $materia['promedio'] . "\n";
                $contenido .= "% Aprobados: " . $materia['aprobados'] . "\n\n";
            }
            break;
        case 'generales':
            $contenido .= "ESTADÍSTICAS GENERALES\n";
            $contenido .= str_repeat("-", 30) . "\n";
            $contenido .= "Total Estudiantes: " . $datos['generales']['total_estudiantes'] . "\n";
            $contenido .= "Total Profesores: " . $datos['generales']['total_profesores'] . "\n";
            $contenido .= "Total Grupos: " . $datos['generales']['total_grupos'] . "\n";
            $contenido .= "Total Materias: " . $datos['generales']['total_materias'] . "\n";
            $contenido .= "Promedio General: " . $datos['generales']['promedio_general'] . "\n";
            $contenido .= "% Aprobación: " . $datos['generales']['porcentaje_aprobacion'] . "%\n";
            break;
        case 'busqueda':
            $contenido .= "RESULTADOS DE BÚSQUEDA\n";
            $contenido .= str_repeat("-", 30) . "\n";
            $contenido .= "Término buscado: " . $datos['termino'] . "\n";
            $contenido .= "Total de resultados: " . count($datos['busqueda']) . "\n\n";
            
            if (!empty($datos['busqueda'])) {
                // Agrupar resultados por módulo
                $agrupados = [];
                foreach ($datos['busqueda'] as $item) {
                    $agrupados[$item['modulo']][] = $item;
                }
                
                foreach ($agrupados as $modulo => $items) {
                    $contenido .= strtoupper($modulo) . " (" . count($items) . ")\n";
                    $contenido .= str_repeat("-", 20) . "\n";
                    
                    foreach ($items as $item) {
                        if ($modulo === 'estudiantes') {
                            $contenido .= "Nombre: " . $item['nombre'] . "\n";
                            $contenido .= "Grupo: " . $item['grupo'] . "\n";
                            $contenido .= "Promedio: " . $item['promedio'] . "\n";
                            $contenido .= "Email: " . $item['email'] . "\n";
                        } elseif ($modulo === 'profesores') {
                            $contenido .= "Nombre: " . $item['nombre'] . "\n";
                            $contenido .= "Materia: " . $item['materia'] . "\n";
                            $contenido .= "Grupos: " . $item['grupos'] . "\n";
                            $contenido .= "Estudiantes: " . $item['estudiantes'] . "\n";
                            $contenido .= "Email: " . $item['email'] . "\n";
                        } elseif ($modulo === 'materias') {
                            $contenido .= "Nombre: " . $item['nombre'] . "\n";
                            $contenido .= "Código: " . $item['codigo'] . "\n";
                            $contenido .= "Nivel: " . $item['nivel'] . "\n";
                            $contenido .= "Créditos: " . $item['creditos'] . "\n";
                        } elseif ($modulo === 'grupos') {
                            $contenido .= "Nombre: " . $item['nombre'] . "\n";
                            $contenido .= "Nivel: " . $item['nivel'] . "\n";
                            $contenido .= "Estudiantes: " . $item['estudiantes'] . "\n";
                            $contenido .= "Profesor: " . $item['profesor'] . "\n";
                        }
                        $contenido .= "\n";
                    }
                }
            } else {
                $contenido .= "No se encontraron resultados para la búsqueda.\n";
            }
            break;
    }
    
    $contenido .= "\n" . str_repeat("=", 50) . "\n";
    $contenido .= "Generado por Sistema de Control Escolar\n";
    $contenido .= "Fecha: " . date('d/m/Y H:i:s') . "\n";
    $contenido .= str_repeat("=", 50) . "\n";
    
    return $contenido;
}
?>

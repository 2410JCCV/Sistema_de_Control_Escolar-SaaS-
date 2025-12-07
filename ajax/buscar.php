<?php
/**
 * AJAX Search Handler
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../config/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Configurar headers para AJAX
header('Content-Type: application/json; charset=utf-8');

// Incluir funciones de búsqueda
require_once __DIR__ . '/../includes/search.php';

$accion = isset($_GET['accion']) ? sanitize($_GET['accion']) : '';
$termino = isset($_GET['termino']) ? sanitize($_GET['termino']) : '';
$modulo = isset($_GET['modulo']) ? sanitize($_GET['modulo']) : 'todos';

try {
    switch ($accion) {
        case 'buscar':
            if (empty($termino)) {
                echo json_encode(['resultados' => []]);
                exit;
            }
            
            $resultados = buscarGlobal($termino, $modulo);
            echo json_encode([
                'success' => true,
                'resultados' => $resultados,
                'total' => count($resultados)
            ]);
            break;
            
        case 'sugerencias':
            $sugerencias = obtenerSugerencias($termino);
            echo json_encode([
                'success' => true,
                'sugerencias' => $sugerencias
            ]);
            break;
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>

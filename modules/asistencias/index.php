<?php
/**
 * Módulo de Asistencias - Redirección
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    redirect('index.php');
}

// Redirigir a listar
header('Location: listar.php');
exit;




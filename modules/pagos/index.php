<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

header('Location: listar.php');
exit;




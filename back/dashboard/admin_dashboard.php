<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
?>
<h2>Dashboard Administrador</h2>
<div id="contenido-admin"></div>
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once '../connection.php';

$sql = "SELECT id, pregunta FROM preguntes";
$result = $conn->query($sql);

if ($result === false) {
    echo json_encode(['error' => 'Error en la consulta: ' . $conn->error]);
    exit;
}

$preguntes = [];
while ($row = $result->fetch_assoc()) {
    $preguntes[] = $row;
}

echo json_encode($preguntes);

$conn->close();
?>
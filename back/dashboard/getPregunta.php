<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once '../connection.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'ID de pregunta no proporcionado']);
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT p.id, p.pregunta, r.id as resposta_id, r.resposta, r.correcta, r.imatge
        FROM preguntes p 
        LEFT JOIN respostes r ON p.id = r.pregunta_id 
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$pregunta = null;
$respostes = [];

while ($row = $result->fetch_assoc()) {
    if ($pregunta === null) {
        $pregunta = [
            'id' => $row['id'],
            'pregunta' => $row['pregunta'],
            'respostes' => []
        ];
    }
    $pregunta['respostes'][] = [
        'id' => $row['resposta_id'],
        'resposta' => $row['resposta'],
        'correcta' => (bool)$row['correcta'],
        'imatge' => $row['imatge']
    ];
}

if ($pregunta === null) {
    echo json_encode(['error' => 'Pregunta no trobada']);
} else {
    echo json_encode($pregunta);
}

$conn->close();
?>
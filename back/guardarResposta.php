<?php
session_start();
require_once("connection.php");

$pregunta_id = $_POST['pregunta_id'];
$resposta_index = $_POST['resposta_index'];

// Comprobar si ya existe una respuesta para esta pregunta
if (!isset($_SESSION['respostes'])) {
    $_SESSION['respostes'] = [];
}

$preguntaContestada = false;
foreach ($_SESSION['respostes'] as $resposta) {
    if ($resposta['pregunta_id'] == $pregunta_id) {
        $preguntaContestada = true;
        break;
    }
}

if ($preguntaContestada) {
    echo json_encode(['success' => false, 'message' => 'Aquesta pregunta ja ha estat contestada']);
    exit;
}

// Obtener la información de la respuesta
$sql = "SELECT r.resposta, r.correcta, p.pregunta 
        FROM respostes r 
        JOIN preguntes p ON r.pregunta_id = p.id 
        WHERE r.pregunta_id = ? AND r.id = (
            SELECT id FROM respostes 
            WHERE pregunta_id = ? 
            ORDER BY id 
            LIMIT 1 OFFSET ?
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $pregunta_id, $pregunta_id, $resposta_index);
$stmt->execute();
$result = $stmt->get_result();
$resposta = $result->fetch_assoc();

$_SESSION['respostes'][] = [
    'pregunta_id' => $pregunta_id,
    'pregunta' => $resposta['pregunta'],
    'resposta' => $resposta['resposta'],
    'correcta' => $resposta['correcta'] == 1
];

echo json_encode(['success' => true]);
?>
<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../connection.php';

$pregunta = $_POST['pregunta'];
$respostes = [
    $_POST['resposta1'],
    $_POST['resposta2'],
    $_POST['resposta3'],
    $_POST['resposta4']
];
$correcta = (int)$_POST['correcta'];

$conn->begin_transaction();

try {
    $sql = "INSERT INTO preguntes (pregunta) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pregunta);
    $stmt->execute();
    $pregunta_id = $conn->insert_id;

    foreach ($respostes as $index => $resposta) {
        $sql = "INSERT INTO respostes (pregunta_id, resposta, correcta) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $es_correcta = ($index == $correcta) ? 1 : 0;
        $stmt->bind_param("isi", $pregunta_id, $resposta, $es_correcta);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
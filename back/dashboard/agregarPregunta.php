<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../connection.php';

header('Content-Type: application/json');

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
    // Insertar la pregunta
    $sql = "INSERT INTO preguntes (pregunta) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pregunta);
    $stmt->execute();
    $pregunta_id = $conn->insert_id;

    // Insertar las respuestas
    $sql = "INSERT INTO respostes (pregunta_id, resposta, correcta, imatge) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    for ($i = 0; $i < 4; $i++) {
        $es_correcta = ($i == $correcta) ? 1 : 0;
        $imatge_url = null;

        // Manejar la subida de la imagen
        if (isset($_FILES["imatge".($i+1)]) && $_FILES["imatge".($i+1)]['error'] == 0) {
            $target_dir = "../../front/img/";
            $file_extension = pathinfo($_FILES["imatge".($i+1)]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["imatge".($i+1)]["tmp_name"], $target_file)) {
                $imatge_url = "http://localhost/decero/front/img/" . $new_filename;
            } else {
                throw new Exception("Error al pujar la imatge de la resposta " . ($i+1));
            }
        }

        $stmt->bind_param("isis", $pregunta_id, $respostes[$i], $es_correcta, $imatge_url);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Pregunta afegida correctament']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../connection.php';

$id = $_GET['id'];

$conn->begin_transaction();

try {
    // Primero, obtener las rutas de las imágenes
    $sql = "SELECT imatge FROM respostes WHERE pregunta_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $imatges = $result->fetch_all(MYSQLI_ASSOC);

    // Eliminar las respuestas
    $sql = "DELETE FROM respostes WHERE pregunta_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Eliminar la pregunta
    $sql = "DELETE FROM preguntes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $conn->commit();

    // Eliminar las imágenes físicamente
    foreach ($imatges as $imatge) {
        $ruta_imatge = str_replace("http://localhost/decero/front/img/", "../../front/img/", $imatge['imatge']);
        if (file_exists($ruta_imatge)) {
            unlink($ruta_imatge);
        }
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

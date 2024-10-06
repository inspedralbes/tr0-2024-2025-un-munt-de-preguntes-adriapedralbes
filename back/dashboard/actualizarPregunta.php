<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../connection.php';

error_log('PHP se está ejecutando como usuario: ' . posix_getpwuid(posix_geteuid())['name']);

// Activar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id = $_POST['id'];
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
    // Actualizar pregunta
    $sql = "UPDATE preguntes SET pregunta = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $pregunta, $id);
    $stmt->execute();

    // Actualizar respuestas
    $sql = "UPDATE respostes SET resposta = ?, correcta = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    for ($i = 1; $i <= 4; $i++) {
        $resposta = $_POST["resposta$i"];
        $resposta_id = $_POST["resposta{$i}_id"];
        $es_correcta = ($i - 1 == $correcta) ? 1 : 0;
        $stmt->bind_param("sii", $resposta, $es_correcta, $resposta_id);
        $stmt->execute();

        // Manejar la actualización de la imagen
        if (isset($_FILES["imatge$i"]) && $_FILES["imatge$i"]['size'] > 0) {
            $target_dir = "../../front/img/";
            $file_extension = pathinfo($_FILES["imatge$i"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Verificar si el directorio existe y tiene permisos de escritura
            if (!is_dir($target_dir) || !is_writable($target_dir)) {
                throw new Exception("El directorio de destino no existe o no tiene permisos de escritura");
            }
            
            if (move_uploaded_file($_FILES["imatge$i"]["tmp_name"], $target_file)) {
                $image_url = "http://localhost/decero/front/img/" . $new_filename;
                $sql_update_image = "UPDATE respostes SET imatge = ? WHERE id = ?";
                $stmt_image = $conn->prepare($sql_update_image);
                $stmt_image->bind_param("si", $image_url, $resposta_id);
                $stmt_image->execute();
            } else {
                $upload_error = error_get_last();
                throw new Exception("Error al pujar la imatge de la resposta $i: " . ($upload_error['message'] ?? 'Error desconocido'));
            }
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
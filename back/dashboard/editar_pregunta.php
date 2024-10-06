<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require_once 'connection.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pregunta = $_POST['pregunta'];
    $respostes = $_POST['respostes'];
    $correcta = $_POST['correcta'];

    $sql = "UPDATE preguntes SET pregunta = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $pregunta, $id);
    $stmt->execute();

    $sql = "DELETE FROM respostes WHERE pregunta_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    foreach ($respostes as $index => $resposta) {
        $es_correcta = ($index == $correcta) ? 1 : 0;
        $sql = "INSERT INTO respostes (pregunta_id, resposta, correcta) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $id, $resposta, $es_correcta);
        $stmt->execute();
    }

    header('Location: listar_preguntes.php');
    exit;
}

$sql = "SELECT * FROM preguntes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pregunta = $result->fetch_assoc();

$sql = "SELECT * FROM respostes WHERE pregunta_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$respostes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pregunta</title>
</head>
<body>
    <h2>Editar Pregunta</h2>
    <form method="post">
        <label for="pregunta">Pregunta:</label>
        <input type="text" name="pregunta" value="<?php echo $pregunta['pregunta']; ?>" required><br>

        <?php foreach ($respostes as $index => $resposta): ?>
            <label for="respostes[<?php echo $index; ?>]">Resposta <?php echo $index+1; ?>:</label>
            <input type="text" name="respostes[]" value="<?php echo $resposta['resposta']; ?>" required>
            <input type="radio" name="correcta" value="<?php echo $index; ?>" <?php echo $resposta['correcta'] ? 'checked' : ''; ?> required> Correcta<br>
        <?php endforeach; ?>

        <input type="submit" value="Actualitzar Pregunta">
    </form>
    <a href="listar_preguntes.php">Tornar al Llistat</a>
</body>
</html>
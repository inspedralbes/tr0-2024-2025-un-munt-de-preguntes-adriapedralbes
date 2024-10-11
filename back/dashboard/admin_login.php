<?php
session_start();
require_once '../connection.php';

// Activar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM admins WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin'] = true;
                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Usuari o contrasenya incorrectes']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
    }
    exit;
}

// Si es una solicitud GET, devolvemos el formulario HTML
?>
<h2>Accés Administrador</h2>
<form id="admin-login-form">
    <input type="text" name="username" placeholder="Usuari" required><br>
    <input type="password" name="password" placeholder="Contrasenya" required><br>
    <input type="submit" value="Accedir">
</form>
<button onclick="tornarIniciAdmin()">Tornar</button>

<script>
function tornarIniciAdmin() {
    document.getElementById('admin-panel').style.display = 'none';
    document.getElementById('pagina-inicial').style.display = 'block';
}
</script>
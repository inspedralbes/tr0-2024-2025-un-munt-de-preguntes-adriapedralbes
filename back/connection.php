<?php
require_once 'config.php';

$conn = mysqli_connect($db_server, $db_username, $db_password, $db_name);

if ($conn === false) {
    die("ERROR: No se pudo conectar. " . mysqli_connect_error());
}

// Establecer el conjunto de caracteres de la conexión a utf8mb4
if (!mysqli_set_charset($conn, "utf8mb4")) {
    die("Error al establecer el conjunto de caracteres utf8mb4: " . mysqli_error($conn));
}
?>
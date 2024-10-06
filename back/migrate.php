<?php
require_once("connection.php");
require_once("config.php");

$json = file_get_contents('preguntes_respostes.json');
$data = json_decode($json, true);

if (!$conn) {
    die("La conexión a la base de datos falló: " . mysqli_connect_error());
}

if ($data === null) {
    die("Error al llegir o descodificar el fitxer JSON");
}

// Crear la tabla 'preguntes'
$sql_preguntes = "CREATE TABLE IF NOT EXISTS preguntes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta TEXT NOT NULL
)";

if (!mysqli_query($conn, $sql_preguntes)) {
    die("Error al crear la tabla 'preguntes': " . mysqli_error($conn));
}

// Crear la tabla 'respostes'
$sql_respostes = "CREATE TABLE IF NOT EXISTS respostes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    resposta TEXT NOT NULL,
    correcta BOOLEAN NOT NULL,
    imatge VARCHAR(255),
    FOREIGN KEY (pregunta_id) REFERENCES preguntes(id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $sql_respostes)) {
    die("Error al crear la tabla 'respostes': " . mysqli_error($conn));
}

// Crear la tabla 'admins'
$sql_admins = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";

if (!mysqli_query($conn, $sql_admins)) {
    die("Error al crear la tabla 'admins': " . mysqli_error($conn));
}

// Insertar datos del JSON en las tablas
foreach ($data['preguntes'] as $pregunta) {
    $sql_insert_pregunta = "INSERT INTO preguntes (id, pregunta) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql_insert_pregunta);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "is", $pregunta['id'], $pregunta['pregunta']);
    if (!mysqli_stmt_execute($stmt)) {
        die("Error al insertar pregunta: " . mysqli_stmt_error($stmt));
    }

    foreach ($pregunta['respostes'] as $resposta) {
        $sql_insert_resposta = "INSERT INTO respostes (pregunta_id, resposta, correcta, imatge) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql_insert_resposta);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . mysqli_error($conn));
        }
        
        $correcta = $resposta['correcta'] ? 1 : 0;
        
        mysqli_stmt_bind_param($stmt, "isis", $pregunta['id'], $resposta['resposta'], $correcta, $resposta['imatge']);
        if (!mysqli_stmt_execute($stmt)) {
            die("Error al insertar respuesta: " . mysqli_stmt_error($stmt));
        }
    }
}

// Insertar usuario administrador por defecto
$admin_username = 'adrianquiz';
$admin_password = password_hash('1234', PASSWORD_DEFAULT);

$sql_insert_admin = "INSERT INTO admins (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password)";
$stmt = mysqli_prepare($conn, $sql_insert_admin);
if (!$stmt) {
    die("Error en la preparación de la consulta para insertar admin: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "ss", $admin_username, $admin_password);
if (!mysqli_stmt_execute($stmt)) {
    die("Error al insertar administrador: " . mysqli_stmt_error($stmt));
}

echo "Migración completada con éxito. Usuario admin creado o actualizado.";

// Cerrar la conexión
mysqli_close($conn);
?>
<?php

require_once("connection.php");
require_once("config.php");

// Verificar la conexión
if (!$conn) {
    die(json_encode([
        "error" => true,
        "message" => "La conexión a la base de datos falló: " . mysqli_connect_error()
    ]));
}

// Obtener el número de preguntas solicitadas
$num_preguntes = isset($_GET['num_preguntes']) ? intval($_GET['num_preguntes']) : 10;
$num_preguntes = max(1, min($num_preguntes, 10)); // Asegurarse de que esté entre 1 y 10

// Paso 1: Seleccionar preguntas aleatorias
$sql_preguntes = "SELECT id, pregunta FROM preguntes ORDER BY RAND() LIMIT $num_preguntes";
$result_preguntes = mysqli_query($conn, $sql_preguntes);

if (!$result_preguntes) {
    die(json_encode([
        "error" => true,
        "message" => "Error al seleccionar preguntas: " . mysqli_error($conn)
    ]));
}

$preguntes = [];
$pregunta_ids = [];

while ($row = mysqli_fetch_assoc($result_preguntes)) {
    $preguntes[] = [
        "id" => $row['id'],
        "pregunta" => $row['pregunta'],
        "respostes" => []
    ];
    $pregunta_ids[] = $row['id'];
}

// Paso 2: Obtener respuestas para las preguntas seleccionadas
if (!empty($pregunta_ids)) {
    $ids_string = implode(',', $pregunta_ids);
    $sql_respostes = "SELECT pregunta_id, id, resposta, correcta, imatge 
                      FROM respostes 
                      WHERE pregunta_id IN ($ids_string)";
    
    $result_respostes = mysqli_query($conn, $sql_respostes);
    
    if (!$result_respostes) {
        die(json_encode([
            "error" => true,
            "message" => "Error al obtener respuestas: " . mysqli_error($conn)
        ]));
    }
    
    while ($row = mysqli_fetch_assoc($result_respostes)) {
        foreach ($preguntes as &$pregunta) {
            if ($pregunta['id'] == $row['pregunta_id']) {
                $pregunta['respostes'][] = [
                    "id" => $row['id'],
                    "resposta" => $row['resposta'],
                    "correcta" => $row['correcta'] == 1,
                    "imatge" => $row['imatge']
                ];
                break;
            }
        }
    }
}

// Cerrar la conexión
mysqli_close($conn);

// Configurar cabeceras para JSON
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Devolver las preguntas y respuestas como JSON
echo json_encode($preguntes);
?>
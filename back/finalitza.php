<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['respostes']) || !isset($_SESSION['num_preguntes'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No hi ha dades del quiz per comprovar'
    ]);
    exit;
}

$respostesCorrectes = 0;
$totalPreguntes = $_SESSION['num_preguntes'];

foreach ($_SESSION['respostes'] as $resposta) {
    if ($resposta['correcta']) {
        $respostesCorrectes++;
    }
}

$resultat = [
    'success' => true,
    'respostesCorrectes' => $respostesCorrectes,
    'totalPreguntes' => $totalPreguntes,
    'percentatge' => ($totalPreguntes > 0) ? ($respostesCorrectes / $totalPreguntes) * 100 : 0
];

echo json_encode($resultat);

// Limpiar la sesión después de enviar los resultados
session_destroy();
?>
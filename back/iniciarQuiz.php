<?php
session_start();
header('Content-Type: application/json');

$num_preguntes = isset($_POST['num_preguntes']) ? intval($_POST['num_preguntes']) : 0;
$nom_usuari = isset($_POST['nom_usuari']) ? $_POST['nom_usuari'] : '';

if ($num_preguntes <= 0 || $num_preguntes > 10 || empty($nom_usuari)) {
    echo json_encode([
        'success' => false,
        'message' => 'Dades del quiz invàlides'
    ]);
    exit;
}

$_SESSION['num_preguntes'] = $num_preguntes;
$_SESSION['nom_usuari'] = $nom_usuari;
$_SESSION['respostes'] = [];

echo json_encode([
    'success' => true,
    'message' => 'Quiz iniciat correctament'
]);
?>
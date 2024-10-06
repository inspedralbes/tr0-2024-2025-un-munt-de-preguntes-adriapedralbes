<?php
// runMigration.php
header('Content-Type: application/json');

// Verificar si la migración ya se ha realizado
$migration_flag_file = __DIR__ . '/migration_completed.flag';

if (file_exists($migration_flag_file)) {
    echo json_encode(['success' => true, 'message' => 'La migración ya se ha realizado previamente.']);
    exit;
}

// Ejecutar el script de migración existente
ob_start();
include 'migrate.php';
$output = ob_get_clean();

// Crear el archivo flag para indicar que la migración se ha completado
file_put_contents($migration_flag_file, date('Y-m-d H:i:s'));

echo json_encode(['success' => true, 'message' => 'Migración completada con éxito.', 'output' => $output]);
?>
<?php
require '../config.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !is_array($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO items (box_id, name, description) VALUES (:box_id, :name, :description)");

    foreach ($data as $item) {
        $stmt->execute([
            'box_id' => $item['box_id'],
            'name' => $item['name'],
            'description' => $item['description'] ?? ''
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'count' => count($data)]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

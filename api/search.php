<?php
require '../config.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
if (strlen($q) < 2) { echo json_encode([]); exit; }

$sql = "
    SELECT 'box' as type, qr_code as title, l.name as subtitle, qr_code as id 
    FROM boxes b JOIN locations l ON b.location_id = l.id
    WHERE qr_code LIKE :q OR notes LIKE :q OR ai_description LIKE :q
    UNION ALL
    SELECT 'item' as type, i.name as title, CONCAT('In: ', b.qr_code) as subtitle, b.qr_code as id
    FROM items i JOIN boxes b ON i.box_id = b.id
    WHERE i.name LIKE :q OR i.description LIKE :q
    LIMIT 15
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['q' => "%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>

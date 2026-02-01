<?php
require '../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$box_id = $input['box_id'] ?? 0;

if (!$box_id) {
    echo json_encode(['error' => 'No box ID provided']);
    exit;
}

// 1. Fetch Image Path
$stmt = $pdo->prepare("SELECT image_path FROM boxes WHERE id = ?");
$stmt->execute([$box_id]);
$box = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$box || empty($box['image_path'])) {
    echo json_encode(['error' => 'No image found for this box']);
    exit;
}

// 2. Prepare Image
$image_path = '../' . $box['image_path']; // Adjust for api/ folder
if (!file_exists($image_path)) {
    echo json_encode(['error' => 'Image file missing']);
    exit;
}

$image_data = base64_encode(file_get_contents($image_path));
$mime_type = mime_content_type($image_path);

// 3. Call Gemini API
$url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Analyseer deze foto van de inhoud van een verhuisdoos. Geef ALLEEN een kommagescheiden lijst van concrete zelfstandige naamwoorden. Geen volledige zinnen, geen vulwoorden zoals 'diverse', 'en andere', 'waarschijnlijk', 'mogelijk'. Voorbeeld: 'Winterjassen, Sjaals, Mutsen'. Taal: Nederlands. Max 30 woorden."],
                [
                    "inline_data" => [
                        "mime_type" => $mime_type,
                        "data" => $image_data
                    ]
                ]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
    exit;
}
curl_close($ch);

$result = json_decode($response, true);

// 4. Parse Result
try {
    $description = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    // Clean up
    $description = trim($description);
    
    if ($description) {
        // 5. Update Database
        $upd = $pdo->prepare("UPDATE boxes SET ai_description = ? WHERE id = ?");
        $upd->execute([$description, $box_id]);
        
        echo json_encode(['success' => true, 'description' => $description]);
    } else {
        echo json_encode(['error' => 'AI returned no description', 'raw' => $result]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Parsing error', 'raw' => $result]);
}
?>

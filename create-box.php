<?php
require 'config.php';

// Logic First
$stmt = $pdo->query("SELECT * FROM locations ORDER BY name ASC");
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$prefilledQr = $_GET['qr'] ?? '';

try {
    $generatedQr = $prefilledQr ? $prefilledQr : 'BOX-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
} catch (Exception $e) { $generatedQr = 'BOX-000000'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qr_code = trim($_POST['qr_code'] ?? '');
    $location_id = $_POST['location_id'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($qr_code) || empty($location_id)) {
        $error = "Selecteer een locatie.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO boxes (qr_code, location_id, notes) VALUES (:qr, :loc, :notes)");
            $stmt->execute(['qr' => $qr_code, 'loc' => $location_id, 'notes' => $notes]);
            
            // Safe Redirect
            header("Location: box.php?qr=" . urlencode($qr_code));
            exit;
        } catch (PDOException $e) {
            $error = ($e->getCode() == 23000) ? "Deze ID bestaat al." : "Fout: " . $e->getMessage();
        }
    }
}

require 'header.php';
?>

<div class="container">
    <div class="page-header">
        <a href="index.php" class="back-btn"><span>&larr;</span> Annuleren</a>
        <h2 class="page-title">Nieuwe Doos</h2>
    </div>

    <?php if ($error): ?>
        <div class="card" style="background: #FEE2E2; border-color: #EF4444; color: #991B1B; font-weight: 500;">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="card">
            <div class="input-group">
                <label>Doos ID</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="qr_code" value="<?php echo htmlspecialchars($generatedQr); ?>" <?php echo $prefilledQr ? 'readonly' : ''; ?> style="font-family: 'Outfit'; font-weight: 700; letter-spacing: 1px; color: var(--primary); background: var(--bg);">
                    <?php if(!$prefilledQr): ?>
                    <a href="scanner.php" class="btn-text" style="background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0 16px; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                        📷
                    </a>
                    <button type="button" onclick="window.location.reload()" style="padding: 0 20px; border-radius: var(--radius-md); border: 1px solid var(--border); background: var(--surface); cursor: pointer;">
                        ↻
                    </button>
                    <?php endif; ?>
                </div>
                <?php if($prefilledQr): ?>
                    <small style="color: var(--primary); display: block; margin-top: 4px;">✅ Gescannde code geselecteerd</small>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <div class="flex-between" style="margin-bottom: 8px;">
                    <label style="margin:0;">Locatie</label>
                    <a href="locations.php" style="font-size: 0.85rem; color: var(--primary); text-decoration: none;">+ Nieuwe Locatie</a>
                </div>
                <select name="location_id" required style="height: 54px;">
                    <option value="" disabled selected>Kies een plek...</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?php echo $loc['id']; ?>"><?php echo htmlspecialchars($loc['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Notities</label>
                <textarea name="notes" placeholder="Wat ga je erin stoppen?" rows="3"></textarea>
            </div>
        </div>

        <button type="submit" class="btn-primary">Aanmaken</button>
    </form>
</div>
</body>
</html>

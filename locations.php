<?php
require 'config.php';
require 'header.php';

$message = '';
$error = '';

// Handle Location Deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Check usage
    $check = $pdo->prepare("SELECT COUNT(*) FROM boxes WHERE location_id = ?");
    $check->execute([$id]);
    if ($check->fetchColumn() > 0) {
        $error = "Kan locatie niet verwijderen: Er zijn dozen hier opgeslagen.";
    } else {
        $del = $pdo->prepare("DELETE FROM locations WHERE id = ?");
        $del->execute([$id]);
        $message = "Locatie verwijderd.";
    }
}

// Handle Add Location
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_location'])) {
    $name = trim($_POST['new_location']);
    try {
        $stmt = $pdo->prepare("INSERT INTO locations (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);
        $message = "Locatie '$name' toegevoegd.";
    } catch (Exception $e) {
        $error = "Fout bij toevoegen: " . $e->getMessage();
    }
}

// Fetch All
$locations = $pdo->query("
    SELECT l.*, (SELECT COUNT(*) FROM boxes b WHERE b.location_id = l.id) as usage_count 
    FROM locations l 
    ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="page-header">
        <a href="index.php" class="back-btn"><span>&larr;</span> Overzicht</a>
        <h2 class="page-title">Locaties</h2>
    </div>

    <!-- Notifications -->
    <?php if($message): ?>
        <div class="card" style="background: #D1FAE5; border-color: #10B981; color: #065F46; padding: 14px;">
            ✅ <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="card" style="background: #FEE2E2; border-color: #EF4444; color: #991B1B; padding: 14px;">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Add Form -->
    <div class="card">
        <form method="POST" style="display: flex; gap: 10px;">
            <input type="text" name="new_location" placeholder="Nieuwe locatie naam..." required style="margin:0;">
            <button type="submit" class="btn-primary" style="width: auto; padding: 0 24px;">+</button>
        </form>
    </div>

    <!-- List -->
    <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php foreach ($locations as $loc): ?>
            <div class="card flex-between" style="padding: 16px; margin: 0;">
                <div>
                    <div style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($loc['name']); ?></div>
                    <div class="text-muted" style="font-size: 0.85rem;">
                        <?php echo $loc['usage_count']; ?> dozen
                    </div>
                </div>
                
                <?php if($loc['usage_count'] == 0): ?>
                    <a href="?delete=<?php echo $loc['id']; ?>" class="btn-text" style="color: #EF4444; font-size: 1.2rem; padding: 5px 12px;" onclick="return confirm('Verwijderen?')">
                        🗑️
                    </a>
                <?php else: ?>
                    <span class="text-muted" style="opacity: 0.5; padding: 5px 12px;">🔒</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>

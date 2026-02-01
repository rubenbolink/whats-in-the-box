<?php
require 'config.php';

$qr = $_GET['qr'] ?? '';
$box = null;
$items = [];

if ($qr) {
    // 1. Fetch Box Data
    $stmt = $pdo->prepare("SELECT b.id, b.qr_code, b.notes, b.image_path, b.ai_description, l.name as location_name FROM boxes b JOIN locations l ON b.location_id = l.id WHERE b.qr_code = :qr");
    $stmt->execute(['qr' => $qr]);
    $box = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Handle Logic (Redirects)
    if ($box) {
        // Handle Item Add
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_name'])) {
            $insert = $pdo->prepare("INSERT INTO items (box_id, name, description) VALUES (?, ?, ?)");
            $insert->execute([$box['id'], $_POST['item_name'], $_POST['item_desc'] ?? '']);
            header("Location: box.php?qr=" . urlencode($qr));
            exit;
        }

        // Handle Photo Upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['box_image'])) {
            $file = $_FILES['box_image'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $filename = 'box_' . $box['id'] . '_' . time() . '.' . $ext;
                    $target = 'assets/uploads/' . $filename;
                    if(move_uploaded_file($file['tmp_name'], $target)) {
                        $upd = $pdo->prepare("UPDATE boxes SET image_path = ? WHERE id = ?");
                        $upd->execute([$target, $box['id']]);
                        header("Location: box.php?qr=" . urlencode($qr));
                        exit;
                    }
                }
            }
        }
        
        // Fetch Items for Display
        $stmtI = $pdo->prepare("SELECT * FROM items WHERE box_id = ? ORDER BY id DESC");
        $stmtI->execute([$box['id']]);
        $items = $stmtI->fetchAll(PDO::FETCH_ASSOC);
    }
}

// 3. Output HTML
require 'header.php';
?>

<div class="container">
    <div class="page-header">
        <a href="index.php" class="back-btn"><span>&larr;</span> Overzicht</a>
    </div>

    <?php if (!$box): ?>
        <div class="card text-center" style="padding: 60px 20px;">
            <div style="font-size: 3rem; margin-bottom: 20px;">🔍</div>
            <h3 style="margin: 0 0 10px 0;">Onbekende Doos</h3>
            <p class="text-muted">ID: <?php echo htmlspecialchars($qr); ?></p>
            <a href="create-box.php?qr=<?php echo urlencode($qr); ?>" class="btn-primary" style="margin-top: 20px;">Deze Doos Aanmaken</a>
        </div>
    <?php else: ?>
        <!-- Hero Card -->
        <div class="card" style="background: linear-gradient(135deg, var(--surface) 0%, var(--bg) 100%); border: 1px solid var(--border); overflow: hidden; padding: 0;">
            <!-- Box Image Area -->
            <div style="position: relative; height: 200px; background: #000; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                <?php if(!empty($box['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($box['image_path']); ?>" alt="Box Content" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="color: rgba(255,255,255,0.2); font-size: 4rem;">📷</div>
                    <form method="POST" enctype="multipart/form-data" id="photoForm" style="position: absolute; bottom: 10px; right: 10px;">
                        <label for="camInput" class="btn-text" style="background: rgba(0,0,0,0.6); color: white; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(4px);">
                            + Foto
                        </label>
                        <input type="file" name="box_image" id="camInput" accept="image/*" capture="environment" style="display: none;" onchange="this.form.submit()">
                    </form>
                <?php endif; ?>
                
                <?php if(!empty($box['image_path'])): ?>
                    <!-- Retake button tiny corner -->
                    <form method="POST" enctype="multipart/form-data" id="photoFormRetake" style="position: absolute; top: 10px; right: 10px;">
                        <label for="camInputRetake" style="font-size: 1.2rem; background: rgba(0,0,0,0.5); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white;">
                            📷
                        </label>
                        <input type="file" name="box_image" id="camInputRetake" accept="image/*" capture="environment" style="display: none;" onchange="this.form.submit()">
                    </form>
                <?php endif; ?>
            </div>

            <div style="padding: 20px;">
                <div class="flex-between" style="align-items: flex-start;">
                    <div>
                        <h1 style="font-family: 'Outfit'; font-size: 2.5rem; margin: 0; line-height: 1; color: var(--primary);">
                            <?php echo htmlspecialchars($box['qr_code']); ?>
                        </h1>
                        <div style="font-size: 1.1rem; margin-top: 8px; font-weight: 500;">
                            📍 <?php echo htmlspecialchars($box['location_name']); ?>
                        </div>
                    </div>
                </div>
                <?php if($box['notes']): ?>
                    <div style="margin-top: 20px; padding: 16px; background: rgba(0,0,0,0.03); border-radius: var(--radius-md); font-style: italic; color: var(--text-sub);">
                        "<?php echo htmlspecialchars($box['notes']); ?>"
                    </div>
                <?php endif; ?>
                
                 <!-- AI Section -->
                 <div style="margin-top: 16px; display: flex; gap: 8px; align-items: center;">
                    <?php if($box['image_path']): ?>
                        <button onclick="analyzeImage()" class="btn-text" style="font-size: 0.9rem; border: 1px solid var(--accent); color: var(--accent);">
                            ✨ AI Analyseren
                        </button>
                    <?php endif; ?>
                 </div>
                 <div id="aiResult" style="margin-top: 10px; font-size: 0.9rem; color: var(--text-sub); display: none;">
                    <i>AI is aan het kijken...</i>
                 </div>
            </div>
        </div>

        <h3 style="margin-bottom: 16px; font-size: 1.2rem; font-weight: 700;">Inhoud</h3>
        
        <!-- Add Item -->
        <div class="card">
            <form method="POST" id="addItemForm">
                <div class="input-group">
                    <input type="text" name="item_name" id="itemName" placeholder="Wat zit erin?" required>
                </div>
                <div style="position: relative;">
                    <textarea name="item_desc" id="itemDesc" placeholder="Extra details (merk, kleur...)" rows="2" style="padding-right: 48px;"></textarea>
                    <button type="button" id="micBtn" style="position: absolute; right: 12px; bottom: 12px; border: none; background: none; font-size: 1.4rem; cursor: pointer; opacity: 0.6;">
                        🎤
                    </button>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 16px;">Toevoegen</button>
            </form>
            <div id="offlineMsg" style="display:none; color: var(--primary); margin-top: 10px; text-align: center; font-weight: 500;">Offline opgeslagen!</div>
        </div>

        <!-- Items List -->
        <?php if (count($items) > 0): ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($items as $item): ?>
                <div class="card" style="margin:0; padding: 16px; display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($item['name']); ?></div>
                        <?php if($item['description']): ?>
                            <div class="text-muted" style="margin-top: 4px; font-size: 0.95rem;"><?php echo htmlspecialchars($item['description']); ?></div>
                        <?php endif; ?>
                    </div>
                    <button onclick="deleteItem(<?php echo $item['id']; ?>, this)" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; opacity: 0.4; padding: 0 0 0 10px;">
                        🗑️
                    </button>
                </div>
            <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted" style="margin-top: 40px;">Nog niets in deze doos.</p>
        <?php endif; ?>

    <?php endif; ?>
</div>

<script src="assets/js/offline-db.js"></script>
<script>
    // Mic & Offline Logic
    const micBtn = document.getElementById('micBtn');
    const descArea = document.getElementById('itemDesc');
    const form = document.getElementById('addItemForm');

    if (micBtn && ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        recognition.lang = 'nl-NL';
        micBtn.onclick = () => { micBtn.style.color = 'var(--accent)'; recognition.start(); };
        recognition.onresult = (e) => { 
            descArea.value += (descArea.value ? ' ' : '') + e.results[0][0].transcript; 
            micBtn.style.color = ''; 
        };
    } else if(micBtn) micBtn.style.display = 'none';

    if(form) {
        form.addEventListener('submit', async (e) => {
            if(!navigator.onLine) {
                e.preventDefault();
                await queueOfflineItem({
                    box_id: <?php echo $box ? $box['id'] : 0; ?>,
                    name: document.getElementById('itemName').value,
                    description: descArea.value
                });
                document.getElementById('offlineMsg').style.display='block';
                form.reset();
                setTimeout(()=>document.getElementById('offlineMsg').style.display='none', 3000);
            }
        });
    }

    // AI Analysis
    async function analyzeImage() {
        const resDiv = document.getElementById('aiResult');
        const btn = document.querySelector('button[onclick="analyzeImage()"]');
        
        resDiv.style.display = 'block';
        resDiv.innerHTML = '<i>AI is aan het kijken... (Dit kan 5-10s duren)</i>';
        if(btn) btn.disabled = true;
        
        try {
            const resp = await fetch('api/analyze.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ box_id: <?php echo $box['id']; ?> })
            });
            const data = await resp.json();
            
            if(data.success) {
                resDiv.innerHTML = `
                    <div style="margin-bottom: 8px;">✨ <b>AI Suggestie:</b> ${data.description}</div>
                    <button onclick="addItemsFromAI('${data.description.replace(/'/g, "\\'")}')" class="btn-primary" style="font-size: 0.85rem; padding: 6px 12px;">+ Voeg toe aan lijst</button>
                `;
            } else {
                // Debugging: Show raw error
                let errMsg = data.error;
                if (data.raw && data.raw.error) {
                    errMsg += ': ' + data.raw.error.message;
                } else if (data.raw) {
                    errMsg += '<br><pre style="font-size:0.75rem; white-space:pre-wrap;">' + JSON.stringify(data.raw, null, 2) + '</pre>';
                }
                resDiv.innerHTML = `⚠️ <b>Error:</b> ${errMsg}`;
                console.error(data);
            }
        } catch(e) {
            resDiv.innerHTML = `⚠️ <b>Connection Error</b>`;
            console.error(e);
        } finally {
            if(btn) btn.disabled = false;
        }
    }

    async function addItemsFromAI(desc) {
        if(!confirm('Wil je deze items toevoegen aan de lijst?')) return;
        
        const items = desc.split(',').map(s => s.trim()).filter(s => s.length > 0);
        
        for (const item of items) {
            // Re-use the form submission logic but manually via fetch to avoid reload spam, or just reload once at end
            const fd = new FormData();
            fd.append('item_name', item);
            fd.append('item_desc', 'AI Generated');
            
            await fetch('', { // Post to self
                method: 'POST',
                body: fd
            });
        }
        window.location.reload();
    }

    async function deleteItem(id, btn) {
        if(!confirm('Weet je zeker dat je dit item wilt verwijderen?')) return;
        
        try {
            const resp = await fetch('api/delete_item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await resp.json();
            
            if(data.success) {
                // Remove element visually
                btn.closest('.card').remove();
            } else {
                alert('Fout bij verwijderen: ' + (data.error || 'Onbekend'));
            }
        } catch(e) {
            alert('Verbindingsfout');
        }
    }
</script>
</body>
</html>

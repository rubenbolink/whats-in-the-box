<?php
require 'config.php';
require 'header.php';

$search = $_GET['q'] ?? '';
$results = [];

if ($search) {
    $stmt = $pdo->prepare("
        SELECT i.name, i.description, b.qr_code, b.id as box_id, l.name as location_name
        FROM items i
        JOIN boxes b ON i.box_id = b.id
        JOIN locations l ON b.location_id = l.id
        WHERE MATCH(i.name, i.description) AGAINST(:search IN NATURAL LANGUAGE MODE)
        LIMIT 20
    ");
    $stmt->execute(['search' => $search]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("
        SELECT b.qr_code, b.id, l.name as location_name, COUNT(i.id) as item_count
        FROM boxes b
        JOIN locations l ON b.location_id = l.id
        LEFT JOIN items i ON b.id = i.box_id
        GROUP BY b.id
        ORDER BY b.id DESC
        LIMIT 10
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container">
    <div class="page-header">
        <h2 class="page-title">Overzicht</h2>
        <a href="locations.php" class="btn-text" style="font-size: 0.9rem;">📍 Locaties</a>
    </div>

    <!-- Quick Actions -->
    <div class="grid-2" style="margin-bottom: 20px;">
        <a href="scanner.php" class="card clickable text-center" style="margin:0; background: var(--surface); border: 2px solid var(--primary); color: var(--primary);">
            <div style="font-size: 2rem; margin-bottom: 4px;">📷</div>
            <div style="font-weight: 600;">Scan QR</div>
        </a>
        <a href="create-box.php" class="card clickable text-center" style="margin:0;">
            <div style="font-size: 2rem; margin-bottom: 4px;">📦</div>
            <div style="font-weight: 600; color: var(--text-main);">Nieuwe Doos</div>
        </a>
    </div>

    <!-- Search -->
    <form action="" method="GET" style="position: relative; margin-bottom: 30px;">
        <input type="text" name="q" placeholder="Zoek op item, beschrijving..." value="<?php echo htmlspecialchars($search); ?>" style="padding-left: 48px; border-radius: 50px;">
        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 1.2rem; opacity: 0.5;">🔍</span>
    </form>

    <div class="flex-between" style="margin-bottom: 16px;">
        <h3 style="margin:0; font-size: 1.1rem; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
            <?php echo $search ? 'Resultaten' : 'Recent'; ?>
        </h3>
        <?php if(!$search): ?>
            <a href="print-labels.php" class="btn-text" style="padding:0; font-size: 0.9rem;">Labels Printen &rarr;</a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($results)): ?>
        <div class="text-center" style="padding: 60px 20px; opacity: 0.6;">
            <div style="font-size: 3rem; margin-bottom: 10px;">📦</div>
            <p class="text-muted">Je zolder is nog leeg (of heel georganiseerd!).</p>
        </div>
    <?php else: ?>
        <div class="results-container">
            <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($results as $row): ?>
                <a href="box.php?qr=<?php echo urlencode($row['qr_code']); ?>" class="card clickable flex-between" style="text-decoration: none; margin: 0; padding: 18px 24px;">
                    <div>
                        <?php if ($search): ?>
                            <div style="font-weight: 600; font-size: 1.1rem; color: var(--text-main); margin-bottom: 2px;"><?php echo htmlspecialchars($row['name']); ?></div>
                            <div class="text-muted" style="font-size: 0.9rem;"><?php echo htmlspecialchars($row['description']); ?></div>
                            <div style="font-size: 0.8rem; margin-top: 6px; color: var(--primary); font-weight: 500;">
                                📦 <?php echo htmlspecialchars($row['qr_code']); ?> &nbsp;•&nbsp; <?php echo htmlspecialchars($row['location_name']); ?>
                            </div>
                        <?php else: ?>
                            <div style="font-family: 'Outfit'; font-weight: 700; font-size: 1.25rem; color: var(--text-main);">
                                <?php echo htmlspecialchars($row['qr_code']); ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.95rem; margin-top: 2px;">
                                📍 <?php echo htmlspecialchars($row['location_name']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!$search): ?>
                    <div style="width: 32px; height: 32px; background: var(--bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.9rem; color: var(--text-sub);">
                        <?php echo $row['item_count']; ?>
                    </div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // --- Live Search Logic ---
    const searchInput = document.querySelector('input[name="q"]');
    const resultsContainer = document.querySelector('.results-container'); // Need to wrap results
    const originalContent = resultsContainer ? resultsContainer.innerHTML : '';
    
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();
            
            // If empty, restore original "Recent" list (if we were on home) or just reload
            if (query.length === 0) {
                 // For simplicity on the home page, we can reload or just clear results. 
                 // If we want a true SPA feel, we'd need to store the "Recent" HTML. 
                 // Let's just restore original content if available.
                 if(originalContent) resultsContainer.innerHTML = originalContent;
                 return;
            }
            
            if (query.length < 2) return;

            debounceTimer = setTimeout(() => {
                fetch(`api/search.php?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        renderResults(data);
                    })
                    .catch(err => console.error('Search error:', err));
            }, 300);
        });
    }

    function renderResults(data) {
        if (!resultsContainer) return;
        
        // Update header logic (simulated)
        const header = document.querySelector('h3');
        if(header) header.innerText = 'Resultaten';

        if (data.length === 0) {
            resultsContainer.innerHTML = `
                <div class="text-center" style="padding: 40px 20px; opacity: 0.6;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🤔</div>
                    <p class="text-muted">Geen resultaten gevonden.</p>
                </div>`;
            return;
        }

        let html = '<div style="display: flex; flex-direction: column; gap: 12px;">';
        data.forEach(item => {
            // Determine icon and details based on type
            const isBox = item.type === 'box';
            const icon = isBox ? '📦' : '📄';
            const title = item.title;
            const subtitle = item.subtitle;
            const url = `box.php?qr=${encodeURIComponent(item.id)}`; // API returns qr_code as id for boxes

            html += `
                <a href="${url}" class="card clickable flex-between" style="text-decoration: none; margin: 0; padding: 18px 24px; animation: fadeEnter 0.2s;">
                    <div>
                        <div style="font-weight: 600; font-size: 1.1rem; color: var(--text-main); margin-bottom: 2px;">
                            ${isBox ? title : title} 
                        </div>
                        <div class="text-muted" style="font-size: 0.9rem;">
                            ${subtitle}
                        </div>
                        <div style="font-size: 0.8rem; margin-top: 6px; color: var(--primary); font-weight: 500;">
                            ${icon} ${isBox ? 'Doos' : 'Item'}
                        </div>
                    </div>
                </a>
            `;
        });
        html += '</div>';
        resultsContainer.innerHTML = html;
    }

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js').then(reg => {
            reg.update(); 
        });
    }
</script>
</body>
</html>

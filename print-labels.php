<?php require 'header.php'; ?>

<style>
    /* Shared Styles */
    .label-item {
        border: 1px dashed #ddd;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 5px;
        background: white;
    }

    /* Screen Styles (Responsive Preview) */
    @media screen {
        #sheet {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 16px;
            width: 100%;
            height: auto;
            margin-top: 20px;
        }
        .label-item {
            aspect-ratio: 1/1; /* Square preview cards */
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }
    }

    /* Print Styles (Strict A4) */
    @media print {
        @page { 
            size: A4 portrait; 
            margin: 0; 
        }
        body { 
            margin: 0; 
            padding: 0; 
            background: white; 
        }
        .no-print, .navbar, .page-header, .container > .card { 
            display: none !important; 
        }
        .container {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        #sheet {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(7, 1fr);
            gap: 0;
            width: 210mm;
            height: 297mm;
            padding: 10mm; 
            margin: 0;
            transform: none;
            box-shadow: none;
            box-sizing: border-box;
        }
        .label-item {
            border: 1px dashed #ddd; /* Light guide lines */
            box-shadow: none;
            border-radius: 0;
            aspect-ratio: auto;
        }
    }
</style>

<div class="container">
    <div class="page-header no-print">
        <a href="index.php" class="back-link"><span>&larr;</span> Dashboard</a>
        <h2 class="page-title">Labels Printen</h2>
    </div>

    <div class="card no-print">
        <p>Dit maakt een A4 vel met <b>28 stickers</b>.</p>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn-primary">🖨️ Nu Printen</button>
            <button onclick="location.reload()" class="btn-text">🔄 Nieuwe Codes</button>
        </div>
        <p class="text-muted" style="font-size: 0.85rem; margin-top: 10px;">
            Tip: Zet marges op "Geen" of "Minimaal" in je printer instellingen.
        </p>
    </div>

    <!-- Sheet Container -->
    <div id="sheet">
        <!-- Labels generated via JS -->
    </div>
</div>

<script src="assets/lib/qrcode.min.js"></script>
<script>
    function generateId() {
        return 'BOX-' + Math.random().toString(36).substr(2, 6).toUpperCase();
    }

    const labelsContainer = document.getElementById('sheet');
    
    // 4 columns x 7 rows = 28 labels
    for (let i = 0; i < 28; i++) {
        const id = generateId();
        const labelDiv = document.createElement('div');
        labelDiv.className = 'label-item';
        
        const qrDiv = document.createElement('div');
        const textDiv = document.createElement('div');
        textDiv.textContent = id;
        textDiv.style.fontSize = '12px';
        textDiv.style.fontFamily = 'Outfit, sans-serif';
        textDiv.style.fontWeight = '700';
        textDiv.style.marginTop = '4px';

        labelDiv.appendChild(qrDiv);
        labelDiv.appendChild(textDiv);
        labelsContainer.appendChild(labelDiv);

        new QRCode(qrDiv, {
            text: id,
            width: 100,
            height: 100,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });
    }
</script>
</body>
</html>

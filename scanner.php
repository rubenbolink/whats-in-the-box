<?php require 'header.php'; ?>

<div class="container">
    <div class="page-header">
        <a href="index.php" class="back-btn"><span>&larr;</span> Annuleren</a>
        <h2 class="page-title">Scanner</h2>
    </div>

    <div class="card" style="padding: 0; border: 2px solid var(--primary); background: black; height: 400px; display: flex; align-items: center; justify-content: center;">
        <div id="reader" style="width: 100%; height: 100%;"></div>
    </div>
    
    <p class="text-center text-muted" style="margin-top: 20px; font-weight: 500;">
        Richt de camera op een Box-Zolder QR code.
    </p>
</div>

<script src="assets/lib/html5-qrcode.min.js"></script>
<script>
    const onScanSuccess = (decodedText) => {
        html5QrCode.stop().then(() => {
            window.location.href = `box.php?qr=${encodeURIComponent(decodedText)}`;
        }).catch(() => {
            window.location.href = `box.php?qr=${encodeURIComponent(decodedText)}`;
        });
    };

    const html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess)
    .catch(err => {
        document.getElementById('reader').innerHTML = `<div style="color:white; padding:20px;">Camera toegang geweigerd: ${err}</div>`;
    });
</script>
</body>
</html>

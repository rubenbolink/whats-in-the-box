<?php
require 'config.php';
echo "<h1>Success!</h1>";
echo "<p>Project <b>zolder</b> is running.</p>";
echo "<p>Database connection status: " . ($pdo ? 'Connected' : 'Failed') . "</p>";
?>

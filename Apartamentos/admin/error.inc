<?php if (isset($_GET['error']) && !empty($_GET['error'])): ?>
    <div class="error">
        <p><?php echo htmlspecialchars($_GET['error']); ?></p>
    </div>
<?php endif; ?>

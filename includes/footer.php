    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Web Quản Lý Chi Tiêu. All rights reserved.</p>
        </div>
    </footer>
    <script src="<?php echo base_url('assets/js/main.js'); ?>"></script>
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo base_url($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>


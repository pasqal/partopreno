<?php
// ============================================
// Pied de page commun à toutes les pages
// ============================================
?>
    </main>
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> - Système d'Inscription. Tous droits réservés.</p>
            <?php if (isAdminLoggedIn() && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                <p class="admin-link">
                    <a href="<?php echo url('admin/index.php'); ?>">⚙️ Accès Admin</a>
                </p>
            <?php endif; ?>
        </div>
    </footer>
    
    <script src="<?php echo url('assets/js/script.js'); ?>"></script>
</body>
</html>

    </div> <!-- End container-fluid -->
    
    <footer style="background: #333; color: white; padding: 20px; margin-top: 40px; text-align: center;">
        <p>&copy; <?= date('Y') ?> <?= $Settings->site_name ?>. All rights reserved.</p>
    </footer>
    
    <?php 
    // Use admin assets as shop assets may not exist
    $admin_assets = base_url('themes/default/admin/assets/');
    ?>
    <script type="text/javascript" src="<?= $admin_assets ?>js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?= $admin_assets ?>js/custom.js"></script>
</body>
</html>


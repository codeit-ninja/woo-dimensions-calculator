<?php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}
?>
<div class="wrap">
    <h1><?php echo $this->page_name; ?></h1>
    <form method="post" action="">

        <?php settings_fields( 'woo-product-dimensions-master' ); ?>
        <?php do_settings_sections( 'codeit-plugins-settings' ); ?>
        <?php submit_button(); ?>
    </form>
</div>
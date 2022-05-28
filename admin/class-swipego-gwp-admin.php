<?php
if ( !defined( 'ABSPATH' ) ) exit;

class Swipego_GWP_Admin {

    // Register hooks
    public function __construct() {

        add_action( 'admin_notices', array( $this, 'give_notice' ) );

    }

    // Show notice if Give WP not installed
    public function give_notice() {

        if ( !swipego_is_plugin_activated( 'give/give.php' ) ) {
            swipego_gwp_notice( __( 'Give WP needs to be installed and activated.', 'swipego-gwp' ), 'error' );
        }

    }

}

new Swipego_GWP_Admin();

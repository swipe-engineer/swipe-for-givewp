<?php
if ( !defined( 'ABSPATH' ) ) exit;

class Swipego_GWP_Init {

    // Register hooks
    public function __construct() {

        add_action('plugins_loaded', array($this, 'init'));

    }

    // Load required files
    public function init() {

        require_once( SWIPEGO_GWP_PATH . 'includes/class-swipego-gwp-gateway.php' );

    }

}
new Swipego_GWP_Init();

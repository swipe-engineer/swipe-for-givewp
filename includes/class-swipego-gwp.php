<?php
if (!defined('ABSPATH')) exit;

class SwipeGo_GWP
{
    // Load dependencies
    public function __construct()
    {
        
        // Libraries
        require_once(SWIPEGO_GWP_PATH . 'libraries/swipego/class-swipego.php');
        
        if ( swipego_is_plugin_activated('give/give.php')) {
            
            // Functions
            require_once(SWIPEGO_GWP_PATH . 'includes/functions.php');
    
            // Admin
            require_once(SWIPEGO_GWP_PATH . 'admin/class-swipego-gwp-admin.php');
            
            // API
            require_once( SWIPEGO_GWP_PATH . 'libraries/swipego/includes/abstracts/abstract-swipego-client.php' );
            require_once( SWIPEGO_GWP_PATH . 'libraries/swipego/includes/class-swipego-api.php' );
            require_once( SWIPEGO_GWP_PATH . 'includes/class-swipego-gwp-api.php' );
        
            if ( swipego_is_logged_in() || swipego_get_integration() ) {
                
                // Initialize payment gateway
                require_once( SWIPEGO_GWP_PATH . 'includes/class-swipego-gwp-init.php' );
    
            }
            
        }
        
    }

    public function register_activation_hook()
    {
        echo 'register_activation_hook';
    }

    public function register_deactivation_hook()
    {
        swipego_delete_access_token();
        echo 'register_deactivation_hook';
    }

    public function register_uninstall_hook()
    {
        swipego_delete_access_token();
        echo 'register_uninstall_hook';
    }
}

$swipego_gwp = new SwipeGo_GWP();

register_activation_hook( SWIPEGO_GWP_FILE, array($swipego_gwp, 'register_activation_hook') );
register_deactivation_hook( SWIPEGO_GWP_FILE, array($swipego_gwp, 'register_deactivation_hook') );
register_uninstall_hook( SWIPEGO_GWP_FILE, array($swipego_gwp, 'register_uninstall_hook') );
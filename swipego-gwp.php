<?php
/**
 * Plugin Name:       Swipe for GiveWP
 * Description:       Swipe payment integration for GiveWP.
 * Version:           1.0.0
 * Requires at least: 4.6
 * Requires PHP:      7.0
 * Author:            Fintech Worldwide Sdn. Bhd.
 * Author URI:        https://swipego.io/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'Swipego_GWP' ) ) return;

define( 'SWIPEGO_GWP_FILE', __FILE__ );
define( 'SWIPEGO_GWP_URL', plugin_dir_url( SWIPEGO_GWP_FILE ) );
define( 'SWIPEGO_GWP_PATH', plugin_dir_path( SWIPEGO_GWP_FILE ) );
define( 'SWIPEGO_GWP_BASENAME', plugin_basename( SWIPEGO_GWP_FILE ) );
define( 'SWIPEGO_GWP_VERSION', '1.0.0' );

// // Plugin core class
require( SWIPEGO_GWP_PATH . 'includes/class-swipego-gwp.php' );
<?php
if ( !defined( 'ABSPATH' ) ) exit;

class Swipego_GWP_API extends Swipego_API_GWP {

    // Log a message in Gravity Forms logs
    protected function log( $message ) {

        if ( $this->debug ) {
            swipego_gwp_logger( $message );
        }

    }

}

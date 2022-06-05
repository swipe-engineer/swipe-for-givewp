<?php

class Give_Swipego_Settings_Metabox
{
    private static $instance;

    public static function get_instance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Setup hooks.
     */
    public function setup_hooks()
    {
        
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'enqueue_js'));
            add_action('wp_ajax_swipego_gwp_update_settings', array($this, 'update_settings'));

            add_filter('give_forms_swipego_metabox_fields', array($this, 'give_swipego_add_settings'));
            add_filter('give_metabox_form_data_settings', array($this, 'add_swipego_setting_tab'), 0, 1);
        }
    }

    public function add_swipego_setting_tab($settings)
    {
        if (give_is_gateway_active('swipego')) {
            $settings['swipego_options'] = apply_filters('give_forms_swipego_options', array(
                'id' => 'swipego_options',
                'icon-html' => '<img width="13" src="'. SWIPEGO_URL . 'assets/images/icon-swipe.svg'.'" />',
                'title' => __('SwipeGo', 'give'),
                'fields' => apply_filters('give_forms_swipego_metabox_fields', array()),
            ));
        }

        return $settings;
    }

    public function give_swipego_add_settings($settings)
    {

        // Bailout: Do not show offline gateways setting in to metabox if its disabled globally.
        if (in_array('swipego', (array) give_get_option('gateways'))) {
            return $settings;
        }

        $is_gateway_active = give_is_gateway_active('swipego');

        //this gateway isn't active
        if (!$is_gateway_active) {
            return $settings;
        }

        //Fields
        $all_business = swipego_gwp_get_businesses();

        $all_business[0] = array(
            'id' => '0',
            'name' => __('Select a business', 'give-swipego'),
        );

        $businesses = array_map(
            function ($business) {
                return $business['name'];
            },
            array_reverse($all_business, true)
        );

        $give_swipego_settings = array(

            array(
                'name' => __('SwipeGo', 'give-swipego'),
                'desc' => __('Do you want to customize the donation instructions for this form?', 'give-swipego'),
                'id' => 'swipego_customize_swipego_donations',
                'type' => 'radio_inline',
                'default' => 'global',
                'options' => apply_filters('give_forms_content_options_select', array(
                    'global' => __('Global Option', 'give-swipego'),
                    'enabled' => __('Customize', 'give-swipego'),
                    'disabled' => __('Disable', 'give-swipego'),
                )
                ),
            ),
            array(
                'name' => __('Business', 'give-swipego'),
                'id' => 'swipego_business_id',
                'type' => 'select',
                'options' => $businesses,
                'row_classes' => 'give-swipego-key',
            ),
            array(
                'name' => __('Environment', 'give-swipego'),
                'id' => 'swipego_environment',
                'type' => 'radio_inline',
                'default' => 'production',
                'options' => array(
                    'production' => __('Production', 'give-swipego'),
                    'sandbox' => __('Sandbox', 'give-swipego'),
                ),
            ),
            array(
                'name' => __('API Access Key', 'give-swipego'),
                'id' => 'swipego_api_key',
                'type' => 'text',
                'attributes' => [
                    'readonly' => 'readonly',
                ],
                'row_classes' => 'give-swipego-key',
            ),
            array(
                'name' => __('API Signature Key', 'give-swipego'),
                'id' => 'swipego_signature_key',
                'type' => 'text',
                'attributes' => [
                    'readonly' => 'readonly',
                ],
                'row_classes' => 'give-swipego-key',
            ),
        );

        return array_merge($settings, $give_swipego_settings);
    }

    public function enqueue_js($hook)
    {
        if ('post.php' === $hook || $hook === 'post-new.php') {
            wp_enqueue_script('give_swipego_each_form', SWIPEGO_GWP_URL . '/includes/js/meta-box.js', array('jquery'), SWIPEGO_GWP_VERSION, true);

            wp_localize_script('give_swipego_each_form', 'swipego_gwp_update_settings', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('swipego_gwp_update_settings_nonce'),
            ));
        }
    }
}

Give_Swipego_Settings_Metabox::get_instance()->setup_hooks();

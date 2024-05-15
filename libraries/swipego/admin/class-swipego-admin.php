<?php
if (!defined('ABSPATH')) exit;

class Swipego_Admin
{

    private $id = 'swipego';

    // Register hooks
    public function __construct()
    {

        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        add_action('wp_ajax_swipego_login', array($this, 'login'));
        add_action('wp_ajax_swipego_logout', array($this, 'logout'));
    }

    // Register admin menu
    public function register_menu()
    {

        add_menu_page(
            __('Swipe', 'swipego'),
            __('Swipe', 'swipego'),
            'manage_options',
            $this->id,
            array($this, 'view_page'),
            SWIPEGO_URL . 'assets/images/icon-swipe.svg',
            65
        );

        if (swipego_is_logged_in()) {
            add_submenu_page(
                'swipego',
                __('Swipe – Dashboard', 'swipego'),
                __('Dashboard', 'swipego'),
                'manage_options',
                $this->id
            );
        }
    }

    // Get the views of the admin page based on user authentication
    public function view_page()
    {

        if (!swipego_is_logged_in()) {
            echo $this->login_page();
        } else {
            echo $this->dashboard_page();
        }
    }

    // Get the views of the login page
    private function login_page()
    {

        ob_start();
        require_once(SWIPEGO_PATH . 'admin/views/login.php');

        return ob_get_clean();
    }

    // Get the views of the dashboard page
    private function dashboard_page()
    {
        if (swipego_is_plugin_activated('give/give.php') && give_get_option('swipego_business_id') == false) {

            $businesses = swipego_gwp_get_businesses();
            $business = reset($businesses) ?? null;

            if (isset($business)) {
                give_update_option('swipego_business_id', $business['id'] ?? null);
                give_update_option('swipego_environment', 'production');
                give_update_option('swipego_api_key', $business['api_key'] ?? null);
                give_update_option('swipego_signature_key', $business['signature_key'] ?? null);
                give_update_option('swipego_webhook_name', $business['webhook_name'] ?? null);
                give_update_option('swipego_webhook_url', $business['webhook_url'] ?? null);
                give_update_option('swipego_webhook_enabled', $business['webhook_enabled'] ?? null);
            }
        }

        $swipego_wc_plugin = $this->get_plugin_url('woocommerce/woocommerce.php', 'swipe-for-woocommerce/swipego-wc.php', 'wc', __('WooCommerce', 'swipego'));
        $swipego_gf_plugin = $this->get_plugin_url('gravityforms/gravityforms.php', 'swipe-for-gravity-forms/swipego-gf.php', 'gf', __('Gravity Forms', 'swipego'));
        $swipego_give_plugin = $this->get_plugin_url('give/give.php', 'swipe-for-givewp/swipego-gwp.php', 'gwp', __('Give WP', 'swipego'));

        ob_start();
        require_once(SWIPEGO_PATH . 'admin/views/dashboard.php');

        return ob_get_clean();
    }

    // Generate plugin URL based on installed and activated plugins
    private function get_plugin_url($main_plugin_file, $swipego_plugin_file, $settings_page_slug, $main_plugin_name)
    {

        $main_plugin = explode('/', $main_plugin_file)[0] ?? '';
        $swipe_plugin = explode('/', $swipego_plugin_file)[0] ?? '';

        $is_main_plugin_activated = swipego_is_plugin_activated($main_plugin_file);
        $is_swipego_plugin_activated = swipego_is_plugin_activated($swipego_plugin_file);

        $is_main_plugin_installed = swipego_is_plugin_installed($main_plugin_file);
        $is_swipego_plugin_installed = swipego_is_plugin_installed($swipego_plugin_file);

        $main_plugin_download_url = 'https://wordpress.org/plugins/' . $main_plugin;
        $swipego_plugin_download_url = 'https://wordpress.org/plugins/' . $swipe_plugin;

        if ($main_plugin == 'gravityforms') {
            $main_plugin_download_url = 'https://www.gravityforms.com';
        }

        if ($is_main_plugin_activated && $is_swipego_plugin_activated && $settings_page_slug == 'gwp') {
            return array(
                'label' => __('Configure', 'swipego'),
                'url'   => admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways')
            );
        }

        // If main plugin and Swipe plugin is activated, return settings page URL
        if ($is_main_plugin_activated && $is_swipego_plugin_activated) {
            return array(
                'label' => __('Configure', 'swipego'),
                'url'   => admin_url('admin.php?page=swipego_' . $settings_page_slug . '_settings')
            );
        }

        // If Swipe plugin is installed but not activated, return plugin activation URL
        if ($is_swipego_plugin_installed && !$is_swipego_plugin_activated) {
            return array(
                'label' => __('Activate', 'swipego'),
                'url'   => wp_nonce_url(admin_url('plugins.php?action=activate&plugin=' . $swipego_plugin_file), 'activate-plugin_' . $swipego_plugin_file)
            );
        }

        // If Swipe plugin is not installed, return plugin download URL
        if (!$is_swipego_plugin_installed) {
            return array(
                'label' => __('Download', 'swipego'),
                'url'   => esc_url($swipego_plugin_download_url)
            );
        }

        /////////////////////////////////////////////////////////////

        // If main plugin is installed but not activated, return plugin activation URL
        if ($is_main_plugin_installed && !$is_main_plugin_activated) {
            return array(
                'label' => sprintf(__('Activate %s', 'swipego'), $main_plugin_name),
                'url'   => wp_nonce_url(admin_url('plugins.php?action=activate&plugin=' . $main_plugin_file), 'activate-plugin_' . $main_plugin_file)
            );
        }

        // If main plugin is not installed, return plugin download URL
        if (!$is_main_plugin_installed) {
            return array(
                'label' => sprintf(__('Download %s', 'swipego'), $main_plugin_name),
                'url'   => esc_url($main_plugin_download_url)
            );
        }
    }

    // Enqueue styles & scripts
    public function enqueue_scripts($hook)
    {

        wp_enqueue_style('swipego-admin-all', SWIPEGO_URL . 'assets/css/admin-all.css', array(), SWIPEGO_VERSION, 'all');

        wp_register_style('sweetalert2', SWIPEGO_URL . 'assets/css/sweetalert2.min.css', array(), '11.4.1', 'all');
        wp_register_script('sweetalert2', SWIPEGO_URL . 'assets/js/sweetalert2.all.min.js', array('jquery'), '11.4.1', true);

        if (strpos($hook, 'swipego') == false) {
            return;
        }

        wp_enqueue_style('swipego-admin-global', SWIPEGO_URL . 'assets/css/global.min.css', array(), '3.0.23', 'all');
        wp_enqueue_style('swipego-admin', SWIPEGO_URL . 'assets/css/admin.css', array(), SWIPEGO_VERSION, 'all');

        wp_enqueue_script('flowbite', SWIPEGO_URL . 'assets/js/flowbite.js', array('jquery'), '1.3.4', true);
        wp_enqueue_script('jquery-validate', SWIPEGO_URL . 'assets/js/jquery.validate.min.js', array('jquery'), '1.19.3', true);

        wp_enqueue_style('sweetalert2');
        wp_enqueue_script('sweetalert2');

        wp_enqueue_script('swipego-admin', SWIPEGO_URL . 'assets/js/admin.js', array('jquery', 'jquery-validate', 'sweetalert2'), SWIPEGO_VERSION, true);

        wp_localize_script('swipego-admin', 'swipego_login', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('swipego_login_nonce'),
        ));

        wp_localize_script('swipego-admin', 'swipego_logout', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('swipego_logout_nonce'),
        ));
    }

    // Process Swipe account login
    public function login()
    {

        check_ajax_referer('swipego_login_nonce', 'nonce');

        $nonce    = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;
        $email    = isset($_POST['email']) ? sanitize_email($_POST['email']) : null;
        $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : null;
        $remember = isset($_POST['remember']) ? (bool) sanitize_text_field($_POST['remember']) : false;

        if (!wp_verify_nonce($nonce, 'swipego_login_nonce')) {
            wp_send_json_error(array(
                'message' => __('Invalid nonce', 'swipego'),
            ), 400);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('No permission to execute the action', 'swipego'),
            ), 400);
        }

        if (!$email || !$password) {
            wp_send_json_error(array(
                'message' => __('Missing required field', 'swipego'),
            ), 400);
        }

        try {

            $swipego = new Swipego_API_GWP();

            list($code, $response) = $swipego->sign_in(array(
                'email'    => $email,
                'password' => $password,
            ));

            $data = isset($response['data']) ? $response['data'] : false;
            $errors = isset($response['errors']) ? $response['errors'] : false;

            if ($errors) {
                foreach ($errors as $error) {
                    throw new Exception($error[0]);
                }
            }

            if (isset($data['token']) && !empty($data['token'])) {
                swipego_update_access_token($data['token'], $remember);
                swipego_update_integration($email);
            } else {
                throw new Exception(__('An error occured! Please try again.', 'swipego'));
            }
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage(),
            ), 400);
        }

        wp_send_json_success();
    }

    // Process Swipe account logout
    public function logout()
    {

        check_ajax_referer('swipego_logout_nonce', 'nonce');

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (!wp_verify_nonce($nonce, 'swipego_logout_nonce')) {
            wp_send_json_error(array(
                'message' => __('Invalid nonce', 'swipego'),
            ), 400);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('No permission to execute the action', 'swipego'),
            ), 400);
        }

        swipego_delete_access_token();
        swipego_delete_integration();

        wp_send_json_success();
    }
}
new Swipego_Admin();

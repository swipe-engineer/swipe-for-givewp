<?php

/**
 * Class Give_swipeGo_Settings
 *
 * @since 1.0.3
 */
class Give_swipeGo_Settings
{

    /**
     * @access private
     * @var Give_swipeGo_Settings $instance
     */
    private static $instance;

    /**
     * @access private
     * @var string $section_id
     */
    private $section_id;

    /**
     * @access private
     *
     * @var string $section_label
     */
    private $section_label;

    /**
     * @access private
     * @var Swipego_GWP_API $swipego
     */
    private $swipego;

    /**
     * Give_swipeGo_Settings constructor.
     */
    private function __construct()
    {
    }

    /**
     * get class object.
     *
     * @return Give_swipeGo_Settings
     */
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
        $this->section_id = 'swipego';
        $this->section_label = __('swipeGo', 'give-swipego');
        $this->swipego = new Swipego_GWP_API();

        if (is_admin()) {
            // Add settings.
            add_action('admin_enqueue_scripts', array($this, 'enqueue_js'));
            add_action('wp_ajax_swipego_gwp_update_settings', array($this, 'update_settings'));
            add_action('wp_ajax_swipego_gwp_set_webhook', array($this, 'set_webhook'));
            add_action('give_admin_field_swipego_webhook', [$this, 'setWebhookSwpego']);

            add_filter('give_get_settings_gateways', array($this, 'add_settings'), 99);
            add_filter('give_get_sections_gateways', array($this, 'add_sections'), 99);
        }
    }

    /**
     * Add setting section.
     *
     * @param array $sections Array of section.
     *
     * @return array
     */
    public function add_sections($sections)
    {
        $sections[$this->section_id] = $this->section_label;

        return $sections;
    }

    /**
     * Add plugin settings.
     *
     * @param array $settings Array of setting fields.
     *
     * @return array
     */
    public function add_settings($settings)
    {
        $current_section = give_get_current_setting_section();

        if ($current_section != 'swipego') {
            return $settings;
        }

        $all_business = swipego_gwp_get_businesses();

        $businesses = array_map(
            function ($business) {
                return $business['name'];
            },
            $all_business
        );

        $business_id    = give_get_option('swipego_business_id');
        $business       = isset($all_business[$business_id]) ? $all_business[$business_id] : [];

        if (isset($business['integration_id'])) {
            $integration_id = $business['integration_id'];
            $webhook        = $this->swipego->get_webhook($business_id, $integration_id);
        }

        if(empty($businesses)){
            $businesses = array('No business selected');
        }

        $give_swipego_settings = array(
            array(
                'name' => __('swipeGo Settings', 'give-swipego'),
                'id' => 'give_title_gateway_swipego',
                'type' => 'title',
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
            array(
                'id' => 'swipego_webhook',
                'type' => 'swipego_webhook',
                'row_classes' => 'give-swipego-key',
                'default' => $webhook ?? null,
            ),
            array(
                'type' => 'sectionend',
                'id' => 'give_title_gateway_swipego',
            ),
        );

        return array_merge($settings, $give_swipego_settings);
    }

    public function setWebhookSwpego($webhook)
    {
?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="swipego_webhook">Webhook</label>
            </th>
            <td class="give-forminp give-forminp-text">
                <input name="swipego_webhook_url" id="swipego_webhook_url" type="text" value="<?php echo $webhook['default']['url'] ?? null; ?>" class="give-input-field" placeholder="click Set Webhook to generate url" readonly>
                <button id="swipego_webhook_button" style="cursor:pointer" class="button-primary">Set Webhook</button>
            </td>
        </tr>
<?php
    }

    // Update WooCommerce settings
    public function update_settings()
    {

        check_ajax_referer('swipego_gwp_update_settings_nonce', 'nonce');

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (!wp_verify_nonce($nonce, 'swipego_gwp_update_settings_nonce')) {
            wp_send_json_error(array(
                'message' => __('Invalid nonce', 'swipego'),
            ), 400);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('No permission to update the settings', 'swipego'),
            ), 400);
        }

        $business_id = isset($_POST['business_id']) ? sanitize_text_field($_POST['business_id']) : null;

        if (is_null($business_id)) {
            wp_send_json_error(array(
                'message' => __('Invalid business id', 'swipego'),
            ), 400);
        }

        $swipego        = new Swipego_GWP_API();
        $business       = swipego_gwp_get_business($business_id);
        $integration_id = isset($business['integration_id']) ? $business['integration_id'] : null;
        $webhook        = $swipego->get_webhook($business_id, $integration_id);

        wp_send_json_success(
            array(
                'business' => $business,
                'webhook' => $webhook,
            )
        );
    }

    // Set WooCommerce webhook URL in Swipe
    public function set_webhook()
    {

        check_ajax_referer('swipego_gwp_set_webhook_nonce', 'nonce');

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : null;

        if (!wp_verify_nonce($nonce, 'swipego_gwp_set_webhook_nonce')) {
            wp_send_json_error(array(
                'message' => __('Invalid nonce', 'swipego'),
            ), 400);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('No permission to update the settings', 'swipego'),
            ), 400);
        }

        $business_id = isset($_POST['business_id']) ? sanitize_text_field($_POST['business_id']) : null;
        $business_id = $business_id ?? give_get_option('swipego_business_id');
        $business    = swipego_gwp_get_business($business_id);
        $integration_id = $business['integration_id'];

        if (!$business_id) {
            wp_send_json_error(array(
                'message' => __('No business selected', 'swipego'),
            ), 400);
        }

        if (!$integration_id) {
            wp_send_json_error(array(
                'message' => __('Missing integration ID for selected business', 'swipego'),
            ), 400);
        }

        $swipego = new Swipego_GWP_API();

        $webhook = $swipego->set_webhook($business_id, $integration_id);

        wp_send_json_success($webhook);
    }

    public function enqueue_js($hook)
    {
        if ('give_forms_page_give-settings' === $hook) {

            wp_enqueue_script('give_swipego_each_form', SWIPEGO_GWP_URL . '/includes/js/meta-box.js', array('jquery', 'sweetalert2'), SWIPEGO_GWP_VERSION, true);

            wp_localize_script('give_swipego_each_form', 'swipego_gwp_update_settings', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('swipego_gwp_update_settings_nonce'),
            ));

            wp_localize_script('give_swipego_each_form', 'swipego_gwp_set_webhook', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('swipego_gwp_set_webhook_nonce'),
            ));
        }
    }
}

Give_swipeGo_Settings::get_instance()->setup_hooks();

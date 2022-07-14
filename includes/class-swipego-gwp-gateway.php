<?php
if (!defined('ABSPATH')) exit;

class Swipego_GWP_Gateway
{
    const QUERY_VAR = 'swipego_gwp_gateway';
    const LISTENER_PASSPHRASE = 'swipego_givewp_listener_passphrase';


    protected $_version = SWIPEGO_GWP_VERSION;
    protected $_min_give_wp_version = '1.8.12';
    protected $_slug = 'givewpswipego';
    protected $_path = 'givewpswipego/swipego.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Swipe for GiveWP';
    protected $_short_title = 'Swipe';
    protected $_supports_callbacks = true;
    protected $_requires_credit_card = false;

    private Swipego_GWP_API $swipego;

    private static $instance;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->init();
        $this->init_api();
    }

    // Register hooks
    public function init()
    {

        add_filter('give_payment_gateways', array($this, 'register_gateway'));

        if (!give_is_gateway_active('swipego')) return;

        add_action('init', array($this, 'return_redirect'));
        add_action('init', array($this, 'return_callback'));
        add_action('give_gateway_swipego', array($this, 'process_payment'));
        add_action('give_swipego_cc_form', array($this, 'give_swipego_cc_form'));
        add_filter('give_enabled_payment_gateways', array($this, 'give_filter_swipe_gateway'), 10, 2);
        add_filter('give_payment_confirm_swipego', array($this, 'give_swipego_success_page_content'));
        add_action('give_donation_form_before_email', array($this, 'register_account_fields'));

        if (is_admin()) {
            include SWIPEGO_GWP_PATH . '/includes/admin/give-swipego-activation.php';
            include SWIPEGO_GWP_PATH . '/includes/admin/give-swipego-settings.php';
            include SWIPEGO_GWP_PATH . '/includes/admin/give-swipego-settings-metabox.php';
        }
    }

    function register_account_fields($form_id)
    {
        $give_user_info = _give_get_prefill_form_field_values($form_id);
        $phone          = !empty($give_user_info['give_phone']) ? $give_user_info['give_phone'] : '';
?>
        <p id="give-phone-wrap" class="form-row form-row-wide">
            <label class="give-label" for="give-phone">
                <?php esc_attr_e('Phone', 'give'); ?>
                <span class="give-required-indicator">*</span>
                <?php echo Give()->tooltips->render_help(__('phone is used to personalize your donation record.', 'give')); ?>
            </label>

            <input class="give-input required" type="tel" name="give_phone" id="give-phone" placeholder="<?php esc_attr_e('Phone', 'give'); ?>" value="<?php echo esc_html($phone); ?>" required />
        </p>
<?php
    }

    // Initialize API
    private function init_api()
    {
        $this->swipego = new Swipego_GWP_API();
    }

    public function register_gateway($gateways)
    {

        // Format: ID => Name
        $label = array(
            'admin_label'    => __('Swipe Go', 'swipego-gwp'),
            'checkout_label' => __('Swipe Go', 'swipego-gwp'),
        );

        $gateways['swipego'] = apply_filters('give_swipego_label', $label);

        return $gateways;
    }

    public function return_callback()
    {

        if (!isset($_GET['callback'])) {
            return;
        }

        if ($_GET['callback'] !== 'swipego_gwp_gateway') {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $response = file_get_contents('php://input');
        $response = json_decode($response, true);
        $data     = isset($response['data']) ? $response['data'] : null;

        if ($data['payment_link_reference_2'] !== 'givewp') {
            return false;
        }

        if (!isset($data['payment_id'])) {
            return false;
        }

        $payment_id      = $data['payment_link_reference'];
        $form_id         = give_get_payment_form_id($payment_id);
        $custom_donation = give_get_meta($form_id, 'swipego_customize_swipego_donations', true, 'global');
        $status          = give_is_setting_enabled($custom_donation, 'enabled');

        try {

            swipego_gwp_logger('Verifying hash for form #' . $form_id);

            $getData = $this->getData($form_id);

            $this->swipego->set_signature_key($getData['signature_key']);
            $this->swipego->validate_ipn_response($data);
        } catch (Exception $e) {

            swipego_gwp_logger($e->getMessage());

            wp_die($e->getMessage(), 'Swipe IPN', array('response' => 200));
        } finally {

            swipego_gwp_logger('Verified hash for form #' . $form_id);
        }

        if ($data['payment_link_id'] !== give_get_meta($payment_id, 'swipego_id', true)) {
            exit('No Payment Link found');
        }

        if ($data['payment_status'] == '1' && give_get_payment_status($payment_id)) {
            $this->publish_payment($payment_id, $data);
        }
    }

    public function return_redirect()
    {
        if (!isset($_GET[self::QUERY_VAR])) {
            return;
        }

        $passphrase = get_option(self::LISTENER_PASSPHRASE, false);

        if (!$passphrase) {
            return;
        }

        if ($_GET[self::QUERY_VAR] != $passphrase) {
            return;
        }

        if (!isset($_GET['payment_id'])) {
            status_header(403);
            exit;
        }

        $payment_id      = preg_replace('/\D/', '', $_GET['payment_id']);
        $form_id         = give_get_payment_form_id($payment_id);
        $custom_donation = give_get_meta($form_id, 'swipego_customize_swipego_donations', true, 'global');
        $status          = give_is_setting_enabled($custom_donation, 'enabled');


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $response = file_get_contents('php://input');
            $response = json_decode($response, true);

        } else {

            $response = [
                'attempt_id' => isset($_GET['attempt_id']) ? sanitize_text_field($_GET['attempt_id']) : null,
                'payment_link_id' => isset($_GET['payment_link_id']) ? sanitize_text_field($_GET['payment_link_id']) : null,
                'payment_status' => isset($_GET['payment_status']) ? sanitize_text_field($_GET['payment_status']) : null,
            ];
            
        }

        $data = $response;

        if ($data['payment_link_id'] !== give_get_meta($payment_id, 'swipego_id', true)) {
            status_header(404);
            exit('No Payment Link found');
        }

        if ($data['payment_status'] == '1' && give_get_payment_status($payment_id)) {
            $this->publish_payment($payment_id, $data);
        }

        if ($data['payment_status'] == '1') {
            $return = add_query_arg(
                array(
                    'payment-confirmation' => 'swipego',
                    'payment-id' => $payment_id,
                ),
                get_permalink(
                    give_get_option('success_page')
                )
            );
        } else {
            $return = give_get_failed_transaction_uri('?payment-id=' . $payment_id);
        }

        wp_redirect($return);
        exit;
    }

    private function publish_payment($payment_id, $data)
    {
        if ('publish' !== get_post_status($payment_id)) {
            give_set_payment_transaction_id($payment_id, $data['attempt_id']);
            give_update_payment_status($payment_id, 'publish');
            give_insert_payment_note($payment_id, "Payment ID: {$payment_id}, Transaction ID: {$data['attempt_id']}");
        }
    }

    public function process_payment($purchase_data)
    {
        // Validate nonce.
        give_validate_nonce($purchase_data['gateway_nonce'], 'give-gateway');

        $swipego_key = $this->get_swipego($purchase_data);
        $payment_id = $this->create_payment($purchase_data);

        // Check payment.
        if (!$swipego_key['business_id'] || !$swipego_key['api_key']) {
            // If errors are present, send the user back to the purchase page so they can be corrected
            give_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['give-gateway']);
        }


        // Check payment.
        if (empty($payment_id)) {
            // Record the error.
            give_record_gateway_error(__('Payment Error', 'give-swipego'), sprintf( /* translators: %s: payment data */
                __('Payment creation failed before sending donor to Swipe Go. Payment data: %s', 'give-swipego'),
                json_encode($purchase_data)
            ), $payment_id);
            // Problems? Send back.
            give_send_back_to_checkout();
        }

        $this->swipego->set_api_key($swipego_key['api_key']);
        $this->swipego->set_environment($swipego_key['environment']);

        $parameter = array(
            'email'        => $purchase_data['user_email'],
            'currency'     => 'MYR',
            'amount'       => $purchase_data['price'],
            'title'        => 'payment for GiveWP : ' . $payment_id,
            'phone_no'     => $purchase_data['post_data']['give_phone'],
            'description'  => 'description payment for GiveWP : ' . $payment_id,
            'redirect_url' => self::get_listener_url($payment_id),
            'reference'    => $payment_id,
            'reference_2'  => 'givewp',
            'send_email'   => true,
        );

        list($code, $response) = $this->swipego->create_payment_link($parameter);

        if ($code !== 200) {

            $errors = isset($response['errors']) ? $response['errors'] : false;

            // Record the error.
            give_record_gateway_error(
                __('Payment Error', 'give-swipego'),
                sprintf(
                    __('Payment link creation failed. Error message: %s', 'give-$payment_id'),
                    json_encode($errors)
                ),
                $payment_id
            );

            // Problems? Send back.
            give_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['give-gateway']);
            return;
        }

        give_update_meta($payment_id, 'swipego_id', $response['data']['_id']);

        wp_redirect($response['data']['payment_url']);

        exit;
    }

    private function create_payment($purchase_data)
    {

        $form_id = intval($purchase_data['post_data']['give-form-id']);
        $price_id = isset($purchase_data['post_data']['give-price-id']) ? $purchase_data['post_data']['give-price-id'] : '';

        // Collect payment data.
        $insert_payment_data = array(
            'price' => $purchase_data['price'],
            'give_form_title' => $purchase_data['post_data']['give-form-title'],
            'give_form_id' => $form_id,
            'give_price_id' => $price_id,
            'date' => $purchase_data['date'],
            'user_email' => $purchase_data['user_email'],
            'user_phone' => $purchase_data['post_data']['give_phone'],
            'purchase_key' => $purchase_data['purchase_key'],
            'currency' => give_get_currency($form_id, $purchase_data),
            'user_info' => $purchase_data['user_info'],
            'status' => 'pending',
            'gateway' => 'swipego',
        );

        /**
         * Filter the payment params.
         *
         * @since 3.0.2
         *
         * @param array $insert_payment_data
         */
        $insert_payment_data = apply_filters('give_create_payment', $insert_payment_data);

        // Record the pending payment.
        return give_insert_payment($insert_payment_data);
    }

    private function get_swipego($purchase_data)
    {

        $form_id = intval($purchase_data['post_data']['give-form-id']);

        $custom_donation = give_get_meta($form_id, 'swipego_customize_swipego_donations', true, 'global');
        $status = give_is_setting_enabled($custom_donation, 'enabled');

        if ($status) {
            return array(
                'business_id' => give_get_meta($form_id, 'swipego_business_id', true),
                'environment' => give_get_meta($form_id, 'swipego_environment', true),
                'api_key' => give_get_meta($form_id, 'swipego_api_key', true),
                'signature_key' => give_get_meta($form_id, 'swipego_signature_key', true),
            );
        }
        return array(
            'business_id' => give_get_option('swipego_business_id'),
            'environment' => give_get_option('swipego_environment'),
            'api_key' => give_get_option('swipego_api_key'),
            'signature_key' => give_get_option('swipego_signature_key'),
        );
    }

    public function getData($form_id)
    {
        $custom_donation = give_get_meta($form_id, 'swipego_customize_swipego_donations', true, 'global');
        $status = give_is_setting_enabled($custom_donation, 'enabled');

        if ($status) {
            return array(
                'business_id'   => give_get_meta($form_id, 'swipego_business_id', true),
                'environment'   => give_get_meta($form_id, 'swipego_environment', true),
                'api_key'       => give_get_meta($form_id, 'swipego_api_key', true),
                'signature_key' => give_get_meta($form_id, 'swipego_signature_key', true),
            );
        }
        return array(
            'business_id'   => give_get_option('swipego_business_id'),
            'environment'   => give_get_option('swipego_environment'),
            'api_key'       => give_get_option('swipego_api_key'),
            'signature_key' => give_get_option('swipego_signature_key'),
        );
    }

    public static function get_listener_url($payment_id)
    {
        $passphrase = get_option(self::LISTENER_PASSPHRASE, false);

        if (!$passphrase) {
            $passphrase = md5(site_url() . time());
            update_option(self::LISTENER_PASSPHRASE, $passphrase);
        }

        $arg = array(
            self::QUERY_VAR => $passphrase,
            'payment_id' => $payment_id,
        );
        return add_query_arg($arg, site_url('/'));
    }

    public function give_filter_swipe_gateway($gateway_list, $form_id)
    {
        if ((false === strpos($_SERVER['REQUEST_URI'], '/wp-admin/post-new.php?post_type=give_forms'))
            && $form_id
            && !give_is_setting_enabled(give_get_meta($form_id, 'swipego_customize_swipego_donations', true, 'global'), array('enabled', 'global'))
        ) {
            unset($gateway_list['swipego']);
        }
        return $gateway_list;
    }

    public function give_swipego_success_page_content($content)
    {
        if (!isset($_GET['payment-id']) && !give_get_purchase_session()) {
            return $content;
        }

        $payment_id = isset($_GET['payment-id']) ? absint($_GET['payment-id']) : false;

        if (!$payment_id) {
            $session    = give_get_purchase_session();
            $payment_id = give_get_donation_id_by_key($session['purchase_key']);
        }

        $payment = get_post($payment_id);
        if ($payment && 'pending' === $payment->post_status) {

            // Payment is still pending so show processing indicator to fix the race condition.
            ob_start();

            give_get_template_part('payment', 'processing');

            $content = ob_get_clean();
        }

        return $content;
    }

    public function give_swipego_cc_form($form_id)
    {
        printf(
            '
            <fieldset class="no-fields">
                <div style="display: flex; justify-content: center; margin-top: 20px;">
                <img class="object-contain h-10 m-auto sm:mr-0 order-first sm:order-last" src="' . esc_attr(SWIPEGO_URL . 'assets/images/logo-swipe.svg') . '">
                </div>
                <p style="text-align: center;">
                    <b>%1$s</b> %2$s
                </p>
            </fieldset>
            ',
            esc_html__('Pay Using Swipe Go: ', 'give'),
            esc_html__('Pay with Maybank2u, CIMB Clicks, Bank Islam, RHB, Hong Leong Bank, Bank Muamalat, Public Bank, Alliance Bank, Affin Bank, AmBank, Bank Rakyat, UOB, Standard Chartered, Boost, e-Wallet.', 'give')
        );

        return true;
    }
}

Swipego_GWP_Gateway::get_instance();

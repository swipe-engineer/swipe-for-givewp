<?php
if (!defined('ABSPATH')) exit;


// Display notice
function swipego_gwp_notice($message, $type = 'success')
{

    $plugin = esc_html__('Swipe for GiveWP', 'swipego-gwp');

    printf('<div class="notice notice-%1$s"><p><strong>%2$s:</strong> %3$s</p></div>', esc_attr($type), $plugin, $message);
}

// Log a message in GiveWP logs
function swipego_gwp_logger($message)
{
    do_action( 'logger', $message );
}

// Get approved businesses from Swipe
function swipego_gwp_get_businesses()
{

    try {

        $swipego = new Swipego_GWP_API();

        $swipego->set_access_token(swipego_get_access_token());

        list($code, $response) = $swipego->get_approved_businesses();

        $data = isset($response['data']) ? $response['data'] : false;

        $businesses = array();

        if (is_array($data)) {

            foreach ($data as $item) {

                $business_id = isset($item['id']) ? sanitize_text_field($item['id']) : null;
                $webhookEvent = [];

                if (!$business_id) {
                    continue;
                }

                if (isset($item['integration']['webhook_events'])) {
                    foreach ($item['integration']['webhook_events'] as $webhook) {
                        if (!isset($webhook['_id'])) {
                            continue;
                        }

                        if ($webhook['name'] == 'payment.created') {
                            $webhookEvent = $webhook;
                            break;
                        }
                    }
                }

                $businesses[$business_id] = array(
                    'id'              => $business_id,
                    'name'            => isset($item['name']) ? sanitize_text_field($item['name']) : null,
                    'integration_id'  => isset($item['integration']['id']) ? sanitize_text_field($item['integration']['id']) : null,
                    'api_key'         => isset($item['integration']['api_key']) ? sanitize_text_field($item['integration']['api_key']) : null,
                    'signature_key'   => isset($item['integration']['signature_key']) ? sanitize_text_field($item['integration']['signature_key']) : null,
                    'webhook_name'    => isset($webhookEvent['name']) ? sanitize_text_field($webhookEvent['name']) : null,
                    'webhook_url'     => isset($webhookEvent['url']) ? sanitize_text_field($webhookEvent['url']) : null,
                    'webhook_enabled' => isset($webhookEvent['enabled']) ? sanitize_text_field($webhookEvent['enabled']) : null,
                );
            }
        }

        return $businesses;
    } catch (Exception $e) {
        return false;
    }
}

// Get business information from Swipe by its ID
function swipego_gwp_get_business($business_id)
{
    $businesses = swipego_gwp_get_businesses();
    return isset($businesses[$business_id]) ? $businesses[$business_id] : false;
}

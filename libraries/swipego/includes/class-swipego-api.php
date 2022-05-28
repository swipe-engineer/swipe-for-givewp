<?php
if (!defined('ABSPATH')) exit;

class Swipego_API extends Swipego_Client
{

    const LISTENER_PASSPHRASE = 'swipego_givewp_webhook_passphrase';


    public function sign_in(array $params)
    {
        return $this->post('auth/sign-in', $params);
    }

    public function get_approved_businesses()
    {

        if (!$this->access_token) {
            throw new Exception('Missing access token.');
        }

        return $this->get('approved-businesses');
    }

    public function get_webhooks($business_id, $integration_id)
    {

        if (!$this->access_token) {
            throw new Exception('Missing access token.');
        }

        return $this->get('integrations/businesses/' . $business_id . '/integrations/' . $integration_id . '/webhook-events');
    }

    public function store_webhook($business_id, $integration_id, array $params)
    {

        if (!$this->access_token) {
            throw new Exception('Missing access token.');
        }

        return $this->post('integrations/businesses/' . $business_id . '/integrations/' . $integration_id . '/webhook-events', $params);
    }

    public function update_webhook($business_id, $integration_id, $webhook_event_id = null, array $params)
    {

        if (!$this->access_token) {
            throw new Exception('Missing access token.');
        }

        return $this->patch('integrations/businesses/' . $business_id . '/integrations/' . $integration_id . '/webhook-events/' . $webhook_event_id, $params);
    }

    public function delete_webhook($business_id, $integration_id, $event_id, array $params)
    {

        if (!$this->access_token) {
            throw new Exception('Missing access token.');
        }

        return $this->delete('integrations/businesses/' . $business_id . '/integrations/' . $integration_id . '/webhook-events/' . $event_id, $params);
    }

    public function get_webhook($business_id, $integration_id, $event_name = 'payment.created')
    {
        $this->set_access_token(swipego_get_access_token());

        if (!$this->access_token) {
            throw new Exception('Missing access token.');
        }

        list($code, $response) = $this->get_webhooks($business_id, $integration_id);

        $webhooks = isset($response['data']['data']) ? $response['data']['data'] : array();

        if ($webhooks) {
            foreach ($webhooks as $webhook) {
                if (!isset($webhook['_id'])) {
                    continue;
                }

                if ($webhook['name'] == $event_name) {
                    return $webhook;
                }
            }
        }

        return null;
    }

    public function set_webhook($business_id, $integration_id, $webhook_event_id = null, $event_name = 'payment.created')
    {

        $this->set_access_token(swipego_get_access_token());

        if (!$this->access_token) {
            throw new Exception('Missing access token.');
        }

        if (isset($webhook_event_id)) {
            list($code, $response) = $this->update_webhook($business_id, $integration_id, $webhook_event_id, array(
                'url'     => $this->get_webhook_url(),
                'enabled' => true,
            ));
            return $response;
        }

        $webhook = $this->get_webhook($business_id, $integration_id);

        if (isset($webhook['name']) && $webhook['name'] == $event_name) {
            list($code, $response) =  $this->update_webhook($business_id, $integration_id, $webhook['_id'], array(
                'url'     => $this->get_webhook_url(),
                'enabled' => true,
            ));
            return $response;
        }

        list($code, $response) = $this->store_webhook($business_id, $integration_id, array(
            'name'    => $event_name,
            'url'     => $this->get_webhook_url(),
            'enabled' => true,
        ));
        return $response;
    }

    public function get_webhook_url()
    {
        return add_query_arg( 'callback', 'swipego_gwp_gateway', site_url( '/' ) );
    }

    public function create_payment_link(array $params)
    {

        if (!$this->api_key) {
            throw new Exception('Missing API key.');
        }

        return $this->post('payment-links', $params);
    }

    public function get_payment_link($payment_id)
    {

        if (!$this->api_key) {
            throw new Exception('Missing API key.');
        }

        return $this->get('payment-links/' . $payment_id);
    }
}

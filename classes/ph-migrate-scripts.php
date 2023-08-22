<?php

/**
 * Script loader for website commenting
 *
 * @package ProjectHuddle
 * @subpackage File Uploads
 */

/**
 * Script loader class
 */
class PH_Migrate_Scripts
{

	/**
	 * PH_Migrate_Scripts constructor.
	 */
	public function __construct()
	{

        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

        // add_action( 'wp_ajax_ph_migrate_button', array( $this, 'getFreemiusData' ) );
        // add_action( 'wp_ajax_nopriv_ph_migrate_button', array( $this, 'getFreemiusData' ) );

        // add_action( 'wp_ajax_ph_migrate_button', array( $this, 'getTestData' ) );
        // add_action( 'wp_ajax_nopriv_ph_migrate_button', array( $this, 'getTestData' ) );

        // add_action( 'rest_api_init', array( $this, 'ph_migration_register_webhook_endpoint' ) );

	}

    public function ph_migration_register_webhook_endpoint() {

        register_rest_route( 'ph-migration/v1', '/webhook', array(
            'methods' => 'GET',
            'callback' => array( $this, 'ph_migration_handle_webhook' ),
        ));

    }

    public function ph_migration_handle_webhook( $request ) {

        $data = $request->get_json_params(); // Received JSON data from Freemius

        // $webhookURL = "https://webhook.suretriggers.com/suretriggers/bf263436-3dd4-11ee-a01f-662f04b50926";

        // print_r( $data );

        // Send the POST request to the webhook using wp_remote_post
        // $webhook_response = wp_remote_post( $webhookURL, array(
        //     "body" => $data,
        // ));

        // print_r( $webhook_response );
        
        if ( isset($data['type']) && $data['type'] === 'payment.created' ) {

            // Handle the renewal payment event
            // You can extract necessary information from $data and process it.
                // $user_id = isset($data['user_id']) ? $data['user_id'] : '';

                // $api = $this->getFreemiusApi();        
        
                // $store_id = $this->_get_store_id( $api );

                // $user_data = $this->_find_user_by_email( $api, $store_id, $user_id );

                // $user_email = isset( $user_data['email'] ) ? $user_data['email'] : '';

            // Write logic to find if a user exists with the email address.
            $user_email = $data->objects->user->email;

            $amount = $data->objects->payment->gross;

            $is_renewal = $data->objects->payment->is_renewal;

            $is_renewal = $is_renewal ? 'Yes' : 'No';

        }
    
        // Include the received data in the response
        $response_data = array(
            'message' => 'Webhook received.',
            'data' => $data // Include the received data here
        );

        return new WP_REST_Response( $response_data, 200 );
    }
	
	/**
     * Function that loads scripts
     *
     * @since 1.0
     */
    function load_scripts() {

        if( is_user_logged_in() ) {

            // Register the script first/*98784
            wp_register_script( 'ph-migration-js', PH_MIGRATE_PLUGIN_URL . 'assets/ph-migration.js', array( 'jquery' ), '', true );

            wp_register_style( 'ph-migrate-css', PH_MIGRATE_PLUGIN_URL . 'assets/ph-migrate-style.css', array(), '1.0');

            // Localize the script
            wp_localize_script(
                'ph-migration-js',
                'phMigrationScript',
                array(
                    'nonce'     => wp_create_nonce('ph_migrate_nonce'),
                    'ajaxurl'   => admin_url('admin-ajax.php'),
                )
            );

            // Enqueue the script
            wp_enqueue_script( 'ph-migration-js' );
            wp_enqueue_style( 'ph-migrate-css' );

        }

    }

    public function getFreemiusApi() {
        $api = new Freemius_Api_WordPress( PH_ACCESS_TYPE, PH_DEV_ID, PH_DEV_PUBLIC_KEY, PH_DEV_SECRET_KEY );

        return $api;
    }

    public function getTestData() {
        check_ajax_referer( 'ph_migrate_nonce', 'nonce' );

        $arguments = isset( $_POST['data'] ) ? $_POST['data'] : [];

        $url = add_query_arg( $arguments, 'https://projecthuddle.local/wp-json/ph-migrate-rest/v1/webhook/' ); // add URL of your site or mail API.

        $response = wp_remote_post( $url, [ 'timeout' => 60 ] );

        wp_send_json( $response );
    }

    public function getFreemiusData() {

        check_ajax_referer( 'ph_migrate_nonce', 'nonce' );

        $result = [];
        $response  = array();
        $data      = array();
        $plugin_id = '5368';

        // if ( isset( $_POST['data'] ) ) {

		// 	$data = $_POST['data'];
        // }

        $email = 'sara@yeldomarketing.co.uk';
    
        $api = $this->getFreemiusApi();        
    
        $store_id = $this->_get_store_id( $api );
    
        if( ! $store_id ) {
            wp_send_json_error( 'Invalid Authorization' );
        }

        $result['user'] = $this->_find_user_by_email( $api, $store_id, $email );

        $user_id = isset( $result['user']['id'] ) ? $result['user']['id'] : '';

        if( ! $user_id ) {
            wp_send_json_error( 'User not found!' );
        }
    
        $result['licenses'] = $this->_find_license_by_user( $api, $store_id, $user_id );

        $result['billing'] = $this->_find_billing_details( $api, $store_id, $user_id );

        $result['installs'] = $this->_find_installation_details( $api, $user_id, $plugin_id );

        $webhookURL = "https://webhook.suretriggers.com/suretriggers/2f58851a-3766-11ee-ae57-662f04b50926";

        $data = array(
            'email' => isset($result['user']['email']) ? $result['user']['email'] : '',
            'first_name' => isset($result['user']['first']) ? $result['user']['first'] : '',
            'last_name' => isset($result['user']['last']) ? $result['user']['last'] : '',
            'phone' => isset($result['billing']->phone) ? $result['billing']->phone : '',
            'address_name' => isset($result['billing']->business_name) ? $result['billing']->business_name : '',
            'address_line_1' => isset($result['billing']->address_street) ? $result['billing']->address_street : '',
            'address_line_2' => isset($result['billing']->address_apt) ? $result['billing']->address_apt : '',
            'address_city' => isset($result['billing']->address_city) ? $result['billing']->address_city : '',
            'address_country' => isset($result['billing']->address_country) ? $result['billing']->address_country : '',
            'country_code' => isset($result['billing']->address_country_code) ? strtoupper( $result['billing']->address_country_code ) : 'US',
            'address_state' => isset($result['billing']->address_state) ? $result['billing']->address_state : '',
            'address_zip' => isset($result['billing']->address_zip) ? $result['billing']->address_zip : '',
            'price' => isset($result['installs']['gross']) ? $result['installs']['gross'] : '',
            'customer_id' => $user_id,
            'license_key' => isset($result['licenses']->licenses[0]) ? $result['licenses']->licenses[0]->secret_key : '',
            'expiration' => isset($result['licenses']->licenses[0]) ? $result['licenses']->licenses[0]->expiration : '',
            'subscribed' => isset($result['user']['is_marketing_allowed']) ? $result['user']['is_marketing_allowed'] : '',
        );

        // print_r( $data );

        // Send the POST request to the webhook using wp_remote_post
        $webhook_response = wp_remote_post( $webhookURL, array(
            "body" => $data,
        ));

        if (is_wp_error($webhook_response)) {
            // echo "Webhook call error: " . $response->get_error_message();
            $response['success'] = false;
        } else {
            // echo "Webhook call response: " . wp_remote_retrieve_body($response);
            $response['success'] = true;
        }

        wp_send_json( $response );
    
        // return $result;
    }
    
    public function _get_store_id( $api ) {

        $result = $api->Api("dashboard.json?format=json");

        if( ! isset( $result->stores[0] ) ) {
            wp_send_json_error( 'Invalid Authorization' );
        }

        return $result->stores[0]->id;
    }
    
    public function _find_user_by_email( $api, $store_id, $email_id ) {

        $users = $api->Api("stores/{$store_id}/users.json?format=json&search={$email_id}");
        
        if ( empty($users->users) ) {
            // No users found with the provided email
            error_log("No users found with email: $email_id");
            return ['error' => "No users found with the provided email"];
        }
    
        $user = isset($users->users[0]) ? get_object_vars($users->users[0]) : [];
        
        return $user;			
        
    }
    
    public function _find_license_by_user( $api, $store_id, $user_id ) {

        $licenseResponse = $api->Api("stores/{$store_id}/users/{$user_id}/licenses.json?format=json&count=100");

        if (isset($licenseResponse->error)) {
            $errorMessage = $licenseResponse->error->message;
            $errorCode = $licenseResponse->error->code;

            // Return an appropriate response or error data
            return ['error' => $errorMessage];
        }

        // $license = isset($licenseResponse->licenses[0]) ? get_object_vars($licenseResponse->licenses[0]) : [];

        // Process the licenses data.
        return $licenseResponse;
        
    }

    public function _find_billing_details( $api, $store_id, $user_id ) {  
        
        $billingResponse = $api->Api("stores/{$store_id}/users/{$user_id}/billing.json?format=json&count=100");

        if (isset($billingResponse->error)) {
            $errorMessage = $billingResponse->error->message;
            $errorCode = $billingResponse->error->code;

            // Return an appropriate response or error data
            return ['error' => $errorMessage];
        }

        // Process the licenses data.
        return $billingResponse;

    }

    public function _find_installation_details( $api, $user_id, $plugin_id ) {  
        
        $installation = $api->Api("plugins/{$plugin_id}/installs.json?user_id={$user_id}");

        if (isset($installation->error)) {
            $errorMessage = $installation->error->message;
            $errorCode = $installation->error->code;

            // Return an appropriate response or error data
            return ['error' => $errorMessage];
        }

        $installs = isset($installation->installs[0]) ? get_object_vars($installation->installs[0]) : [];

        // Process the licenses data.
        return $installs;

    }

}
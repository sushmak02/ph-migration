<?php
/**
 * PH Migration Call API
 *
 * @package PH Migration Call
 * @since 1.0.0
 */

if ( ! class_exists( 'PH_Migration_Rest_API' ) ) :

	/**
	 * PH_Migration_Rest_API
	 *
	 * @since 1.0.0
	 */
	class PH_Migration_Rest_API {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function set_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'rest_api_init', array( $this, 'add_subscription' ) );
		}

		/**
		 * Add Subscription's
		 *
		 * @return void
		 */
		function add_subscription() {
			register_rest_route(
				'ph-migrate-rest/v1',
				'/webhook',
				array(
					array(
						'methods'             => 'POST, GET',
						'callback'            => array( $this, 'subscribe' ),
						'permission_callback' => '__return_true',
					),
				)
			);
		}

		/**
		 * Add Subscription
		 *
		 * @param array $request Request Rest API Params.
		 * @return array
		 */
		function subscribe( $request ) {

			$data = $request->get_params(); // Received JSON data from Freemius

			// error_log( print_r( $data, true ) );

			$data_array = array(
				'name' => ( isset( $data['name'] ) && ! empty( $data['name'] ) ) ? sanitize_text_field( $data['name'] ) : 'Tester',
			);

			$webhookURL = "https://webhook.suretriggers.com/suretriggers/8e3609b8-40ce-11ee-a01f-662f04b50926";

			// Send the POST request to the webhook using wp_remote_post
			$webhook_response = wp_remote_post( $webhookURL, array(
			    "body" => $data_array
			));

			$response_data = array(
				'message' => 'Webhook received.',
				'data' => $data // Include the received data here
			);

			error_log( print_r( $data, true ));
	
			return new WP_REST_Response( $response_data, 200 );

		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	PH_Migration_Rest_API::set_instance();

endif;

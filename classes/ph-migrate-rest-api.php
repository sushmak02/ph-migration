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
			add_action( 'rest_api_init', array( $this, 'addPhWebhook' ) );
		}

		/**
		 * Add Subscription's
		 *
		 * @return void
		 */
		function addPhWebhook() {
			register_rest_route(
				'ph-migrate-rest/v1',
				'/webhook',
				array(
					array(
						'methods'             => 'POST, GET',
						'callback'            => array( $this, 'getFreemiusData' ),
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
		function getFreemiusData( $request ) {

			$data = $request->get_params(); // Received JSON data from Freemius

			// $data = $request->get_json_params(); // Received JSON data from Freemius

			if ( isset($data['type']) && $data['type'] === 'payment.created' ) {

				// Handle the renewal payment event	
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

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	PH_Migration_Rest_API::set_instance();

endif;

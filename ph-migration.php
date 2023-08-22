<?php

/**
 * Plugin Name: ProjectHuddle Migration
 * Plugin URI: http://projecthuddle.com
 * Description: Get users data from Freemius and provides a webhook to migrate users from Freemius to SureCart.
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Version: 1.0.0
 *
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('PH_Migration')) {

    /**
	 * Main PH_Migration Class
	 * Uses singleton design pattern
	 *
	 * @since 1.0.0
	 */
	final class PH_Migration {

        /**
		 * Holds only one PH_Migration instance
		 *
		 * @var $instance
		 * @since 1.0
		 */
		private static $instance;

        /**
		 * Main PH_Migration Instance
		 *
		 * @return PH_Migration $instance The one true PH_Migration
		 */
		public static function instance()
		{
			if (!isset(self::$instance) && !(self::$instance instanceof PH_Migration)) {
                
				self::$instance = new PH_Migration();

                // set up constants immediately.
				self::$instance->setup_constants();

                // get all includes.
				self::$instance->includes();

                // classes.
				self::$instance->scripts = new PH_Migrate_Scripts();

				// Loaded action.
				do_action('ph_migration_loaded');

			}

			return self::$instance;
		}

        /**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function setup_constants()
		{

			// Plugin Folder Path.
			if (!defined('PH_MIGRATE_PLUGIN_DIR')) {
				define('PH_MIGRATE_PLUGIN_DIR', dirname( __FILE__ ));
			}

			// Plugin Folder URL.
			if (!defined('PH_MIGRATE_PLUGIN_URL')) {
				define('PH_MIGRATE_PLUGIN_URL', plugin_dir_url(__FILE__));
			}

		}

        /**
		 * Include required files
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function includes()
		{
            error_log( PH_MIGRATE_PLUGIN_DIR );
            require_once PH_MIGRATE_PLUGIN_DIR . '/freemius/start.php';

            if ( ! class_exists( 'Freemius_Api_WordPress' ) ) {
                require_once WP_FS__DIR_SDK . '/FreemiusWordPress.php';
            }

            require_once PH_MIGRATE_PLUGIN_DIR . '/classes/ph-migrate-scripts.php';
            require_once PH_MIGRATE_PLUGIN_DIR . '/classes/ph-migrate-rest-api.php';
            
            // $freemius_data = $this->getFreemiusData();

            // error_log( print_r( $freemius_data, true ) );

        }

        public function getFreemiusData() {

            $result = [];

            $email = 'sushmatest@gmail.com';
        
			$api = new Freemius_Api_WordPress( PH_ACCESS_TYPE, PH_DEV_ID, PH_DEV_PUBLIC_KEY, PH_DEV_SECRET_KEY );


            $store_id = $this->_get_store_id( $api );

			if( ! $store_id ) {
                return;
            }

			$result = $api->Api("plugins/5368/events/1092009868.json");

        
            $user_data = $this->_find_user_by_email( $api, $store_id, '7351921' );

            $user_email = isset( $user_data['email'] ) ? $user_data['email'] : '';

			// $user_id = isset( $result['user']['id'] ) ? $result['user']['id'] : '';

			// if( ! $user_id ) {
            //     return;
            // }

			// $activations = $api->Api("plugins/5368/installs.json?user_id=7314373");

            error_log( print_r( $user_email, true ) );
            // error_log( "===== ");

        
            // $result['licenses'] = $this->_find_license_by_user( $api, $store_id, $user_id );

            // $result['billing'] = $this->_find_billing_details( $api, $store_id, $user_id );
        
            return $result;

			
        }
        
        public function _get_store_id( $api ) {
            $result = $api->Api("dashboard.json?format=json");
            if( ! isset( $result->stores[0] ) ) {
                return false;
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

			// Process the licenses data
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

			// Process the licenses data
			return $billingResponse;

        }

    }
}

if ( !function_exists('PH_Freemius_Migrate') ) {
	// phpcs:ignore
	function PH_Freemius_Migrate($abstract = null, array $parameters = [])
	{
		return PH_Migration::instance();
	}

	// Get PH_Freemius_Migrate Running.
	PH_Freemius_Migrate();
}
?>
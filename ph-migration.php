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
			
            require_once PH_MIGRATE_PLUGIN_DIR . '/freemius/start.php';

            if ( ! class_exists( 'Freemius_Api_WordPress' ) ) {
                require_once WP_FS__DIR_SDK . '/FreemiusWordPress.php';
            }

            require_once PH_MIGRATE_PLUGIN_DIR . '/classes/ph-migrate-scripts.php';
            require_once PH_MIGRATE_PLUGIN_DIR . '/classes/ph-migrate-rest-api.php';

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
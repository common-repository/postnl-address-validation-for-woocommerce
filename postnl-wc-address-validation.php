<?php
/**
 * Plugin Name:     PostNL Address Validation for WooCommerce
 * Plugin URI:      https://www.postnl.nl
 * Description:     With the PostNL Address Validation for WooCommerce plug-in you can easily find a Dutch or International address in the check out. This way you always ship to the right address.
 * Author: PostNL
 * Author URI: https://postnl.nl
 * Text Domain:     postnl-wc-address-validation
 * Domain Path:     /languages
 * Version:           1.0.9
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Tested up to:      6.5.3
 * WC requires at least: 6.0
 */

/* This plugin has been deprecated. There is no longer support for this plugin and from July 1st 2024 this plugin will not be available anymore. Please refer to the PostNL for WooCommerce plug-in. With this plug-in it is also possible to validate Dutch address data while it’s entered.. */

use PostNL\AddressValidation\CheckoutFields;
use PostNL\AddressValidation\CompatibilityCheck;
use PostNL\AddressValidation\RequestApi;
use PostNL\AddressValidation\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * PostNL Woocommerce Address Validation
 */
class PostNLWCAddressValidation {
	private static $instance;
	private $plugin_data;

	/**
	 * Main PostNl Address Validation loader
	 */
	private function __construct() {
		define( 'POSTNL_WC_ADDRESS_VALIDATION_FILE', plugin_basename( __FILE__ ) );
		define( 'POSTNL_WC_ADDRESS_VALIDATION_DIR', dirname( __FILE__ ) );

		$this->autoloader();
		$this->get_plugin_data();

		add_action( 'init', array( $this, 'load_classes' ) );
	}

	/**
	 * Class Loader
	 * @return void
	 */
	public function autoloader() {
		spl_autoload_register( function ( $className ) {
			if ( strpos( $className, 'PostNL\AddressValidation' ) === 0 ) {
				require_once plugin_dir_path( __FILE__ ) . 'include/classes/' . str_replace( '\\', '/', $className ) . '.php';
			}
		} );
	}

	/**
	 * Get plugin information
	 * @return void
	 */
	public function get_plugin_data(): array {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( null === $this->plugin_data ) {
			$this->plugin_data = get_plugin_data( __FILE__ );
		}

		return $this->plugin_data ?? array();
	}

	/**
	 * @return PostNLWCAddressValidation
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get plugin url
	 *
	 * @return string
	 */
	public function plugin_url(): string {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Load translations
	 *
	 * @return void
	 */
	public function translations() {
		/* load text-domain */
		$plugin_data = $this->get_plugin_data();
		load_plugin_textdomain( $plugin_data['TextDomain'], false, POSTNL_WC_ADDRESS_VALIDATION_DIR . '/languages/' );

	}

	/**
	 * Class Loader
	 */
	public function load_classes() {
		//Deprecation notice
		(new \PostNL\AddressValidation\DeprecationMessage())->register();

		$checker = new CompatibilityCheck();
		if ( $checker->is_plugin_compatible() === true ) {
			( new Settings() )->register();

			if ( $this->is_enabled() ) {
				new CheckoutFields();
				new RequestApi();
			}
		}
	}

	/**
	 * Is plugin enabled
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return Settings::is_enabled();
	}
}

/* Load main instance */
PostNLWCAddressValidation::get_instance();

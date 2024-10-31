<?php
/**
 * Project:     postnl-wc-address-validation
 * Author:      Lettow Studios
 * Date:        28/06/2022
 */

namespace PostNL\AddressValidation;

use PostNLWCAddressValidation;

class CompatibilityCheck {

	public function is_plugin_compatible(): bool {

		if ( $this->is_valid_wp_version() === false ) {
			add_action( 'admin_notices', array( $this, 'required_wp_version' ) );

			return false;
		}

		if ( $this->is_woocommerce_activated() === false ) {
			add_action( 'admin_notices', array( $this, 'required_plugin_woocommerce' ) );

			return false;
		}

		if ( $this->is_valid_php_version() === false ) {
			add_action( 'admin_notices', array( $this, 'required_php_version' ) );

			return false;
		}

		if ( $this->is_apikey_enabled() === false ) {
			add_action( 'admin_notices', array( $this, 'apikey_enabled' ) );

			return false;
		}

		return true;
	}

	/**
	 * Validate php version
	 * @return bool
	 */
	public function is_valid_wp_version(): bool {
		$data = ( PostNLWCAddressValidation::get_instance() )->get_plugin_data();

		if ( ! is_wp_version_compatible( $data['RequiresWP'] ) ) {
			add_action( 'admin_notices', [ $this, 'required_php_version' ], 10, 0 );

			return false;
		}

		return true;
	}

	/**
	 * Check if woocommerce is activated
	 * @return bool
	 */
	public function is_woocommerce_activated(): bool {
		return class_exists( 'woocommerce' );
	}

	/**
	 * is valid php version
	 * @return bool
	 */
	public function is_valid_php_version(): bool {
		$data = ( PostNLWCAddressValidation::get_instance() )->get_plugin_data();
		if ( ! is_php_version_compatible( $data['RequiresPHP'] ?? '' ) ) {
			add_action( 'admin_notices', array( $this, 'required_php_version' ) );

			return false;
		}

		return true;
	}

	/**
	 * PHP version error message
	 * @return void
	 */
	public function required_php_version() {
		$data  = ( PostNLWCAddressValidation::get_instance() )->get_plugin_data();
		$error = sprintf( __( '%1$s requires PHP %2$d or higher (%2$d or higher recommended).', 'postnl-wc-address-validation' ), $data['Name'] ?? '', $data['RequiresPHP'] ?? '' );
		$this->display_notification( $error );
	}

	/**
	 * Display Error message
	 *
	 * @param string $error
	 *
	 * @return void
	 */
	protected function display_notification( string $error, $type = 'error' ) {
		if ( ! is_admin() ) {
			return;
		}
		echo '<div class="' . esc_attr($type) . '"><p>' . esc_html($error) . '</p></div>';
	}

	/**
	 * WP version error message
	 * @return void
	 */
	public function required_wp_version() {
		$data  = ( PostNLWCAddressValidation::get_instance() )->get_plugin_data();
		$error = sprintf( __( '%1$s requires Wordpress %2$d or higher (%2$d or higher recommended).', 'postnl-wc-address-validation' ), $data['Name'] ?? '', $data['RequiresPHP'] ?? '' );
		$this->display_notification( $error );
	}

	/**
	 * Woocommerce requirement error message
	 * @return void
	 */
	public function required_plugin_woocommerce() {
		$data  = ( PostNLWCAddressValidation::get_instance() )->get_plugin_data();
		$error = sprintf( __( '%s requires %sWooCommerce%s to be installed & activated!', 'postnl-wc-address-validation' ), $data['Name'] ?? '', '<a href="https://wordpress.org/plugins/woocommerce/">', '</a>' );
		$this->display_notification( $error );
	}

	/**
	 * Check if Apikey is enabled and valid
	 * @return void
	 */
	public function is_apikey_enabled() {
		$is_active = empty( Settings::get_apikey() );
		if ( $is_active ) {
			$data = ( PostNLWCAddressValidation::get_instance() )->get_plugin_data();
			$url  = ( new Settings() )->get_settings_link();

			$error = sprintf( __( '%s API key needs to be activated - %s settings %s', 'postnl-wc-address-validation' ), $data['Name'] ?? '', '<a href="' . $url . '">', '</a>' );
			$this->display_notification( $error );
		}
	}
}

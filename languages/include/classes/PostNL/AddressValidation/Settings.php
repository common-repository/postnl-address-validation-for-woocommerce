<?php
/**
 * Project:     postnl-wc-address-validation
 * Author:      Lettow Studios
 * Date:        28/06/2022
 *
 * @package     postnl-wc-address-validation
 */

namespace PostNL\AddressValidation;

use PostNL\AddressValidation\API\PostNLAPI;
use PostNLWCAddressValidation;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin Settings
 */
class Settings {
	/**
	 * Error message
	 *
	 * @var $error
	 */
	protected $error = null;

	/**
	 * Default constructor
	 */
	public function __construct() {
	}

	/**
	 * Get API KEY
	 *
	 * @return false|mixed|void
	 */
	public static function get_apikey() {
		return get_option( 'woocommerce_postnlpcr_apikey', null );
	}

	/**
	 * Get option enabled
	 *
	 * @return false|mixed|void
	 */
	public static function get_enabled() {
		return get_option( 'woocommerce_postnlpcr_enabled', null );
	}

	/**
	 * Get API Region
	 *
	 * @return false|mixed|void
	 */
	public static function get_api_region() {
		return get_option( 'woocommerce_postnlpcr_region', 'default' );
	}

	/**
	 * Register woocommerce hooks
	 *
	 * @return void
	 */
	public function register() {
		/* add tab */
		add_action( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 21 );

		/* plugin tab settings and storage */
		$id = $this->get_tab_key();
		add_action( "woocommerce_settings_tabs_$id", array( $this, 'settings_tab' ) );
		add_action( "woocommerce_update_options_$id", array( $this, 'update_settings' ) );

		/* Plugin settings link */
		add_filter( 'plugin_action_links_' . POSTNL_WC_ADDRESS_VALIDATION_FILE, array( $this, 'settings_link' ) );
	}

	/**
	 * Get tab key
	 *
	 * @return string
	 */
	public function get_tab_key(): string {
		return 'postnl-postcodechecker';
	}

	/**
	 * Add a new settings tab to the WooCommerce settings tabs array.
	 *
	 * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding ours.
	 *
	 * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including ours.
	 */
	public function add_settings_tab( array $settings_tabs ): array {
		$data                                  = ( PostNLWCAddressValidation::get_instance() )->get_plugin_data();
		$settings_tabs[ $this->get_tab_key() ] = $data['Name'] ?? '';

		return $settings_tabs;
	}

	/**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @uses woocommerce_admin_fields()
	 * @uses $this->get_settings()
	 */
	public function settings_tab() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	/**
	 * Tab settings
	 *
	 * @return mixed|void
	 */
	public function get_settings() {

		$valid_service   = ( new PostNLAPI() )->validate_api();
		$api_key_enabled = ! is_wp_error( $valid_service );

		$api_key_enabled_text = $api_key_enabled ? __( 'Active', 'postnl-wc-address-validation' ) : __( 'Inactive', 'postnl-wc-address-validation' );
		$api_key_color        = $api_key_enabled ? 'green' : 'red';

		$api_key_enabled_label = sprintf( '<span style="color:%1$s">%2$s</span>', $api_key_color, $api_key_enabled_text );
		/* translators: %1$s: status label */
		$description  = '<p><strong>' .sprintf( __( 'API key is: %1$s', 'postnl-wc-address-validation' ), $api_key_enabled_label ) . '</strong><br/></p>';

		/* translators: %s: API url */
		$description .= '<p><strong>Request API-Key</strong></p>';
		$description .= '<p>To use the PostNL Address Validation for Woocommerce you need an API-Key.</p>';
		$description .= 'If you only want to have Dutch addresses validated, you need an API key for <strong>Adrescheck Nederland.</strong><br/>';
		$description .= 'If you also want to validate International addresses, you need an API key for <strong>Address Check International.</strong><br/>';
		$description .= '<p>'.sprintf( __( 'Go to request Address check API %s ', 'postnl-wc-address-validation' ), '<a href="https://www.postnl.nl/zakelijke-oplossingen/slimme-dataoplossingen/adrescheck/abonnement/" target="_blank" rel="noreferrer">Click here</a>' ).'</p>';

		$settings = array(
			array(
				'title' => __( 'Postcode Checker', 'postnl-wc-address-validation' ),
				'type'  => 'title',
				'id'    => 'woocommerce_postnlpcr_options',
			),
			array(
				'title'    => __( 'Enable', 'postnl-wc-address-validation' ),
				'id'       => 'woocommerce_postnlpcr_enabled',
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc_tip' => true,
			),
			array(
				'title'   => __( 'API Key', 'postnl-wc-address-validation' ),
				'desc'    => $description,
				'id'      => 'woocommerce_postnlpcr_apikey',
				'type'    => 'text',
				'default' => '',
			),
			array(
				'id'          => 'woocommerce_postnlpcr_region',
				'title'       => __( 'National / International', 'postnl-wc-address-validation' ),
				'description' => __( 'Use National or International API' ),
				'type'        => 'select',
				'options'     => array(
					'default'       => __( 'National ( The Netherlands )', 'postnl-wc-address-validation' ),
					'international' => __( 'International', 'postnl-wc-address-validation' ),
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'woocommerce_postnlpcr_options',
			),
		);

		return apply_filters( '/postnl/postcodechecker/wc/settings/tab', $settings );
	}

	/**
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 *
	 * @uses woocommerce_update_options()
	 * @uses $this->get_settings()
	 */
	public function update_settings() {
		woocommerce_update_options( $this->get_settings() );

		/* validate api key */
		$service  = new PostNLAPI();
		$response = $service->validate_api();

		if ( is_wp_error( $response ) ) {
			$this->set_error( $response );
			add_action( 'admin_notices', array( $this, 'show_notice' ) );

			/* Disable plugin errors in api key */
			$this->set_disabled();
		}
	}

	/**
	 * Show Admin error message
	 *
	 * @return void
	 */
	public function show_notice() {
		$error = $this->get_error();
		if ( $error ) {
			?>
			<div class="error notice">
				<p><?php echo esc_html( printf( __( 'There has been an error: %s', 'postnl-wc-address-validation' ), $this->get_error_message( $error ) ) ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Get Error message
	 *
	 * @param $error
	 *
	 * @return string
	 */
	public function get_error_message( $error ): string {

		$code = $error->get_error_code();

		$custom_errors = array(
			'oauth.v2.InvalidApiKeyForGivenResource' => __( 'Invalid ApiKey for given resource', 'postnl-wc-address-validation' ),
			'oauth.v2.InvalidApiKey'                 => __( 'Invalid ApiKey', 'postnl-wc-address-validation' ),
		);
		$message       = $custom_errors[ $code ] ?? $error->get_error_message();

		return apply_filters( '/postnl/postcodechecker/wc/settings/error_message', $message, $error );
	}

	/**
	 * Get Error
	 *
	 * @return null
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Set error error message
	 *
	 * @param null $error
	 */
	public function set_error( $error ): void {
		$this->error = $error;
	}

	/**
	 * Add link to settings
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function settings_link( array $links ) : array {
		$url           = $this->get_settings_link();
		$settings_link = sprintf( '<a href="%s">%s</a>', $url, __( 'Settings', 'postnl-wc-address-validation' ) );
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Get Settings link
	 *
	 * @return string
	 */
	public function get_settings_link() : string {
		return admin_url( 'admin.php?page=wc-settings&tab=' . $this->get_tab_key() );
	}

	/**
	 * Check if enabled
	 *
	 * @return bool
	 */
	public static function is_enabled() : bool {
		$enabled = self::get_enabled();
		$apikey  = self::get_apikey();

		return ( 'yes' === $enabled && ! empty( $apikey ) );
	}

	/**
	 * Disable plugin
	 *
	 * @return void
	 */
	public function set_disabled() {
		delete_option( 'woocommerce_postnlpcr_enabled' );
	}
}

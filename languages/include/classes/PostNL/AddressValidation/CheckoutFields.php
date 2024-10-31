<?php
/**
 * Project:     postnl-wc-address-validation
 * Author:      Lettow Studios
 * Date:        28/06/2022
 */

namespace PostNL\AddressValidation;

use PostNLWCAddressValidation;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CheckoutFields {

	public function __construct() {
		// Load styles & scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'add_styles_scripts' ) );
		//Add Fields
		add_filter( 'woocommerce_billing_fields', array( $this, 'add_checkout_billing_fields' ), 100, 2 );
		add_filter( 'woocommerce_shipping_fields', array( $this, 'add_checkout_shipping_fields' ), 100, 2 );
		//Locale field selectors
		add_filter( 'woocommerce_country_locale_field_selectors', array( $this, 'country_locale_field_selectors' ) );

		add_filter( 'woocommerce_default_address_fields', array( $this, 'set_checkout_default_fields' ) );
		add_filter( 'woocommerce_get_country_locale', array( $this, 'set_fields_for_locale' ), 50, 1 );

		/* Processing checkout */
		add_filter( 'woocommerce_checkout_posted_data', array(
			$this,
			'checkout_posted_data_set_address_field_1'
		), 9, 1 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'set_checkout_field_address_1' ), 20, 2 );

		//Woocommerce filters
		add_filter( 'woocommerce_my_account_my_address_formatted_address', array(
			$this,
			'user_address_add_data'
		), 1, 3 );

		add_filter( 'woocommerce_formatted_address_replacements', array(
			$this,
			'formatted_address_replacements'
		), 1, 2 );
	}

	/**
	 * Add plugin styles and scripts
	 * @return void
	 */
	public function add_styles_scripts() {

		if ( is_checkout() || is_account_page() ) {
			/* Plugin information */
			$plugin_data = ( PostNLWCAddressValidation::get_instance() )->get_plugin_data();
			$plugin_url  = ( PostNLWCAddressValidation::get_instance() )->plugin_url();

			/* Register scripts */
			wp_register_script( 'postnl-wc-address-validation-checkout', $plugin_url . '/assets/js/checkout.min.js', array( 'wc-checkout' ), $plugin_data['Version'], true );
			wp_enqueue_script( 'postnl-wc-address-validation-checkout' );

			/* Localize scripts */
			$settings = array(
				'url'   => add_query_arg( array( 'action' => 'postnl_api_request' ), admin_url( 'admin-ajax.php' ) ),
				'nonce' => wp_create_nonce( 'postnl-api-nonce' ),
				'i18n'  => array(
					'no_results'              => __( 'No results found', 'postnl-wc-address-validation' ),
					'no_results_long'         => __( 'We did our best to validate the address for you. Please check your input and try again.', 'postnl-wc-address-validation' ),
					'edit_address'            => __( 'Edit Address', 'postnl-wc-address-validation' ),
					'edit_address_validation' => __( 'The address will not be revalidated', 'postnl-wc-address-validation' ),
				),
			);
			wp_localize_script( 'postnl-wc-address-validation-checkout', 'postnl_pc_setting', $settings );

			/* Register Styles */
			wp_enqueue_style( 'wcnlpc-nl-checkout', $plugin_url . '/assets/css/style.css', array(), $plugin_data['Version'] );
		}
	}

	/**
	 * Add Billing fields
	 *
	 * @param $fields
	 * @param $country
	 *
	 * @return array
	 */
	public function add_checkout_billing_fields( $fields, $country = '' ) {
		return $this->add_checkout_fields( $fields, $country, 'billing' );
	}

	/**
	 * New checkout billing/shipping fields
	 *
	 * @param array $fields Default fields.
	 *
	 * @return array $fields New fields.
	 */
	public function add_checkout_fields( $fields, $country, $form ) {
		/* Custom fields City */
		$fields[ $form . '_city' ]['custom_attributes'] = array_merge( array( 'data-address-toggle-field' => false ), $fields[ $form . '_city' ]['custom_attributes'] ?? array() );

		/* Add house number */
		$fields[ $form . '_house_number' ] = [
			'label'             => _x( 'Number', 'Housenumber', 'postnl-wc-address-validation' ),
			'required'          => false,
			'hidden'            => true,
			'type'              => 'number',
			'class'             => array( 'form-row-first' ),
			'custom_attributes' => array(
				'data-fields-address'       => 'house_number',
				'data-address-toggle-field' => true,
				'min'                       => 0,
				'step'                      => 1,
			),
		];

		/* Add house number Suffix */
		$fields[ $form . '_house_number_suffix' ] = array(
			'label'             => _x( 'Suffix', 'housenummer suffix', 'postnl-wc-address-validation' ),
			'class'             => array( 'form-row-last' ),
			'required'          => false,
			'hidden'            => true,
			'custom_attributes' => array(
				'data-fields-address'       => 'house_number_suffix',
				'data-address-toggle-field' => true,
			),
		);

		/* Add Street name */
		$fields[ $form . '_street_name' ] = array(
			'label'             => __( 'Street name', 'postnl-wc-address-validation' ),
			'required'          => false,
			'autocomplete'      => 'street-address',
			'hidden'            => true,
			'custom_attributes' => array(
				'data-fields-address'       => 'street_name',
				'data-address-toggle-field' => true,
			),
		);

		/* International Search */
		$fields[ $form . '_address_line' ] = array(
			'label'        => _x( 'Rest of the address', 'address_line', 'postnl-wc-address-validation' ),
			'placeholder'  => __( 'Enter what applies, for example street name, house number, building, floor, door code, region', 'postnl-wc-address-validation' ),
			'required'     => false,
			'hidden'       => true,
			'autocomplete' => 'street-address',
			'type'         => 'textarea',
		);

		/* Add Street name */
		$fields[ $form . '_select_address' ] = array(
			'label'             => __( 'Select adres', 'postnl-wc-address-validation' ),
			'type'              => 'select',
			'hidden'            => true,
			'custom_attributes' => [ 'size' => 5, 'data-select-address' => $form, 'disabled' => 'disabled' ],
			'options'           => [ '' => __( 'No addresses available', 'postnl-wc-address-validation' ) ],
		);

		/* Move field positions */

		return $this->set_field_positions( $fields, $form, $country );
	}

	/**
	 * Move fields around
	 *
	 * @param array $fields
	 * @param string $form
	 * @param string $country
	 *
	 * @return array
	 */
	public function set_field_positions( $fields = [], string $form, string $country = '' ): array {

		$field_reference_key = $form . '_postcode';

		if ( isset( $fields[ $field_reference_key ]['priority'] ) ) {
			$reference_key_priority = intval( $fields[ $field_reference_key ]['priority'] );

			if ( isset( $fields[ $form . '_address_line' ] ) ) {
				$fields[ $form . '_address_line' ]['priority'] = $reference_key_priority + 1;
			}

			$fields[ $form . '_house_number' ]['priority']        = $reference_key_priority + 2;
			$fields[ $form . '_house_number_suffix' ]['priority'] = $reference_key_priority + 3;
			$fields[ $form . '_street_name' ]['priority']         = $reference_key_priority + 4;
			$fields[ $form . '_state' ]['priority']               = $reference_key_priority + 5;

			if ( isset( $fields[ $form . '_select_address' ] ) ) {
				$fields[ $form . '_select_address' ]['priority'] = $reference_key_priority + 7;
			}
		}

		return $fields;
	}

	/**
	 * Set Local fields
	 *
	 * @param array $locale woocommerce country locale field settings
	 *
	 */
	public function set_fields_for_locale( array $locale ): array {

		$allowed_countries = $this->get_countries_available();
		foreach ( $locale as $country_code => $fields ) {
			$allowed_country = in_array( $country_code, $allowed_countries, true );

			if ( $allowed_country ) {
				$locale[ $country_code ]['city']['priority']     = 62;
				$locale[ $country_code ]['postcode']['priority'] = 63;

				$locale[ $country_code ]['postcode']['required'] = true;

				$locale[ $country_code ]['address_1']['hidden']   = true;
				$locale[ $country_code ]['address_1']['required'] = false;

				$locale[ $country_code ]['address_2']['hidden']   = true;
				$locale[ $country_code ]['address_2']['required'] = false;

				/* Set state to text */
				$locale[ $country_code ]['state']['type'] = 'text';
			} else {
				$locale[ $country_code ]['city']['hidden']     = false;
				$locale[ $country_code ]['postcode']['hidden'] = false;
			}

			if ( ! $allowed_country && $country_code !== 'NL' ) {
				$locale[ $country_code ]['address_1']['hidden'] = false;
				$locale[ $country_code ]['address_2']['hidden'] = false;
			}

			$locale[ $country_code ]['house_number'] = array_merge( $locale[ $country_code ]['house_number'] ?? [], [
				'required' => $allowed_country,
				'hidden'   => true,
			] );

			$locale[ $country_code ]['house_number_suffix'] = array_merge( $locale[ $country_code ]['house_number_suffix'] ?? [], [
				'required' => false,
				'hidden'   => true,
			] );

			$locale[ $country_code ]['street_name'] = array_merge( $locale[ $country_code ]['street_name'] ?? [], [
				'required' => $allowed_country,
				'hidden'   => true,
			] );

			$locale[ $country_code ]['select_address'] = array_merge( $locale[ $country_code ]['select_address'] ?? [], [
				'hidden' => ! $allowed_country,
			] );

			/* Additional search */
			$locale[ $country_code ]['address_line'] = array(
				'hidden' => ! $allowed_country,
			);

			if ( $country_code === 'NL' ) {
				$locale[ $country_code ]['address_line'] = array(
					'hidden' => true,
				);

				/* Required search fields NL */

				/* Postcode field */
				$locale[ $country_code ]['postcode']['hidden'] = false;

				$locale[ $country_code ]['city']['hidden'] = true;

				/* House numbers */
				$locale[ $country_code ]['house_number']['hidden']        = false;
				$locale[ $country_code ]['house_number_suffix']['hidden'] = false;
			}
		}

		return $locale;
	}

	/**
	 * get Allowed countries for adresfields
	 *
	 * @return mixed|void
	 */
	public function get_countries_available() {
		/* National addresses only */
		if ( Settings::get_api_region() === 'default' ) {
			return $this->get_countries_national();
		}

		/*
		 * International locations
		 * https://developer.postnl.nl/Images/country-codes-iso-3166-1_tcm85-208285.pdf?v=122c53d9bb7b231ffad7534882a4d7dc
		*/

		return array(
			'AF',
			'AL',
			'DZ',
			'AS',
			'AD',
			'AO',
			'AI',
			'AQ',
			'AG',
			'AR',
			'AM',
			'AW',
			'AU',
			'AT',
			'AZ',
			'BS',
			'BH',
			'BD',
			'BB',
			'BY',
			'BE',
			'BZ',
			'BJ',
			'BM',
			'BT',
			'BO',
			'BQ',
			'BA',
			'BW',
			'BV',
			'BR',
			'IO',
			'BN',
			'BG',
			'BF',
			'BI',
			'CV',
			'KH',
			'CM',
			'CA',
			'KY',
			'CF',
			'TD',
			'CL',
			'CN',
			'CX',
			'CC',
			'CO',
			'KM',
			'CG',
			'CD',
			'CK',
			'CR',
			'HR',
			'CU',
			'CW',
			'CY',
			'CZ',
			'CI',
			'DK',
			'DJ',
			'DM',
			'DO',
			'EC',
			'EG',
			'SV',
			'GQ',
			'ER',
			'EE',
			'SZ',
			'ET',
			'FK',
			'FO',
			'FJ',
			'FI',
			'FR',
			'GF',
			'PF',
			'TF',
			'GA',
			'GM',
			'GE',
			'DE',
			'GH',
			'GI',
			'GR',
			'GL',
			'GD',
			'GP',
			'GU',
			'GT',
			'GG',
			'GN',
			'GW',
			'GY',
			'HT',
			'HM',
			'VA',
			'HN',
			'HK',
			'HU',
			'IS',
			'IN',
			'ID',
			'IR',
			'IQ',
			'IE',
			'IM',
			'IL',
			'IT',
			'JM',
			'JP',
			'JE',
			'JO',
			'KZ',
			'KE',
			'KI',
			'KP',
			'KR',
			'KW',
			'KG',
			'LA',
			'LV',
			'LB',
			'LS',
			'LR',
			'LY',
			'LI',
			'LT',
			'LU',
			'MO',
			'MG',
			'MW',
			'MY',
			'MV',
			'ML',
			'MT',
			'MH',
			'MQ',
			'MR',
			'MU',
			'YT',
			'MX',
			'FM',
			'MD',
			'MC',
			'MN',
			'ME',
			'MS',
			'MA',
			'MZ',
			'MM',
			'NA',
			'NR',
			'NP',
			'NL',
			'NC',
			'NZ',
			'NI',
			'NE',
			'NG',
			'NU',
			'NF',
			'MK',
			'MP',
			'NO',
			'OM',
			'PK',
			'PW',
			'PS',
			'PA',
			'PG',
			'PY',
			'PE',
			'PH',
			'PN',
			'PL',
			'PT',
			'PR',
			'QA',
			'RO',
			'RU',
			'RW',
			'RE',
			'BL',
			'SH',
			'KN',
			'LC',
			'MF',
			'PM',
			'VC',
			'WS',
			'SM',
			'ST',
			'SA',
			'SN',
			'RS',
			'SC',
			'SL',
			'SG',
			'SX',
			'SK',
			'SI',
			'SB',
			'SO',
			'ZA',
			'GS',
			'SS',
			'ES',
			'LK',
			'SD',
			'SR',
			'SJ',
			'SE',
			'CH',
			'SY',
			'TW',
			'TJ',
			'TZ',
			'TH',
			'TL',
			'TG',
			'TK',
			'TO',
			'TT',
			'TN',
			'TR',
			'TM',
			'TC',
			'TV',
			'UG',
			'UA',
			'AE',
			'GB',
			'US',
			'UM',
			'UY',
			'UZ',
			'VU',
			'VE',
			'VN',
			'VG',
			'VI',
			'WF',
			'EH',
			'YE',
			'ZM',
			'ZW',
			'AX',
		);
	}

	/**
	 * Get National countries
	 * @return string[]
	 */
	public function get_countries_national() {
		return [ 'NL' ];
	}

	/**
	 * Add Shipping fields
	 *
	 * @param $fields
	 * @param $country
	 *
	 * @return array
	 */
	public function add_checkout_shipping_fields( array $fields = [], string $country = '' ): array {
		return $this->add_checkout_fields( $fields, $country, 'shipping' );
	}

	/**
	 * Localize checkout fields live
	 *
	 * @param array $locale_fields list of fields filtered by locale
	 *
	 * @return array $locale_fields with custom fields added
	 */
	public function country_locale_field_selectors( $locale_fields ) {
		$locale_fields = array_merge( $locale_fields, [
			'street_name'         => '#billing_street_name_field, #shipping_street_name_field',
			'house_number'        => '#billing_house_number_field, #shipping_house_number_field',
			'house_number_suffix' => '#billing_house_number_suffix_field, #shipping_house_number_suffix_field',
			'address_line'        => '#billing_address_line_field, #shipping_address_line_field',
			'select_address'      => '#billing_select_address_field, #shipping_select_address_field',
		] );

		return $locale_fields;
	}

	/**
	 * Set Checkout fields defaults
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	public function set_checkout_default_fields( $fields = [] ): array {
		$custom_fields = array(
			/* hide addresses */
			'house_number'        => array(
				'hidden'   => true,
				'required' => false,
			),
			'house_number_suffix' => array(
				'hidden'   => true,
				'required' => false,
			),
			'street_name'         => array(
				'hidden'   => true,
				'required' => false,
			),
			'select_address'      => array(
				'hidden'   => true,
				'required' => false,
			),
			'address_line'        => array(
				'hidden'   => true,
				'required' => false,
			),
		);

		return array_merge( $fields, $custom_fields );
	}

	/**
	 * Load order custom data.
	 *
	 * @param array $data Default WC_Order data.
	 *
	 * @return array       Custom WC_Order data.
	 */
	public function load_order_data( $data ) {

		/* Billing */
		$data['billing_street_name']         = '';
		$data['billing_house_number']        = '';
		$data['billing_house_number_suffix'] = '';

		/* Shipping */
		$data['shipping_street_name']         = '';
		$data['shipping_house_number']        = '';
		$data['shipping_house_number_suffix'] = '';

		return $data;
	}

	/**
	 * Custom user profile edit fields.
	 */
	public function user_profile_fields( $meta_fields ) {

		$billing_fields = array(
			'billing_street_name'         => array(
				'label'       => __( 'Street name', 'postnl-wc-address-validation' ),
				'description' => ''
			),
			'billing_house_number'        => array(
				'label'       => __( 'Number', 'postnl-wc-address-validation' ),
				'description' => ''
			),
			'billing_house_number_suffix' => array(
				'label'       => __( 'Suffix', 'postnl-wc-address-validation' ),
				'description' => ''
			),
		);

		$shipping_fields = array(
			'shipping_street_name'         => array(
				'label'       => __( 'Street name', 'postnl-wc-address-validation' ),
				'description' => ''
			),
			'shipping_house_number'        => array(
				'label'       => __( 'Number', 'postnl-wc-address-validation' ),
				'description' => ''
			),
			'shipping_house_number_suffix' => array(
				'label'       => _x( 'Suffix', 'full string', 'postnl-wc-address-validation' ),
				'description' => ''
			),
		);

		/* add fields to billing section */
		$billing_fields                   = array_merge( $meta_fields['billing']['fields'], $billing_fields );
		$billing_fields                   = $this->array_move_keys( $billing_fields, array(
			'billing_street_name',
			'billing_house_number',
			'billing_house_number_suffix'
		), 'billing_address_2', 'after' );
		$meta_fields['billing']['fields'] = $billing_fields;

		/* add fields to shipping section */
		$shipping_fields                   = array_merge( $meta_fields['shipping']['fields'], $shipping_fields );
		$shipping_fields                   = $this->array_move_keys( $shipping_fields, array(
			'shipping_street_name',
			'shipping_house_number',
			'shipping_house_number_suffix'
		), 'shipping_address_2', 'after' );
		$meta_fields['shipping']['fields'] = $shipping_fields;

		return $meta_fields;
	}

	/**
	 * @param $array
	 * @param $keys
	 * @param $reference_key
	 *
	 * @return array|mixed
	 */
	public function array_move_keys( $array, $keys, $reference_key ) {
		// cast $key as array
		$keys = (array) $keys;

		if ( ! isset( $array[ $reference_key ] ) ) {
			return $array;
		}

		$move = array();
		foreach ( $keys as $key ) {
			if ( ! isset( $array[ $key ] ) ) {
				continue;
			}
			$move[ $key ] = $array[ $key ];
			unset ( $array[ $key ] );
		}

		$move_to_pos = array_search( $reference_key, array_keys( $array ) );
		$move_to_pos += 1;

		$new_array = array_slice( $array, 0, $move_to_pos, true ) + $move + array_slice( $array, $move_to_pos, null, true );

		return $new_array;
	}

	/**
	 * @param $data
	 *
	 * @return array|mixed
	 */
	public function checkout_posted_data_set_address_field_1( $data = array() ) {
		$forms = array( 'billing', 'shipping' );
		foreach ( $forms as $form ) {
			if ( ! empty( $data["{$form}_street_name"] ) ) {

				$country = $data["{$form}_country"];

				if ( 'NL' === $country ) {
					// concatenate street & house number & copy to 'address_1'
					$house_number        = $data["{$form}_house_number"] ?? '';
					$house_number_suffix = $data["{$form}_house_number_suffix"] ?? '';
					$street_name         = $data["{$form}_street_name"] ?? '';

					$data["{$form}_address_1"] = sprintf( '%1$s %2$s %3$s', $street_name, $house_number, $house_number_suffix );
				} else {
					// concatenate street & house number & copy to 'address_1'
					$address_line              = $data["{$form}_address_line"] ?? '';
					$data["{$form}_address_1"] = $address_line;
				}

			}
		}

		return $data;
	}

	/**
	 * Order Merge street name, number and suffix into address_1
	 * @return void
	 */
	public function set_checkout_field_address_1( $order_id ) {
		$order                     = wc_get_order( $order_id );
		$ship_to_different_address = isset( $_POST['ship_to_different_address'] ) ? true : false;

		// pre-clean the data we may use
		$form_data = $this->clean_form_data( $_POST );

		if ( in_array( $form_data['billing_country'], $this->get_countries_available() ) ) {
			$billing_house_number = $form_data['billing_house_number'] . ( ! empty( $form_data['billing_house_number_suffix'] ) ? ' ' . $form_data['billing_house_number_suffix'] : '' );
			$billing_address_1    = $form_data['billing_street_name'] . ' ' . $billing_house_number;
			$this->update_order_meta( $order_id, 'billing', 'address_1', $billing_address_1 );

			//set shipping if not available
			if ( ! $ship_to_different_address && $this->has_cart_required_shipping_address() ) {
				$this->update_order_meta( $order_id, 'shipping', 'address_1', $billing_address_1 );
			}
		}

		//Save in shipping address
		if ( in_array( $form_data['shipping_country'], $this->get_countries_available() ) && $ship_to_different_address ) {
			$shipping_house_number = $form_data['shipping_house_number'] . ( ! empty( $form_data['shipping_house_number_suffix'] ) ? ' ' . $form_data['shipping_house_number_suffix'] : '' );
			$shipping_address_1    = $form_data['shipping_street_name'] . ' ' . $shipping_house_number;
			$this->update_order_meta( $order_id, 'shipping', 'address_1', $shipping_address_1 );
		}
	}

	/**
	 * clean & unslash posted data
	 *
	 * @return array $posted
	 */
	public function clean_form_data( $posted ) {
		if ( ! function_exists( 'wc_clean' ) ) {
			return $posted;
		}
		foreach ( $posted as $key => $value ) {
			if ( is_string( $value ) ) {
				$posted[ $key ] = wc_clean( wp_unslash( $value ) );
			}
		}

		return $posted;
	}

	/**
	 * Update post order information
	 *
	 * @param $order_id
	 * @param $form
	 * @param $field_name
	 * @param $value
	 *
	 * @return bool|int|void
	 */
	public function update_order_meta( $order_id, $form, $field_name, $value ) {
		$meta_key = sprintf( '_{%1$s}_{%2$s}', $form, $field_name );

		return update_post_meta( $order_id, $meta_key, $value );
	}

	/**
	 * Check if cart required a shipping address
	 * @return bool
	 */
	public function has_cart_required_shipping_address(): bool {
		if ( is_object( WC()->cart ) && method_exists( WC()->cart, 'needs_shipping_address' ) && function_exists( 'wc_ship_to_billing_address_only' ) ) {
			if ( WC()->cart->needs_shipping_address() || wc_ship_to_billing_address_only() ) {
				$cart_needs_shipping_address = true;
			} else {
				$cart_needs_shipping_address = false;
			}
		} else {
			$cart_needs_shipping_address = true;
		}

		return $cart_needs_shipping_address;
	}

	/**
	 * Custom my address formatted address.
	 *
	 * @param array $address Default address.
	 * @param int $user_id Customer ID.
	 * @param string $form Field name (billing or shipping).
	 *
	 * @return array            New address format.
	 */
	public function user_address_add_data( $address, $user_id, $form ) {
		if ( ! is_array( $address ) || empty( $address ) ) {
			return $address;
		}
		$address['street_name']         = get_user_meta( $user_id, "{$form}_street_name", true );
		$address['house_number']        = get_user_meta( $user_id, "{$form}_house_number", true );
		$address['house_number_suffix'] = ( get_user_meta( $user_id, "{$form}_house_number_suffix", true ) ) ? get_user_meta( $user_id, "{$form}_house_number_suffix", true ) : '';

		return $address;
	}

	/**
	 * Get a posted address field after sanitization and validation.
	 *
	 * @param string $key
	 * @param string $type billing for shipping
	 *
	 * @return string
	 */
	public function get_posted_address_data( $key, $posted, $type = 'billing' ) {
		if ( 'billing' === $type || ( ! $posted['ship_to_different_address'] && $this->has_cart_required_shipping_address() ) ) {
			$return = isset( $posted[ 'billing_' . $key ] ) ? $posted[ 'billing_' . $key ] : '';
		} elseif ( 'shipping' === $type && ! $this->has_cart_required_shipping_address() ) {
			$return = '';
		} else {
			$return = isset( $posted[ 'shipping_' . $key ] ) ? $posted[ 'shipping_' . $key ] : '';
		}

		return $return;
	}

	/**
	 * @param $replacements
	 * @param $args
	 *
	 * @return mixed
	 */
	public function formatted_address_replacements( $replacements, $args ) {
		extract( $args );

		if ( 'NL' == $args['country'] ) {
			if ( ! empty( $args['street_name'] ?? null ) ) {
				$replacements['{address_1}'] = trim( ( $args['street_name'] ?? '' ) . ' ' . ( $args['house_number'] ?? '' ) . ' ' . $args['house_number_suffix'] ?? '' );
			}
		}

		return $replacements;
	}
}



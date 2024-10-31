<?php
/**
 * Project:     postnl-wc-address-validation
 * Author:      Lettow Studios
 * Date:        29/06/2022
 *
 * @package     postnl-wc-address-validation
 */

namespace PostNL\AddressValidation;

use PostNL\AddressValidation\API\PostNLAPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class RequestApi {

	/**
	 * Default construct
	 */
	public function __construct() {
		$key = 'postnl_api_request';
		add_action( 'wp_ajax_' . $key, array( $this, 'request_address' ) );
		add_action( 'wp_ajax_nopriv_' . $key, array( $this, 'request_address' ) );
	}

	/**
	 * Search for adress
	 * @return void
	 */
	public function request_address() {
		//Validate Nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'postnl-api-nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}
		//Check mandatory fields
		if ( ! isset( $_POST['country'] ) ) {
			wp_send_json_error( 'Missing mandatory fields' );
		}

		$country_code = sanitize_text_field( $_POST['country'] ?? '' );

		if ( 'NL' === $country_code ) {
			/* Locale search */
			$form_data = array(
				'countryIso'          => $country_code,
				'postalCode'          => sanitize_text_field( $_POST['postcode'] ?? '' ),
				'houseNumber'         => sanitize_text_field( $_POST['house_number'] ?? '' ),
				'houseNumberAddition' => sanitize_text_field( $_POST['house_number_suffix'] ?? '' ),
			);
		} else {
			//International search
			$addressLine = '';

			if ( isset( $_POST['address_line'] ) && ! empty( $_POST['address_line'] ) ) {
				$addressLine = sanitize_text_field( $_POST['address_line'] ?? '' );
			} elseif ( isset( $_POST['street_name'] ) && ! empty( $_POST['street_name'] ) ) {
				$addressLine = sprintf( '%3$s %1$s%2$s', sanitize_text_field( $_POST['house_number'] ?? '' ), sanitize_text_field( $_POST['house_number_suffix'] ?? '' ), sanitize_text_field( $_POST['street_name'] ?? '' ) );
			}

			$form_data = [
				'countryIso'  => $country_code,
				'postalCode'  => sanitize_text_field( $_POST['postcode'] ?? '' ),
				//International search
				'cityName'    => sanitize_text_field( $_POST['city'] ?? '' ),
				'addressLine' => $addressLine,
			];
		}

		$cache_key = 'postnl:' . implode( ':', $form_data );
		$data      = get_transient( $cache_key );
		if ( $data === false ) {
			$service = new PostNLAPI();
			$data    = $service->get_address( $form_data );
			set_transient( $cache_key, $data, 300 );
		}

		$response = array(
			'form'    => sanitize_text_field( $_POST['form'] ),
			'success' => true,
		);

		if ( is_wp_error( $data ) ) {
			$response['success'] = false;
			$response['error']   = $data->get_error_message();
		} else {
			$response['addresses'] = $data;
		}

		wp_send_json_success( $response );
	}
}

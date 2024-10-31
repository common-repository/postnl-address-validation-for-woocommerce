<?php
/**
 * Project:     postnl-wc-address-validation
 * Author:      Lettow Studios
 * Date:        30/06/2022
 */

namespace PostNL\AddressValidation\API;

use Exception;
use PostNL\AddressValidation\Settings;
use WP_Error;

class PostNLAPI extends AbstractAPI {

	public function is_active() {
		! is_wp_error( $this->validate_api() );
	}

	/**
	 * Validate api key
	 * @return mixed
	 */
	public function validate_api() {

		$region = Settings::get_api_region();

		$validation_address = [
			'countryIso'  => 'NL',
			'postalCode'  => '3131CC',
			'houseNumber' => '17',
		];

		if ( $region !== 'default' ) {
			$validation_address = [
				'countryIso'  => 'BE',
				'postalCode'  => '2600',
				'houseNumber' => '3',
			];
		}

		$validation_address = apply_filters( '/postnl/postcodechecker/api/validation/address', $validation_address );

		return $this->get_address( $validation_address );
	}

	/**
	 * Search for address
	 *
	 * @param $data
	 *
	 * @return mixed|WP_Error
	 */
	public function get_address( $data ) {
		$api_key = Settings::get_apikey();

		if ( empty( $api_key ) ) {
			return new WP_Error( 'postnl-response-error-apikey-not-available', 'No API key available' );
		}

		$base_url = $this->get_end_point();
		$url      = add_query_arg( $data, $base_url );

		try {
			$response = $this->get_json( $url, [
				'User-Agent' => 'WooCommerce',
				'apikey'     => $api_key
			], false );
		} catch ( Exception $e ) {
			return new WP_Error( 'postnl-response-error', $e->getMessage() );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		} elseif ( isset( $response['fault'] ) ) {
			$error_code = $response['fault']['detail']['errorcode'] ?? 'postnl-general-error';

			return new WP_Error( $error_code, $response['fault']['faultstring'] ?? '', $response['fault'] ?? '' );
		} else if ( isset( $response['errors'] ) ) {
			$error_message = $response['errors'][0]['message'] ?? '';

			return new WP_Error( 'postnl-response-error', $error_message, $response['errors'][0] ?? '' );
		}

		//Add stripped address
		$response =  $this->add_stripped_address($response);

		//Clean up
		$response = $this->cleanup_response( $response );

		return apply_filters( '/postnl/postcodechecker/api/get_address/response', $response );
	}

	/**
	 * Get Service endpoint
	 * @return string
	 */
	public function get_end_point(): string {

		$region = Settings::get_api_region();

		$api_end_point = 'https://api.postnl.nl/v4/address/netherlands';
		if ( $region !== 'default' ) {
			$api_end_point = 'https://api.postnl.nl/v4/address/international';
		}

		return $api_end_point;
	}

	/**
	 * Clean up response results
	 *
	 * @param $response
	 *
	 * @return mixed
	 */
	public function cleanup_response( $response ) {
		$allowed_keys = apply_filters( '/postnl/postcodechecker/api/get_address/response/allowed_keys', [
			'formattedAddress',
			'mailabilityScore',
			'cityName',
			'postalCode',
			'streetName',
			'stateName',
			'houseNumber',
			'houseNumberAddition',
			'countryName',
			'countryIso2',
			'countryIso3',
			//Custom fields
			'strippedAddress'
		] );

		//Filter allowed keys
		foreach ( $response as $index => $address ) {
			$response[ $index ] = array_intersect_key( $address, array_flip( $allowed_keys ) );
		}

		return $response;
	}

	/**
	 * Add Custom Stripped address
	 *
	 * @param $response
	 *
	 * @return mixed
	 */
	public function add_stripped_address( $response ) {
		foreach ( $response as $index => $address ) {
			$formatted_address = $address['formattedAddress'] ?? array();

			$city_post_code_1 = sprintf( '%1$s %2$s', $address['postalCode'], $address['cityName'] );
			$city_post_code_2 = sprintf( '%2$s %1$s', $address['postalCode'], $address['cityName'] );
			$strip_names      = array(
				$address['countryName'],
				$address['cityName'],
				$address['postalCode'],
				$address['stateName'],//Additional
				$city_post_code_1,
				$city_post_code_2,
			);
			$stripped_address = array_values( array_diff( $formatted_address, $strip_names ) );

			$response[ $index ]['strippedAddress'] = $stripped_address;
		}

		return $response;
	}

}

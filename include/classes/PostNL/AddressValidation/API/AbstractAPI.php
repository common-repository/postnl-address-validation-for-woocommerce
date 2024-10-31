<?php
/**
 * Project:     postnl-wc-address-validation
 * Author:      Lettow Studios
 * Date:        30/06/2022
 */

namespace PostNL\AddressValidation\API;

use Exception;

/**
 * Abstract API
 */
abstract class AbstractAPI {
	abstract public function get_address( $data );

	public function get_json( $url, $headers = array(), $auth = false ) {
		$args = array(
			'headers'   => $headers,
			'timeout'   => 15,
			'sslverify' => true,
		);

		do_action('postnl_wc_address_validation_api_get_json', $url, $headers, $auth);

		//Set basic auth
		if ( ! empty( $auth ) ) {
			$args['headers']['Authorization'] = 'Basic ' . base64_encode( ( $auth['user'] ?? '' ) . ':' . ( $auth['pwd'] ?? '' ) );
		}
		$response = wp_remote_request( $url, $args );

		do_action('postnl_wc_address_validation_api_response',$response, $url, $args);
		return $this->wp_remote_process_json_response( $response );
	}

	/**
	 * Parse responce
	 *
	 * @param $response
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function wp_remote_process_json_response( $response ) {
		if ( is_wp_error( $response ) ) {
			throw new Exception( "Connection error: " . $response->get_error_message() );
		} else {
			$status = wp_remote_retrieve_response_code( $response );
			$header = wp_remote_retrieve_headers( $response );
			$body   = wp_remote_retrieve_body( $response );

			if ( $status == 404 && ( empty( $body ) || ! $this->is_json( $body ) ) ) {
				throw new Exception( "404 page not found" );
			} else {
				return json_decode( $body, true );
			}
		}
	}
}

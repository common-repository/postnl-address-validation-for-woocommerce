<?php
/**
 * Project:     postnl-address-validation-for-woocommerce
 * Author:      K.Brandse
 * Date:        10/07/2023
 */

namespace PostNL\AddressValidation\Compatibility;

use PostNLWooCommerce\Main;

class CompatibilityPostNLWooCommerce {
	public function init(){

		$postnl = Main::instance();
		$single = $postnl->get_shipping_order();
		//remove housenumber from address formatting
		remove_filter('woocommerce_order_formatted_shipping_address' , array( $single, 'display_shipping_house_number' ), 10);
		remove_filter('woocommerce_order_formatted_billing_address' , array( $single, 'display_billing_house_number' ), 10);
	}
}

var postnl_address_select = {
	/**
	 * Init
	 */
	init: function () {
		/* Address results */
		jQuery(document).on('postnl.addresses_found',postnl_address_select.show_multiple_addresses);

		jQuery(document).on('postnl.address_selected',postnl_address_select.clear_adddress_line);

		jQuery(document).on('postnl.no_addresses_found',postnl_address_select.clear_fields);
		jQuery(document).on('postnl.in_valid_search',postnl_address_select.clear_fields);
		jQuery(document).on('postnl.country_changed',postnl_address_select.clear_fields);

		jQuery(document).on('postnl.validation_changed',postnl_address_select.change_state);
		jQuery(document).on('postnl.validation_changed',postnl_address_select.change_state_address_line);

		jQuery(document).on('postnl.country_changed',postnl_address_select.set_validations);
	},

	/**
	 * Toggle Active state
	 * @param event
	 * @param data
	 */
	change_state_address_line(event,data){
		let address_line = jQuery('#' + data.form + '_address_line_field');
		if(data.validate==false){
			address_line.attr('disabled', 'disabled');
			address_line.hide();
		}else{
			address_line.show();
			address_line.removeAttr('disabled');
		}
	},

	/**
	 * Toggle Active state
	 * @param event
	 * @param data
	 */
	change_state(event,data){
		select_adres = jQuery('#' + data.form + '_select_address');
		if(data.validate==false){
			select_adres.attr('disabled', 'disabled');
			//select_adres.hide();
		}else{
			//select_adres.show();
			select_adres.removeAttr('disabled');
		}
	},
	clear_fields(event,data){
		select_adres = jQuery('#' + data.form + '_select_address');
		select_adres.attr('disabled', 'disabled');
		//set no result message
		select_adres.html(`<option>${postnl_pc_setting.i18n.no_results}</option>`);
	},
	/**
	 * Show Addresses
	 * @param form
	 */
	show_multiple_addresses: function (event,data) {
		addresses = data.addresses;
		let select_adres = jQuery('#' + data.form + '_select_address');
		select_adres.html('');
		if (addresses.length > 0) {
			for (let adres in addresses) {
				item = addresses[adres];
				full_address = item.formattedAddress.join(' ');
				select_adres.append(jQuery('<option>', {
					value: adres, text: full_address
				}));

				select_adres.removeAttr('disabled');
			}
		}

		if (addresses.length == 1) {
			jQuery('#' + data.form + '_select_address').val(0).trigger('change');

		}
	},
	/**
	 * Clear address line
	 */
	clear_adddress_line(event,data){
		let address_line_input = jQuery('#' + data.form + '_address_line');
		address_line_input.val('');
	}
};

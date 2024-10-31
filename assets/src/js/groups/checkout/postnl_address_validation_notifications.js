var postnl_address_validation_notifications = {
	/**
	 * Init
	 */
	init: function () {
		this.add_containers();

		jQuery(document).on('postnl.get_address',postnl_address_validation_notifications.clear_notifications);

		jQuery(document).on('postnl.no_addresses_found',postnl_address_validation_notifications.error_notification);

		jQuery(document).on('postnl.address_selected',postnl_address_validation_notifications.show_address);

		jQuery(document).on('postnl.country_changed',postnl_address_validation_notifications.clear_notifications);
		/* Show if validation is disabled */
		jQuery(document).on('postnl.validation_changed',postnl_address_validation_notifications.validation_notifcation);
	},
	/**
	 * Add message containers
	 */
	add_containers() {
		jQuery('#billing_select_address_field').append(jQuery('<div id="billing_select_message" class="postnl_error_message" style="display:none"/>'));
		jQuery('#shipping_select_address_field').append(jQuery('<div id="shipping_select_message" class="postnl_error_message" style="display:none"/>'));
	},

	/**
	 * Set notification
	 * @param message
	 */
	set_message(message, form, type) {
		let error_container = jQuery('#' + form + '_select_message');

		/* Set message type and clear */
		type = type === undefined ? 'info' : type;
		error_container.removeClass('error success info warning').addClass(type);

		/* Set error message. */
		let error_message = message === null ? '' : message;
		error_container.html(error_message);

		/* Show or hide message */
		if (message === null) {
			message = '';
			error_container.hide();
		} else {
			error_container.show();
		}
	},
	/**
	 * Show Address
	 * @param event
	 * @param data
	 */
	show_address(event,data){
		/* Set success message */
	    let full_address = data.address.formattedAddress.join('<br/>');
		postnl_address_validation_notifications.set_message(full_address, form, 'success');
	},
	/**
	 * No address found
	 * @param event
	 * @param data
	 */
	error_notification(event,data){
		postnl_address_validation_notifications.set_message(postnl_pc_setting.i18n.no_results_long, data.form, 'error');
	},
	/**
	 * Address found clear errors
	 * @param event
	 * @param data
	 */
	clear_notifications(event,data){
		postnl_address_validation_notifications.set_message(null, data.form, 'error');
	},
	/**
	 * Validation notification
	 * @param event
	 * @param data
	 */
	validation_notifcation(event,data){
		if(data.validate===false){
			/* Set notification */
			postnl_address_validation_notifications.set_message(postnl_pc_setting.i18n.edit_address_validation, data.form, 'info');
		}else{
			/* Clear notification */
			postnl_address_validation_notifications.set_message(null, data.form, 'info');
		}
	}
};

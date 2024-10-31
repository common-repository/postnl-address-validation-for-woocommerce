/**
 * Loading indicator
 * @type {{init: postnl_loader.init, disable_loading(*, *): void, enable_loading(*, *): void, set_loading: postnl_loader.set_loading}}
 */
var postnl_loader = {
	init: function () {
		/* Disable loading */
		jQuery(document).on('postnl.no_addresses_found',postnl_loader.disable_loading);
		jQuery(document).on('postnl.addresses_found',postnl_loader.disable_loading);
		/* enable loading loading */
		jQuery(document).on('postnl.get_address',postnl_loader.enable_loading);
	},

	enable_loading(event,data){
		postnl_loader.set_loading(data.form,true);
	},
	disable_loading(event,data){
		postnl_loader.set_loading(data.form,false);
	},
	/**
	 * Set loading
	 * @param enable
	 * @param form
	 */
	set_loading: function (form,enable) {
		main_form = jQuery('#' + form + '_select_address').closest('form');
		if (enable == true) {
			main_form.addClass('loading-addresses');
		} else {
			main_form.removeClass('loading-addresses');
		}
	}
};

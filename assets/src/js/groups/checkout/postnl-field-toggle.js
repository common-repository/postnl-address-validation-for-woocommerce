/**
 * Toggle Fields
 * @type {{init: postnl_field_toggle.init, add_containers(): void}}
 */
var postnl_field_toggle = {
	show: false,
	/**
	 * Init
	 */
	init: function () {
		this.add_containers();
		jQuery('[data-address-toggle]').on('click',postnl_field_toggle.display_toggle_event);
		jQuery(document).on('postnl.validation_changed',postnl_field_toggle.form_changed);
	},
	/**
	 * Add message containers
	 */
	add_containers() {
		let element_html_billing = '<a href="#" class="postnl-address-edit" data-address-toggle="toggle" role="button" data-target-form="billing">'+postnl_pc_setting.i18n.edit_address+'</a>';
		let element_html_shipping = '<a href="#" class="postnl-address-edit" data-address-toggle="toggle" role="button" data-target-form="shipping">'+postnl_pc_setting.i18n.edit_address+'</a>';
		jQuery('#billing_select_address_field').append( jQuery(element_html_billing) );
		jQuery('#shipping_select_address_field').append( jQuery(element_html_shipping) );
	},
	/**
	 * Trigger form changed reset fields
	 * @param event
	 * @param data
	 */
	form_changed(event,data){
		postnl_field_toggle.display_toggle(data.form);
	},
	/**
	 * Toggle Fields
	 * @param event
	 */
	display_toggle_event(event){
		event.preventDefault();

		postnl_field_toggle.show = !postnl_field_toggle.show;

		form = jQuery(this).data('target-form');
		postnl_field_toggle.display_toggle(form);

		/* Disable/Enable validation for form */
		postnl_address_validation.set_validate(form,! postnl_field_toggle.show);
	},
	/**
	 * Display toggle
	 * @param event
	 */
	display_toggle(form){

		/* Get target form */
		if(undefined === form){
			return;
		}


		/* Hide or show fields */
		let toggle_elements = jQuery('[data-address-toggle-field=1]');
		if(postnl_field_toggle.show==true){
			toggle_elements.each(function(){
				jQuery(this).closest('.form-row').show();
			});
		}else{
			toggle_elements.each(function(){
				jQuery(this).closest('.form-row').hide();
			});
		}


		//country code NL
		country = jQuery('#' + form + '_country').val();


		if(country!=='NL'){
			country = jQuery('#' + form + '_city_field').show();
		}

		if(country==='NL') {
			/* Show input field required for international */
			if(true === postnl_field_toggle.show){
				jQuery('#' + form + '_city_field').show();
			}
			/* Hide address line */
			jQuery('#' + form + '_address_line_field').hide();

			/* Show required search fields */
			jQuery('#' + form + '_house_number_field').show();
			jQuery('#' + form + '_house_number_suffix_field').show();
			jQuery('#' + form + '_postcode').show();
		}
	}
};

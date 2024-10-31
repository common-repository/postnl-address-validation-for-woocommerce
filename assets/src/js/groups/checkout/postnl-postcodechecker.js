/**
 * postnl-wc-postcodechecker
 */
var postnl_address_validation = {
	//address results
	addresses: {billing: null, shipping: null},
	validate: {billing: true, shipping: true},
	timeout: {billing: null, shipping: null}, /**
	 * Initialize
	 */
	init: function () {
		main_form = jQuery('form[name="checkout"], body.woocommerce-edit-address form');

		input_fields = '#billing_postcode_field input, #shipping_postcode_field input, #billing_house_number_field input, #shipping_house_number_field input, #billing_house_number_suffix_field input, #shipping_house_number_suffix_field input, #billing_address_line_field textarea, #shipping_address_line_field textarea, #billing_city_field input, #shipping_city_field input, #billing_street_name_field input, #shipping_city_field input';
		main_form.on('change', input_fields, postnl_address_validation.trigger_get_address_form);
		main_form.on('blur', input_fields, postnl_address_validation.trigger_get_address_form);

		/* set selected address */
		main_form.on('change', '[data-select-address]', postnl_address_validation.set_selected_address);
		/* Country change */
		main_form.on('change', '#billing_country, #shipping_country', postnl_address_validation.country_changed);




		/* Initial loading */
		this.search_address('billing');
		this.search_address('shipping');
	},
	/* Toggle valdation */
	toggle_validation(event,data){
		if(data.form!==undefined){
			postnl_address_validation.set_validate(data.form,data.show);
		}
	},
	/**
	 * Get validate
	 * @param form
	 * @returns {*}
	 */
	get_validate(form){
		return postnl_address_validation.validate[form];
	},
	/**
	 * Set Form validate
	 * @param form
	 * @param value
	 */
	set_validate(form,value){
		if(undefined !== form){
			postnl_address_validation.validate[form] = value;
			jQuery(document).trigger('postnl.validation_changed',{form: form,validate:postnl_address_validation.validate[form]});
		}
	},

	/**
	 * Clear fields on country change
	 */
	country_changed: function () {
		form_data = postnl_address_validation.detect_form(jQuery(this));

		/* Trigger country change event */
		jQuery(document).trigger('postnl.country_changed',{form: form_data.form});

		/* ReEnable validate */
		postnl_address_validation.set_validate(form_data.form,true);
	},

	/**
	 * Current form type detection
	 * @param object
	 * @returns {{form: string, prefix: string}}
	 */
	detect_form: function (object) {
		let form = null;
		let form_prefix = null;
		id = object.attr('id');

		if (id.indexOf("billing") > -1) {
			form = "billing";
		} else {
			form = "shipping";
		}
		form_prefix = form + "_";

		return {form: form, prefix: form_prefix};
	},

	trigger_get_address_form: function () {
		form_data = postnl_address_validation.detect_form(jQuery(this));

		/* Set Input timeout */
		clearTimeout(postnl_address_validation.timeout[form_data.form]);
		postnl_address_validation.timeout[form_data.form] = setTimeout(function () {
			postnl_address_validation.search_address(form_data.form);
		}, 500);
	},

	/**
	 * Get address
	 * @param object
	 */
	search_address: function (form) {

		/* Check if validation is required */
		if(postnl_address_validation.get_validate(form)==false){
			return;
		}


		form_prefix = form + '_';

		//is form available
		postcode_field = jQuery('#' + form_prefix + 'country');
		if (postcode_field.length < 1) {
			return;
		}

		let form_data = {
			country: '',
			postcode: '',
			street_name: '',
			house_number: '',
			house_number_suffix: '',
			city: '',
			address_line: '',
			form: form,
		};

		//get fields
		form_data.country = jQuery('#' + form_prefix + 'country').val();
		form_data.postcode = jQuery('#' + form_prefix + 'postcode').val();
		form_data.city = jQuery('#' + form_prefix + 'city').val();
		form_data.street_name = jQuery('#' + form_prefix + 'street_name').val();
		form_data.house_number = jQuery('#' + form_prefix + 'house_number').val();
		form_data.house_number_suffix = jQuery('#' + form_prefix + 'house_number_suffix').val();
		form_data.address_line = jQuery('#' + form_prefix + 'address_line').val();


		//required request fieds
		if (form_data.country !== '' && (form_data.postcode !== '' || form_data.address_line !== '') ) {
			postnl_address_validation.get_address_response(form_data);
		} else {
			//jQuery(document).trigger('postnl.in_valid_search',{form: form_data.form});
		}
	}, /**
	 * Get form data
	 * @param formdata
	 */
	get_address_response: function (formdata) {
		//add nonce to request
		formdata.nonce = postnl_pc_setting.nonce;
		jQuery(document).trigger('postnl.get_address',{form: formdata.form,formdata: formdata});
		jQuery.ajax({
			url: postnl_pc_setting.url, data: formdata, dataType: 'json', method: 'POST'
		}).done(function (responce) {
			jQuery(document).trigger('postnl.address_responce',{data: responce});
			if (responce.data.addresses !== undefined && responce.data.addresses.length > 0) {
				postnl_address_validation.set_addresses(responce.data.addresses, responce.data.form);
				jQuery(document).trigger('postnl.addresses_found',{form: responce.data.form, addresses: responce.data.addresses});
			} else {
				postnl_address_validation.set_addresses(null, responce.data.form);
				jQuery(document).trigger('postnl.no_addresses_found',{form: responce.data.form});
			}
		});
	}, /**
	 * Set adresses
	 * @param addresses
	 * @param form
	 */
	set_addresses: function (addresses, form) {
		postnl_address_validation.addresses[form] = addresses;
	}, /**
	 * Get Addresses
	 * @param form
	 * @returns {*}
	 */
	get_addresses: function (form) {
		return postnl_address_validation.addresses[form];
	},
	/**
	 * Set form fields
	 * @param item
	 * @param form
	 */
	set_form_fields: function (item, form) {
		jQuery('#' + form + '_city').val(item.cityName);
		jQuery('#' + form + '_postcode').val(item.postalCode);
		jQuery('#' + form + '_street_name').val(item.streetName);
		jQuery('#' + form + '_house_number').val(item.houseNumber);
		jQuery('#' + form + '_house_number_suffix').val(item.houseNumberAddition);

		/* Set state */
		if(undefined !== item.stateName){
			jQuery('#' + form + '_state').val(item.stateName);
		}
	},

	set_selected_address: function () {
		form = jQuery(this).data('select-address');
		value = jQuery(this).val();
		addresses = postnl_address_validation.get_addresses(form);
		if (addresses[value] !== undefined) {
			let address = addresses[value];
			postnl_address_validation.set_form_fields(address, form);
			jQuery(document).trigger('postnl.address_selected',{form: form,address});
		}
	}
};

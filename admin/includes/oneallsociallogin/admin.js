jQuery(document).ready(function($) {

	var path = OASL_AJAX_PATH;

	/* Autodetect API Connection Handler */
	jQuery('#autodetect_button').click(function() {

		var message_string;
		var message_container;
		var is_success;

		var data = {
			'oasl_action' : 'autodetect_api_connection_handler'
		};

		message_container = jQuery('#autodetect_result');
		message_container.removeClass('oasl_success_message oasl_error_message').addClass('oasl_working_message');
		message_container.html('Contacting API - please wait ...');

		jQuery.post(path, data, function(response) {

			/* Radio Boxes */
			var radio_curl = jQuery("#api_connection_handler_curl");
			var radio_fsockopen = jQuery("#api_connection_handler_fsockopen");
			var radio_https = jQuery("#api_connection_protocol_https");
			var radio_http = jQuery("#api_connection_protocol_http");

			radio_curl.removeAttr("checked");
			radio_fsockopen.removeAttr("checked");

			radio_https.removeAttr("checked");
			radio_http.removeAttr("checked");

			/* CURL detected */
			if (response == 'success_autodetect_api_curl_https') {
				is_success = true;
				radio_curl.prop("checked", true);
				radio_https.prop("checked", true);
				message_string = 'Autodetected CURL on port 443 - do not forget to save your changes!';
			} else if (response == 'success_autodetect_api_fsockopen_https') {
				is_success = true;
				radio_fsockopen.prop("checked", true);
				radio_https.prop("checked", true);
				message_string = 'Autodetected FSOCKOPEN on port 443 - do not forget to save your changes!';
			} else if (response == 'success_autodetect_api_curl_http') {
				is_success = true;
				radio_curl.prop("checked", true);
				radio_http.prop("checked", true);
				message_string = 'Autodetected CURL on port 80 - do not forget to save your changes!';
			} else if (response == 'success_autodetect_api_fsockopen_http') {
				is_success = true;
				radio_fsockopen.prop("checked", true);
				radio_http.prop("checked", true);
				message_string = 'Autodetected FSOCKOPEN on port 80 - do not forget to save your changes!';
			}
			/* No handler detected */
			else {
				is_success = false;
				radio_curl.prop("checked", true);
				radio_https.prop("checked", true);
				message_string = 'Autodetection Error - our <a href="http://docs.oneall.com/plugins/guide/" target="_blank">documentation</a> might help you fix this issue.';
			}

			message_container.removeClass('oasl_working_message');
			message_container.html(message_string);

			if (is_success) {
				message_container.addClass('oasl_success_message');
			} else {
				message_container.addClass('oasl_error_message');
			}
		});
		return false;
	});

	/* Test API Settings */
	jQuery('#verify_button').click(function() {

		var message_string;
		var message_container;
		var is_success;

		/* Radio Boxes */
		var radio_curl = jQuery("#api_connection_handler_curl:checked").val();
		var radio_fsockopen = jQuery("#api_connection_handler_fsockopen:checked").val();
		var radio_https = jQuery("#api_connection_protocol_https:checked").val();
		var radio_http = jQuery("#api_connection_protocol_http:checked").val();

		var subdomain = jQuery('#api_subdomain').val();
		var key = jQuery('#api_key').val();
		var secret = jQuery('#api_secret').val();
		var handler = (typeof radio_fsockopen !== 'undefined' ? 'fsockopen' : 'curl');
		var protocol = (typeof radio_http !== 'undefined' ? 'http' : 'https');

		var data = {
		  'oasl_action' : 'check_api_settings',
		  'oasl_api_subdomain' : subdomain,
		  'oasl_api_key' : key,
		  'oasl_api_secret' : secret,
		  'oasl_api_connection_protocol' : protocol,
		  'oasl_api_connection_handler' : handler
		};

		message_container = jQuery('#verify_result');
		message_container.removeClass('oasl_success_message oasl_error_message').addClass('oasl_working_message');
		message_container.html('Contacting API - please wait ...');

		jQuery.post(path, data, function(response) {
			is_success = false;
			if (response == 'error_selected_handler_faulty') {
				message_string = 'The API Connection cannot be made, try using the API Connection autodetection';
			} else if (response == 'error_not_all_fields_filled_out') {
				message_string = 'Please fill out each of the fields above'
			} else if (response == 'error_subdomain_wrong') {
				message_string = 'The subdomain does not exist. Have you filled it out correctly?'
			} else if (response == 'error_subdomain_wrong_syntax') {
				message_string = 'The subdomain has a wrong syntax!'
			} else if (response == 'error_communication') {
				message_string = 'Could not contact API. Try using another connection handler'
			} else if (response == 'error_authentication_credentials_wrong') {
				message_string = 'The API credentials are wrong';
			} else if (response == 'success') {
				is_success = true;
				message_string = 'The settings are correct - do not forget to save your changes!';
			} else {
				message_string = 'An unknow error occured! The settings could not be verified.';
			}

			message_container.removeClass('oasl_working_message');
			message_container.html(message_string);

			if (is_success) {
				message_container.addClass('oasl_success_message');
			} else {
				message_container.addClass('oasl_error_message');
			}
		});
		return false;
	});
});
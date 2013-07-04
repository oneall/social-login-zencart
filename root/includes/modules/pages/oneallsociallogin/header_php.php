<?php
/**
 * @package   	OneAll Social Login
 * @copyright 	Copyright 2012 http://www.oneall.com - All rights reserved.
 * @license   	GNU/GPL 2 or later
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 */

// This should be first line of the script.
$zco_notifier->notify ('NOTIFY_HEADER_START_ONEALLSOCIALLOGIN_PAGE');

// Include the OneAll Toolbox.
require_once(dirname (__FILE__) . "/tools.php");

// Do not show the Social Login box while this page is being displayed.
define ('DISABLE_ONEALLSOCIALLOGIN', 1);

// Configuration values container.
$oasl_config = array ();

// Read configuration values.
$query = "SELECT `tag`,`data` FROM " . TABLE_ONEALLSOCIALLOGIN_CONFIG;
$rows = $db->Execute ($query);
while (!$rows->EOF)
{
	// Add value to container.
	$oasl_config [$rows->fields ['tag']] = $rows->fields ['data'];

	// Goto next row
	$rows->MoveNext ();
}

// Callback handler.
if (isset ($_POST) AND !empty ($_POST ['oa_action']) AND $_POST ['oa_action'] == 'social_login' AND !empty ($_POST ['connection_token']))
{
	// Make sur we have the required parameters.
	if (!empty ($oasl_config ['api_subdomain']) AND !empty ($oasl_config ['api_key']) AND !empty ($oasl_config ['api_secret']))
	{
		$api_subdomain = strtolower (trim ($oasl_config ['api_subdomain']));
		$api_key = trim ($oasl_config ['api_key']);
		$api_secret = trim ($oasl_config ['api_secret']);

		$api_connection_handler = ((isset ($oasl_config ['api_connection_handler']) AND $oasl_config ['api_connection_handler'] == 'fsockopen') ? 'fsockopen' : 'curl');
		$api_connection_protocol = ((isset ($oasl_config ['api_connection_protocol']) AND $oasl_config ['api_connection_protocol'] == 'http') ? 'http' : 'https');
		$enable_account_linking = ((!isset ($oasl_config ['flag_account_linking']) OR !empty ($oasl_config ['flag_account_linking'])) ? true : false);

		// API Resource-
		$api_resource_url = $api_connection_protocol . '://' . $api_subdomain . '.api.oneall.com/connections/' . $_POST ['connection_token'] . '.json';

		// Get connection details.
		$result = oneallsociallogin_tools::do_api_request ($api_connection_handler, $api_resource_url, array ('api_key' => $api_key, 'api_secret' => $api_secret));

		// Parse OneAll API result.
		if (is_object ($result) AND property_exists ($result, 'http_code') AND $result->http_code == 200)
		{
			if (($tmp = oneallsociallogin_tools::extract_social_network_profile ($result)) !== false)
			{
				// Add the origin to the session so that we can go back to it later.
				$tmp['origin'] = ( ! empty ($_GET['origin']) ? $_GET['origin'] : '');

				// Setup the Social Login session.
				$_SESSION ['oasl_user_data'] = serialize ($tmp);
			}
		}
	}
}

//Retrieve data
if (isset ($_SESSION ['oasl_user_data']))
{
	//Is this a new user registration?
	$new_registration = null;

	//Extract data
	$user_data = unserialize ($_SESSION ['oasl_user_data']);

	//Make sure it's valid
	if (is_array ($user_data) AND !empty ($user_data ['user_token']))
	{
		// Return to this page afterwards.
		$origin = ( ! empty ($user_data['origin']) ? $user_data['origin'] : '');

		// Get user by token.
		$customers_id = oneallsociallogin_tools::get_customers_id_for_user_token ($user_data ['user_token']);

		// This is a new user.
		if (!is_numeric ($customers_id))
		{
			//Linking enabled?
			if ($enable_account_linking)
			{
				//Only if email is verified
				if (!empty ($user_data ['user_email']) AND isset ($user_data ['user_email_is_verified']) AND $user_data ['user_email_is_verified'] === true)
				{
					//Read existing user
					if (($customers_id_tmp = oneallsociallogin_tools::get_customers_id_for_email_address ($user_data ['user_email'])) !== false)
					{
						if (oneallsociallogin_tools::link_tokens_to_customers_id ($customers_id_tmp, $user_data ['user_token'], $user_data ['identity_token'], $user_data ['identity_provider']) !== false)
						{
							$customers_id = $customers_id_tmp;
							$new_registration = false;
						}
					}
				}
			}
		}

		// We have not linked the social network account to an existing user.
		if (!is_numeric ($customers_id))
		{

			//Complete the user details with the form values
			if (isset ($_POST ['action']) AND ($_POST ['action'] == 'process'))
			{
				//Parse form fields.
				$user_data ['user_gender'] = (isset ($_POST ['gender']) ? zen_db_prepare_input ($_POST ['gender']) : '');
				$user_data ['user_first_name'] = (isset ($_POST ['firstname']) ? zen_db_prepare_input ($_POST ['firstname']) : '');
				$user_data ['user_last_name'] = (isset ($_POST ['lastname']) ? zen_db_prepare_input ($_POST ['lastname']) : '');
				$user_data ['user_birthdate'] = (isset ($_POST ['dob']) ? zen_db_prepare_input ($_POST ['dob']) : '');
				$user_data ['user_email'] = (isset ($_POST ['email_address']) ? zen_db_prepare_input ($_POST ['email_address']) : '');
				$user_data ['user_phone'] = (isset ($_POST ['telephone']) ? zen_db_prepare_input ($_POST ['telephone']) : '');
				$user_data ['user_country_id'] = (isset ($_POST ['country_id']) ? zen_db_prepare_input ($_POST ['country_id']) : '');

				//Set if details are missing
				$error = false;

				//Verify country
				if (! empty ($user_data ['user_country_id']) AND is_numeric ($user_data ['user_country_id']))
				{
					$query = "SELECT COUNT(*) AS total FROM " . TABLE_COUNTRIES . " WHERE countries_id = :country_id";
					$query = $db->bindVars($query, ':country_id', $user_data ['user_country_id'], 'integer');
					$result = $db->Execute($query);
					if ($result->fields['total'] <= 0)
					{
						$error = true;
						$messageStack->add ('oneallsociallogin', ENTRY_COUNTRY_ERROR);
					}
				}
				else
				{
					$error = true;
					$messageStack->add ('oneallsociallogin', ENTRY_COUNTRY_ERROR);
				}

				//Verify gender
				if (ACCOUNT_GENDER == 'true')
				{
					if (!in_array ($user_data ['user_gender'], array ('m', 'f')))
					{
						$error = true;
						$messageStack->add ('oneallsociallogin', ENTRY_GENDER_ERROR);
					}
				}

				//Verify first name
				if (strlen ($user_data ['user_first_name']) < ENTRY_FIRST_NAME_MIN_LENGTH)
				{
					$error = true;
					$messageStack->add ('oneallsociallogin', ENTRY_FIRST_NAME_ERROR);
				}

				//Verify last name
				if (strlen ($user_data ['user_last_name']) < ENTRY_LAST_NAME_MIN_LENGTH)
				{
					$error = true;
					$messageStack->add ('oneallsociallogin', ENTRY_LAST_NAME_ERROR);
				}

				//Verify date of birth
				if (ACCOUNT_DOB == 'true')
				{
					if (ENTRY_DOB_MIN_LENGTH > 0 or !empty ($_POST ['dob']))
					{
						if (substr_count ($user_data ['user_birthdate'], '/') > 2 || checkdate ((int) substr (zen_date_raw ($user_data ['user_birthdate']), 4, 2), (int) substr (zen_date_raw ($user_data ['user_birthdate']), 6, 2), (int) substr (zen_date_raw ($user_data ['user_birthdate']), 0, 4)) == false)
						{
							$error = true;
							$messageStack->add ('oneallsociallogin', ENTRY_DATE_OF_BIRTH_ERROR);
						}
					}
				}

				//Verify email address
				if (strlen ($user_data ['user_email']) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH)
				{
					$error = true;
					$messageStack->add ('oneallsociallogin', ENTRY_EMAIL_ADDRESS_ERROR);
				}
				elseif (!zen_validate_email ($user_data ['user_email']))
				{
					$error = true;
					$messageStack->add ('oneallsociallogin', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
				}
				else
				{
					$query = "SELECT count(*) AS total FROM   " . TABLE_CUSTOMERS . " WHERE  customers_email_address = :emailAddress";
					$query = $db->bindVars ($query, ':emailAddress', $user_data ['user_email'], 'string');
					$result = $db->Execute ($query);
					if ($result->fields ['total'] > 0)
					{
						$error = true;
						$messageStack->add ('oneallsociallogin', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
					}
				}

				//Verify telephone number
				if (strlen ($user_data ['user_phone']) < ENTRY_TELEPHONE_MIN_LENGTH)
				{
					$error = true;
					$messageStack->add ('oneallsociallogin', ENTRY_TELEPHONE_NUMBER_ERROR);
				}

				//No errors?
				if (!$error)
				{
					if (($customers_id_tmp = oneallsociallogin_tools::create_customer_from_data ($user_data)) !== false)
					{
						$customers_id = $customers_id_tmp;
						$new_registration = true;
					}
				}
			}
		}

		//Login the user
		if (!empty ($customers_id))
		{
			//Remove our data
			if (isset ($_SESSION ['oasl_user_data']))
			{
				unset ($_SESSION ['oasl_user_data']);
			}

			// Login this customer
			if (oneallsociallogin_tools::login_customer ($customers_id))
			{
				// Update the login counter
				oneallsociallogin_tools::update_identity_logins ($user_data ['identity_token']);

				// This is a new customer.
				if ($new_registration)
				{
					//Redirect
					zen_redirect(zen_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS));

					//Done
					$zco_notifier->notify ('NOTIFY_LOGIN_SUCCESS_VIA_CREATE_ACCOUNT');
				}
				// This is a returning customer.
				else
				{
					//Redirect to origin (do not redirect to logout page)
					if (!empty ($origin) AND strpos (strtolower ($origin), 'logoff') === false)
					{
						$return_link = $origin;
					}
					//Redirect to homepage
					else
					{
						$return_link = zen_href_link (FILENAME_DEFAULT);
					}

					//Redirect
					zen_redirect ($return_link);

					//Done
					$zco_notifier->notify ('NOTIFY_LOGIN_SUCCESS');
				}
			}
			else
			{
				//Error
				$zco_notifier->notify ('NOTIFY_LOGIN_FAILURE');
			}
		}
	}
}
else
{
	zen_redirect (zen_href_link (FILENAME_DEFAULT));
}


// This should be last line of the script:
$zco_notifier->notify ('NOTIFY_HEADER_END_ONEALLSOCIALLOGIN_PAGE');
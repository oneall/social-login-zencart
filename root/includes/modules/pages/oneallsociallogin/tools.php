<?php
/**
 * @package   	OneAll Social Login
 * @copyright 	Copyright 2013 http://www.oneall.com - All rights reserved.
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

/**
 *
 * ONEALL TOOLBOX
 *
 */

//OneAll Social Login Toolbox
if ( ! class_exists('oneallsociallogin_tools'))
{
	class oneallsociallogin_tools
	{
		/**
		* Logs a given customer in.
		*/
		public static function login_customer ($customers_id)
		{
			global $db;

			// Read customer details
			$query = "SELECT * FROM ".TABLE_CUSTOMERS." WHERE `customers_id` = :customersID";
			$query = $db->bindVars($query, ':customersID', $customers_id, 'integer');
			$customer = $db->Execute($query);

			// The customer has been found
			if (!empty ($customer->fields ['customers_id']))
			{
				//Set customer details
				$_SESSION['customer_id'] = $customer->fields['customers_id'];
				$_SESSION['customer_default_address_id'] = $customer->fields['customers_default_address_id'];
				$_SESSION['customers_authorization'] = $customer->fields['customers_authorization'];
				$_SESSION['customer_first_name'] = $customer->fields['customers_firstname'];
				$_SESSION['customer_last_name'] = $customer->fields['customers_lastname'];

				//Read country details
				$query = "SELECT * FROM " . TABLE_ADDRESS_BOOK . " WHERE customers_id = :customersID AND address_book_id = :addressBookID";
				$query = $db->bindVars($query, ':customersID', $customer->fields['customers_id'], 'integer');
				$query = $db->bindVars($query, ':addressBookID', $customer->fields['customers_default_address_id'], 'integer');
				$country = $db->Execute($query);

				// The country has been found
				if (!empty ($country->fields ['entry_country_id']))
				{
					//Set country details
					$_SESSION['customer_country_id'] = $country->fields['entry_country_id'];
					$_SESSION['customer_zone_id'] = $country->fields['entry_zone_id'];
				}

				// Update statistics
				$query = "UPDATE " . TABLE_CUSTOMERS_INFO ." SET customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 WHERE customers_info_id = :customersID";
				$query = $db->bindVars($query, ':customersID',  $_SESSION['customer_id'], 'integer');
				$db->Execute($query);

				//Restore cart contents
				$_SESSION['cart']->restore_contents();

				// Customer is now logged in.
				return true;
			}

			// Invalid customer specified.
			return false;
		}


		/**
		 * Creates a new customer based on the given data.
		 */
		public static function create_customer_from_data (Array $user_data, $send_email_to_admin = false, $send_email_to_customer = false)
		{
			global $db;

			if (is_array ($user_data) && ! empty ($user_data['user_token']) && ! empty ($user_data['identity_token']))
			{
				// We could get the email.
				if (!empty ($user_data['user_email']))
				{
					// It already exists.
					if (self::get_customers_id_for_email_address($user_data ['user_email']) !== false)
					{
						// Create a new one.
						$user_data ['user_email'] = self::generate_random_email_address();

						//We can't send an email to this address.
						$send_email_to_customer = false;
					}
				}
				// We could not get the email.
				else
				{
					// Create a new one.
					$user_data ['user_email'] = self::generate_random_email_address();

					//We can't send an email to this address.
					$send_email_to_customer = false;
				}

				//Generate a new password
				$customer_password = self::generate_password ();

				//Prepare customer data
				$customer_data = array (
						'customers_firstname' => $user_data ['user_first_name'],
						'customers_lastname' => $user_data ['user_last_name'],
						'customers_email_address' => $user_data ['user_email'],
						'customers_gender' => ( ! empty ($user_data ['user_gender']) ? $user_data ['user_gender'] : ''),
						'customers_dob' => (! empty ($user_data ['user_birthdate']) ? zen_date_raw ($user_data ['user_birthdate']) :  zen_db_prepare_input('0001-01-01 00:00:00')),
						'customers_nick' => $user_data ['user_login'],
						'customers_telephone' => $user_data ['user_phone'],
						'customers_newsletter' => '1',
						'customers_email_format' => 'TEXT',
						'customers_default_address_id' => 0,
						'customers_password' => zen_encrypt_password ($customer_password),
						'customers_authorization' => (int) CUSTOMERS_APPROVAL_AUTHORIZATION
				);

				// Add new customer.
				zen_db_perform (TABLE_CUSTOMERS, $customer_data);
				$customers_id = $db->Insert_ID ();

				// Make sure the account has been created.
				if (is_numeric ($customers_id))
				{
					$customer_data['customers_id'] = $customers_id;

					// Prepare address data.
					$address_data = array(
						'customers_id' => $customers_id,
						'entry_country_id' => (! empty ($user_data ['user_country_id']) ? $user_data ['user_country_id'] : 1),
						'entry_firstname' => $user_data ['user_first_name'],
						'entry_lastname' => $user_data ['user_last_name'],
						'entry_gender' => ( ! empty ($user_data ['user_gender']) ? $user_data ['user_gender'] : '')
					);

					// Add address.
					zen_db_perform(TABLE_ADDRESS_BOOK, $address_data);
					$address_id = $db->Insert_ID();

					// Assign as default address for customer.
					$query = "UPDATE " . TABLE_CUSTOMERS . " SET `customers_default_address_id` = :customers_default_address_id WHERE `customers_id`=:customers_id";
					$query = $db->bindVars($query, ':customers_default_address_id', $address_id, 'integer');
					$query = $db->bindVars($query, ':customers_id', $customers_id, 'integer');
					$db->Execute($query);

					// Add customer info.
					$query = "INSERT IGNORE INTO " . TABLE_CUSTOMERS_INFO ." SET `customers_info_id`=:customers_info_id, customers_info_number_of_logons=0, customers_info_date_account_created=NOW()";
					$query = $db->bindVars($query, ':customers_info_id', $customers_id, 'integer');
					$db->Execute ($query);

					// Tie the tokens to the newly created customer.
					if (self::link_tokens_to_customers_id ($customers_id, $user_data['user_token'], $user_data['identity_token'], $user_data['identity_provider']))
					{
						//Send an email to the customer
						if ($send_email_to_customer === true)
						{
							self::send_confirmation_to_customer ($customer_data, $customer_password, $user_data['identity_provider']);
						}

						//Send an email to the administratos
						if ($send_email_to_admin === true)
						{
							self::send_confirmation_to_administrators ($customer_data, $user_data['identity_provider']);
						}

						//Done
						return $customers_id;
					}
				}
			}

			//Error
			return false;
		}


		/**
		 * Generates a password
		 */
		public static function generate_password ($length = 8)
		{
			$password = '';

			for ($i = 0; $i < $length; $i++)
			{
				do
				{
					$char = chr (mt_rand (48, 122));
				}
				while (!preg_match ('/[a-zA-Z0-9]/', $char));
				$password .= $char;
			}

			return $password;
		}


		/**
		 * Generates a random email address
		 */
		public static function generate_random_email_address ()
		{
			do
			{
				$email_address = md5 (uniqid (mt_rand (10000, 99000))) . "@example.com";
			}
			while (self::get_customers_id_for_email_address ($email_address) !== false);
			return $email_address;
		}


		/**
		 * Links the user/identity tokens to a customer
		 */
		public static function link_tokens_to_customers_id ($customers_id, $user_token, $identity_token, $identity_provider)
		{
			global $db;

			// Make sure that that the customers exists.
			$query = "SELECT `customers_id` FROM ".TABLE_CUSTOMERS." WHERE `customers_id` = :customers_id";
			$query = $db->bindVars($query, ':customers_id', $customers_id, 'integer');
			$result = $db->Execute ($query);

			// The user account has been found!
			if (!empty ($result->fields ['customers_id']))
			{
				// Read the entry for the given user_token.
				$query = "SELECT `oasl_user_id`, `customers_id` FROM ".TABLE_ONEALLSOCIALLOGIN_USER." WHERE `user_token` = :user_token";
				$query = $db->bindVars($query, ':user_token', $user_token, 'string');
				$oasl_user = $db->Execute ($query);

				// The user_token exists but is linked to another user.
				if (!empty ($oasl_user->fields ['oasl_user_id']) AND $oasl_user->fields ['customers_id'] <> $customers_id)
				{
					// Delete the wrongly linked user_token.
					$query = "DELETE FROM ".TABLE_ONEALLSOCIALLOGIN_USER." WHERE `user_token` = :user_token LIMIT 1";
					$query = $db->bindVars($query, ':user_token', $user_token, 'string');
					$db->Execute ($query);

					// Delete the wrongly linked identity_token.
					$query = "DELETE FROM ".TABLE_ONEALLSOCIALLOGIN_IDENTITY." WHERE `oasl_user_id` = :oasl_user_id";
					$query = $db->bindVars($query, ':oasl_user_id', $oasl_user->fields ['oasl_user_id'], 'integer');
					$db->Execute ($query);

					// Reset the identifier to create a new one.
					$oasl_user->fields ['oasl_user_id'] = null;
				}

				// The user_token either does not exist or has been reset.
				if (empty ($oasl_user->fields ['oasl_user_id']))
				{
					// Add new link.
					$query = "INSERT INTO ".TABLE_ONEALLSOCIALLOGIN_USER." SET `customers_id` = :customers_id, `user_token` = :user_token";
					$query = $db->bindVars($query, ':customers_id', $customers_id, 'integer');
					$query = $db->bindVars($query, ':user_token', $user_token, 'string');
					$db->Execute ($query);

					// Identifier of the newly created user_token entry.
					$oasl_user->fields ['oasl_user_id'] = $db->Insert_ID ();
				}

				// Read the entry for the given identity_token.
				$query = "SELECT `oasl_identity_id`, `oasl_user_id`, `identity_token` FROM ".TABLE_ONEALLSOCIALLOGIN_IDENTITY." WHERE `identity_token` = :identity_token";
				$query = $db->bindVars($query, ':identity_token', $identity_token, 'string');
				$oasl_identity = $db->Execute ($query);

				// The identity_token exists but is linked to another user_token.
				if (!empty ($oasl_identity->fields ['oasl_identity_id']) AND $oasl_identity->fields ['oasl_user_id'] <> $oasl_user->fields ['oasl_user_id'])
				{
					// Delete the wrongly linked user_token.
					$query = "DELETE FROM ".TABLE_ONEALLSOCIALLOGIN_IDENTITY." WHERE `oasl_identity_id` = :oasl_identity_id LIMIT 1";
					$query = $db->bindVars($query, ':oasl_identity_id', $oasl_identity->fields ['oasl_identity_id'], 'integer');
					$db->Execute ($query);

					// Reset the identifier to create a new one.
					$oasl_identity->fields ['oasl_identity_id'] = null;
				}

				// The identity_token either does not exist or has been reset.
				if (empty ($oasl_identity->fields ['oasl_identity_id']))
				{
					// Add new link.
					$query = "INSERT INTO ".TABLE_ONEALLSOCIALLOGIN_IDENTITY." SET `oasl_user_id` = :oasl_user_id, `identity_token` = :identity_token, `identity_provider` = :identity_provider, `num_logins`=1";
					$query = $db->bindVars($query, ':oasl_user_id', $oasl_user->fields ['oasl_user_id'], 'integer');
					$query = $db->bindVars($query, ':identity_token', $identity_token, 'string');
					$query = $db->bindVars($query, ':identity_provider', $identity_provider, 'string');
					$insert_result = $db->Execute ($query);

					// Identifier of the newly created identity_token entry.
					$oasl_identity->fields ['oasl_identity_id'] = $db->Insert_ID ();
				}

				// Done.
				return true;
			}

			// An error occured.
			return false;
		}


		/**
		 * Updates the number of logins for an identity_token.
		 */
		public static function update_identity_logins ($identity_token)
		{
			global $db;

			// Make sure it is not empty.
			$identity_token = trim ($identity_token);
			if (strlen ($identity_token) == 0)
			{
				return false;
			}

			//Update
			$query = "UPDATE ".TABLE_ONEALLSOCIALLOGIN_IDENTITY." SET `num_logins`=`num_logins`+1 WHERE `identity_token`=:identity_token LIMIT 1";
			$query = $db->bindVars($query, ':identity_token', $identity_token, 'string');
			return $db->Execute ($query);
		}


		/**
		 * Sends a confirmation to the administrators.
		 */
		public static function send_confirmation_to_administrators ($customer_data, $identity_provider)
		{
			// Setup the mail title.
			$mail_title = "A new customer has registered with Social Login";

			// Setup the mail body.
			$mail_body = array();
			$mail_body[] = "Customer Details:";
			$mail_body[] = " Identifier: ".	 $customer_data['customers_id'];
			$mail_body[] = " First name: ".	  $customer_data['customers_firstname'];
			$mail_body[] = " Last name: ".	  $customer_data['customers_lastname'];
			$mail_body[] = " Email address: ".	  $customer_data['customers_email_address'];
			$mail_body[] = " Signed up with: ".	 $identity_provider."<br />";


			//Send email
			zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, $mail_title, implode ("\n", $mail_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=> implode ("<br />", $mail_body)), 'oneallsociallogin');

			//Done
			return true;
		}


		/**
		 * Sends a confirmation to the given customer.
		 */
		public static function send_confirmation_to_customer ($customer_data, $password, $identity_provider)
		{
			//Try to use existing
			$email_greet_mr = (defined ('EMAIL_GREET_MR') ? EMAIL_GREET_MR : OASL_EMAIL_GREET_MR);
			$email_greet_ms = (defined ('EMAIL_GREET_MS') ? EMAIL_GREET_MS : OASL_EMAIL_GREET_MS);
			$email_greet_none = (defined ('EMAIL_GREET_NONE') ? EMAIL_GREET_NONE : OASL_EMAIL_GREET_NONE);
			$email_subject = (defined ('EMAIL_SUBJECT') ? EMAIL_SUBJECT : OASL_EMAIL_SUBJECT);
			$email_text = (defined ('EMAIL_TEXT') ? EMAIL_TEXT : OASL_EMAIL_TEXT);
			$email_contact = (defined ('EMAIL_CONTACT') ? EMAIL_CONTACT : OASL_EMAIL_CONTACT);
			$email_welcome = (defined ('EMAIL_WELCOME') ? EMAIL_WELCOME : OASL_EMAIL_WELCOME);
			$email_closure = (defined ('EMAIL_GV_CLOSURE') ? EMAIL_GV_CLOSURE : OASL_EMAIL_GV_CLOSURE);

			//Notify customer
			$email_name = $customer_data ['customers_firstname'] . ' ' . $customer_data ['customers_lastname'];
			$email_address = $customer_data ['customers_email_address'];

			if (!empty ($customer_data ['customers_gender']))
			{
				if ($customer_data ['customers_gender'] == 'm')
				{
					$email_body = sprintf ($email_greet_mr, $customer_data ['customers_lastname']);
				}
				else
				{
					$email_body = sprintf ($email_greet_ms, $customer_data ['customers_lastname']);
				}
			}
			else
			{
				$email_body = sprintf ($email_greet_none, $customer_data ['customers_firstname']);
			}
			$email_body .= "\n" . $email_welcome . "n" . $email_text .= "\n\n" . $email_contact . "\n" . $email_closure;
			$email_body_html = $email_body;


			zen_mail ($email_name, $email_address, $email_subject, $email_body, STORE_NAME, EMAIL_FROM, $email_body_html, 'welcome');
		}


		/**
		 * Returns the customer identifier for a given email address.
		 */
		public static function get_customers_id_for_email_address ($email_address)
		{
			global $db;

			// Make sure it is not empty.
			$email_address = trim ($email_address);
			if (strlen ($email_address) == 0)
			{
				return false;
			}

			// Check if the user account exists.
			$query = "SELECT `customers_id` FROM ".TABLE_CUSTOMERS." WHERE `customers_email_address` = :email_address";
			$query = $db->bindVars($query, ':email_address', $email_address, 'string');
			$result = $db->Execute ($query);

			// Either return the id_customer or false if none has been found.
			return (!empty ($result->fields ['customers_id']) ? $result->fields ['customers_id'] : false);
		}


		/**
		 * Returns the customer identifier for a given token.
		 */
		public static function get_customers_id_for_user_token ($user_token)
		{
			global $db;

			// Make sure it is not empty.
			$user_token = trim ($user_token);
			if (strlen ($user_token) == 0)
			{
				return false;
			}

			// Read the id_customer for this user_token.
			$query = "SELECT `oasl_user_id`, `customers_id` FROM ".TABLE_ONEALLSOCIALLOGIN_USER." WHERE `user_token` = :user_token";
			$query = $db->bindVars($query, ':user_token', $user_token, 'string');
			$result = $db->Execute ($query);

			// We have found an entry for this customer.
			if (!empty ($result->fields ['customers_id']))
			{
				$customers_id = intval ($result->fields ['customers_id']);
				$oasl_user_id = intval ($result->fields ['oasl_user_id']);

				// Check if the user account exists.
				$query = "SELECT `customers_id` FROM ".TABLE_CUSTOMERS." WHERE `customers_id` = :customers_id";
				$query = $db->bindVars($query, ':customers_id', $customers_id, 'integer');
				$result = $db->Execute ($query);

				// The user account exists, return it's identifier.
				if (!empty ($result->fields ['customers_id']))
				{
					return $result->fields ['customers_id'];
				}

				// Delete the wrongly linked user_token.
				$query = "DELETE FROM ".TABLE_ONEALLSOCIALLOGIN_USER." WHERE `user_token` = :user_token LIMIT 1";
				$query = $db->bindVars($query, ':user_token', $user_token, 'string');
				$result = $db->Execute ($query);

				// Delete the wrongly linked identity_token.
				$query = "DELETE FROM ".TABLE_ONEALLSOCIALLOGIN_IDENTITY." WHERE `oasl_user_id` = :oasl_user_id";
				$query = $db->bindVars($query, ':oasl_user_id', $oasl_user_id, 'integer');
				$result = $db->Execute ($query);
			}

			// No entry found.
			return false;
		}



		/**
		 * Extracts the social network data from a result-set returned by the OneAll API.
		 */
		public static function extract_social_network_profile ($social_data)
		{
			// Check API result.
			if (is_object ($social_data) && property_exists ($social_data, 'http_code') && $social_data->http_code == 200 && property_exists ($social_data, 'http_data'))
			{
				// Decode the social network profile Data.
				$social_data = json_decode ($social_data->http_data);

				// Make sur that the data has beeen decoded properly
				if (is_object ($social_data))
				{
					// Container for user data
					$data = array ();

					// Parse Social Profile Data.
					$identity = $social_data->response->result->data->user->identity;

					$data ['identity_token'] = $identity->identity_token;
					$data ['identity_provider'] = $identity->source->name;

					$data ['user_token'] = $social_data->response->result->data->user->user_token;
					$data ['user_first_name'] = !empty ($identity->name->givenName) ? $identity->name->givenName : '';
					$data ['user_last_name'] = !empty ($identity->name->familyName) ? $identity->name->familyName : '';
					$data ['user_location'] = !empty ($identity->currentLocation) ? $identity->currentLocation : '';
					$data ['user_constructed_name'] = trim ($data ['user_first_name'] . ' ' . $data ['user_last_name']);
					$data ['user_picture'] = !empty ($identity->pictureUrl) ? $identity->pictureUrl : '';
					$data ['user_thumbnail'] = !empty ($identity->thumbnailUrl) ? $identity->thumbnailUrl : '';
					$data ['user_about_me'] = !empty ($identity->aboutMe) ? $identity->aboutMe : '';

					// Birthdate - ZenCart expects MM/DD/YYYY
					if (!empty ($identity->birthday) && preg_match ('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', $identity->birthday, $matches))
					{
						$data ['user_birthdate'] = str_pad ($matches [2], 2, '0', STR_PAD_LEFT);
						$data ['user_birthdate'] .= '/'. str_pad ($matches [1], 2, '0', STR_PAD_LEFT);
						$data ['user_birthdate'] .= '/' . str_pad ($matches [3], 4, '0', STR_PAD_LEFT);
					}
					else
					{
						$data ['user_birthdate'] = '';
					}

					// Fullname.
					if (!empty ($identity->name->formatted))
					{
						$data ['user_full_name'] = $identity->name->formatted;
					}
					elseif (!empty ($identity->name->displayName))
					{
						$data ['user_full_name'] = $identity->name->displayName;
					}
					else
					{
						$data ['user_full_name'] = $data ['user_constructed_name'];
					}

					// Preferred Username.
					if (!empty ($identity->preferredUsername))
					{
						$data ['user_login'] = $identity->preferredUsername;
					}
					elseif (!empty ($identity->displayName))
					{
						$data ['user_login'] = $identity->displayName;
					}
					else
					{
						$data ['user_login'] = $data ['user_full_name'];
					}

					// Email Address.
					$data ['user_email'] = '';
					if (property_exists ($identity, 'emails') && is_array ($identity->emails))
					{
						$data ['user_email_is_verified'] = false;
						while ($data ['user_email_is_verified'] !== true && (list(, $obj) = each ($identity->emails)))
						{
							$data ['user_email'] = $obj->value;
							$data ['user_email_is_verified'] = !empty ($obj->is_verified);
						}
					}

					// Website/Homepage.
					$data ['user_website'] = '';
					if (!empty ($identity->profileUrl))
					{
						$data ['user_website'] = $identity->profileUrl;
					}
					elseif (!empty ($identity->urls [0]->value))
					{
						$data ['user_website'] = $identity->urls [0]->value;
					}

					// Gender
					$data ['user_gender'] = '';
					if (!empty ($identity->gender))
					{
						switch ($identity->gender)
						{
							case 'male':
								$data ['user_gender'] = 'm';
								break;

							case 'female':
								$data ['user_gender'] = 'f';
								break;
						}
					}

					return $data;
				}
			}
			return false;
		}


		/**
		 * Send an API request by using the given handler
		 */
		public static function do_api_request ($handler, $url, $options = array (), $timeout = 30)
		{
			//FSOCKOPEN
			if ($handler == 'fsockopen')
			{
				return self::do_fsockopen_request ($url, $options, $timeout);
			}
			//CURL
			else
			{
				return self::do_curl_request ($url, $options, $timeout);
			}
		}


		/**
		 * Check if fsockopen can be used
		 */
		public static function check_fsockopen ($secure = true)
		{
			$result = self::do_fsockopen_request (($secure ? 'https' : 'http') . '://www.oneall.com/ping.html');
			if (is_object ($result) AND property_exists ($result, 'http_code') AND $result->http_code == 200)
			{
				if (property_exists ($result, 'http_data'))
				{
					if (strtolower ($result->http_data) == 'ok')
					{
						return true;
					}
				}
			}
			return false;
		}


		/**
		 * Check if CURL can be used
		 */
		public static function check_curl ($secure = true)
		{
			if (in_array ('curl', get_loaded_extensions ()) AND function_exists ('curl_exec'))
			{
				$result = self::do_curl_request (($secure ? 'https' : 'http') . '://www.oneall.com/ping.html');
				if (is_object ($result) AND property_exists ($result, 'http_code') AND $result->http_code == 200)
				{
					if (property_exists ($result, 'http_data'))
					{
						if (strtolower ($result->http_data) == 'ok')
						{
							return true;
						}
					}
				}
			}
			return false;
		}


		/**
		 * Sends a CURL request
		 */
		public static function do_curl_request ($url, $options = array (), $timeout = 15)
		{
			//Store the result
			$result = new stdClass ();

			//Send request
			$curl = curl_init ();
			curl_setopt ($curl, CURLOPT_URL, $url);
			curl_setopt ($curl, CURLOPT_HEADER, 0);
			curl_setopt ($curl, CURLOPT_TIMEOUT, $timeout);
			curl_setopt ($curl, CURLOPT_VERBOSE, 0);
			curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($curl, CURLOPT_USERAGENT, 'SocialLogin PrestaShop (+http://www.oneall.com/)');

			// BASIC AUTH?
			if (isset ($options ['api_key']) AND isset ($options ['api_secret']))
			{
				curl_setopt ($curl, CURLOPT_USERPWD, $options ['api_key'] . ":" . $options ['api_secret']);
			}

			//Make request
			if (($http_data = curl_exec ($curl)) !== false)
			{
				$result->http_code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);
				$result->http_data = $http_data;
				$result->http_error = null;
			}
			else
			{
				$result->http_code = -1;
				$result->http_data = null;
				$result->http_error = curl_error ($curl);
			}

			//Done
			return $result;
		}


		/**
		 * Sends an fsockopen request
		 */
		public static function do_fsockopen_request ($url, $options = array (), $timeout = 15)
		{
			//Store the result
			$result = new stdClass ();

			//Make that this is a valid URL
			if (($uri = parse_url ($url)) == false)
			{
				$result->http_code = -1;
				$result->http_data = null;
				$result->http_error = 'invalid_uri';
				return $result;
			}

			//Make sure we can handle the schema
			switch ($uri ['scheme'])
			{
				case 'http':
					$port = (isset ($uri ['port']) ? $uri ['port'] : 80);
					$host = ($uri ['host'] . ($port != 80 ? ':' . $port : ''));
					$fp = @fsockopen ($uri ['host'], $port, $errno, $errstr, $timeout);
					break;

				case 'https':
					$port = (isset ($uri ['port']) ? $uri ['port'] : 443);
					$host = ($uri ['host'] . ($port != 443 ? ':' . $port : ''));
					$fp = @fsockopen ('ssl://' . $uri ['host'], $port, $errno, $errstr, $timeout);
					break;

				default:
					$result->http_code = -1;
					$result->http_data = null;
					$result->http_error = 'invalid_schema';
					return $result;
					break;
			}

			//Make sure the socket opened properly
			if (!$fp)
			{
				$result->http_code = -$errno;
				$result->http_data = null;
				$result->http_error = trim ($errstr);
				return $result;
			}

			//Construct the path to act on
			$path = (isset ($uri ['path']) ? $uri ['path'] : '/');
			if (isset ($uri ['query']))
			{
				$path .= '?' . $uri ['query'];
			}

			//Create HTTP request
			$defaults = array (
				'Host' => "Host: $host",
				'User-Agent' => 'User-Agent: SocialLogin PrestaShop (+http://www.oneall.com/)'
			);

			// BASIC AUTH?
			if (isset ($options ['api_key']) AND isset ($options ['api_secret']))
			{
				$defaults ['Authorization'] = 'Authorization: Basic ' . base64_encode ($options ['api_key'] . ":" . $options ['api_secret']);
			}

			//Build and send request
			$request = 'GET ' . $path . " HTTP/1.0\r\n";
			$request .= implode ("\r\n", $defaults);
			$request .= "\r\n\r\n";
			fwrite ($fp, $request);

			//Fetch response
			$response = '';
			while (!feof ($fp))
			{
				$response .= fread ($fp, 1024);
			}

			//Close connection
			fclose ($fp);

			//Parse response
			list($response_header, $response_body) = explode ("\r\n\r\n", $response, 2);

			//Parse header
			$response_header = preg_split ("/\r\n|\n|\r/", $response_header);
			list($header_protocol, $header_code, $header_status_message) = explode (' ', trim (array_shift ($response_header)), 3);

			//Build result
			$result->http_code = $header_code;
			$result->http_data = $response_body;

			//Done
			return $result;
		}


		/**
		 * Returns the current url
		 */
		public static function get_current_url ()
		{
			//Get request URI - Should work on Apache + IIS
			$request_uri = ((!isset ($_SERVER ['REQUEST_URI'])) ? $_SERVER ['PHP_SELF'] : $_SERVER ['REQUEST_URI']);
			$request_port = ((!empty ($_SERVER ['SERVER_PORT']) AND $_SERVER ['SERVER_PORT'] <> '80') ? (":" . $_SERVER ['SERVER_PORT']) : '');
			$request_protocol = (self::is_https_on () ? 'https' : 'http') . "://";
			$redirect_to = $request_protocol . $_SERVER ['HTTP_HOST'] . $request_port . $request_uri;
			return $redirect_to;
		}


		/**
		 * Check if the current connection is being made over https
		 */
		public static function is_https_on ()
		{
			if (!empty ($_SERVER ['SERVER_PORT']))
			{
				if (trim ($_SERVER ['SERVER_PORT']) == '443')
				{
					return true;
				}
			}

			if (!empty ($_SERVER ['HTTP_X_FORWARDED_PROTO']))
			{
				if (strtolower (trim ($_SERVER ['HTTP_X_FORWARDED_PROTO'])) == 'https')
				{
					return true;
				}
			}

			if (!empty ($_SERVER ['HTTPS']))
			{
				if (strtolower (trim ($_SERVER ['HTTPS'])) == 'on' OR trim ($_SERVER ['HTTPS']) == '1')
				{
					return true;
				}
			}

			// HTTPS is off.
			return false;
		}
	}
}
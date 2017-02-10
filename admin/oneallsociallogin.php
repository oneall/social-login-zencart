<?php
/**
 * @package   	OneAll Social Login
 * @copyright 	Copyright 2011-2017 http://www.oneall.com
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
require('includes/application_top.php');
require_once(DIR_FS_CATALOG_MODULES . 'pages/oneallsociallogin/tools.php');

/**
 * Verify API Settings
 */
if (!empty ($_POST ['oasl_action']) AND $_POST ['oasl_action'] == 'check_api_settings')
{
	//Check if all fields have been filled out
	if (empty ($_POST ['oasl_api_subdomain']) OR empty ($_POST ['oasl_api_key']) OR empty ($_POST ['oasl_api_secret']))
	{
		die ('error_not_all_fields_filled_out');
	}

	//Read settings
	$api_connection_handler = ((!empty ($_POST ['oasl_api_connection_handler']) AND $_POST ['oasl_api_connection_handler'] == 'fsockopen') ? 'fsockopen' : 'curl');
	$api_connection_protocol = ((!empty ($_POST ['oasl_api_connection_protocol']) AND $_POST ['oasl_api_connection_protocol'] == 'http') ? 'http' : 'https');
	$api_connection_use_https = ($api_connection_protocol == 'https');

	$api_subdomain = trim (strtolower ($_POST ['oasl_api_subdomain']));
	$api_key = trim ($_POST ['oasl_api_key']);
	$api_secret = trim ($_POST ['oasl_api_secret']);

	//FSOCKOPEN
	if ($api_connection_handler == 'fsockopen')
	{
		if (!oneallsociallogin_tools::check_fsockopen ($api_connection_use_https))
		{
			die ('error_selected_handler_faulty');
		}
	}
	//CURL
	else
	{
		if (!oneallsociallogin_tools::check_curl ($api_connection_use_https))
		{
			die ('error_selected_handler_faulty');
		}
	}

	//Full domain entered
	if (preg_match ("/([a-z0-9\-]+)\.api\.oneall\.com/i", $api_subdomain, $matches))
	{
		$api_subdomain = $matches [1];
	}

	//Check subdomain format
	if (!preg_match ("/^[a-z0-9\-]+$/i", $api_subdomain))
	{
		die ('error_subdomain_wrong_syntax');
	}

	//Domain
	$api_domain = $api_subdomain . '.api.oneall.com';

	//Connection to
	$api_resource_url = $api_connection_protocol . '://' . $api_domain . '/tools/ping.json';

	//Get connection details
	$result = oneallsociallogin_tools::do_api_request ($api_connection_handler, $api_resource_url, array ('api_key' => $api_key, 'api_secret' => $api_secret));

	//Parse result
	if (is_object ($result) AND property_exists ($result, 'http_code') AND property_exists ($result, 'http_data'))
	{
		switch ($result->http_code)
		{
			//Success
			case 200:
				die ('success');

			//Authentication Error
			case 401:
				die ('error_authentication_credentials_wrong');

			//Wrong Subdomain
			case 404:
				die ('error_subdomain_wrong');

			//Other error
			default:
				die ('error_communication');
		}
	}
	die ('error_communication');
}


/**
 * Autodetect API Connection Handler
 */
if (!empty ($_POST ['oasl_action']) AND $_POST ['oasl_action'] == 'autodetect_api_connection_handler')
{
	//Check CURL HTTPS - Port 443
	if (oneallsociallogin_tools::check_curl (true) === true)
	{
		die ('success_autodetect_api_curl_https');
	}

	//Check CURL HTTP - Port 80
	if (oneallsociallogin_tools::check_curl (false) === true)
	{
		die ('success_autodetect_api_curl_http');
	}

	//Check FSOCKOPEN HTTPS - Port 443
	if (oneallsociallogin_tools::check_fsockopen (true) == true)
	{
		die ('success_autodetect_api_fsockopen_https');
	}

	//Check FSOCKOPEN HTTP - Port 80
	if (oneallsociallogin_tools::check_fsockopen (false) == true)
	{
		die ('success_autodetect_api_fsockopen_http');
	}

	//No working handler found
	die ('error_autodetect_api_no_handler');
}

/**
 * Make sure the installer has been ran
 */
$query = "SHOW TABLES LIKE '".TABLE_ONEALLSOCIALLOGIN_CONFIG."'";
$rows = $db->Execute ($query);
if ($rows->recordCount() == 0)
{
	?>
		<h2>Error</h2>
		<p>Social Login has not been installed.<br />Please run the <a href="../oneallsociallogin_install.php">installation script</a> first.</p>
	<?php
	
	exit();
}



/**
 * Show Admin Area
 */

// Configuration values
$oasl_config = array ();

// Read config
$query = "SELECT `tag`,`data` FROM " . TABLE_ONEALLSOCIALLOGIN_CONFIG;
$rows = $db->Execute ($query);
while (!$rows->EOF)
{
	// Add value
	$oasl_config [$rows->fields ['tag']] = $rows->fields ['data'];

	// Goto next row
	$rows->MoveNext ();
}

//Restore values
$api_subdomain = (isset ($oasl_config ['api_subdomain']) ? $oasl_config ['api_subdomain'] : '');
$api_key = (isset ($oasl_config ['api_key']) ? $oasl_config ['api_key'] : '');
$api_secret = (isset ($oasl_config ['api_secret']) ? $oasl_config ['api_secret'] : '');
$api_connection_handler = ((isset ($oasl_config ['api_connection_handler']) AND $oasl_config ['api_connection_handler'] == 'fsockopen') ? 'fsockopen' : 'curl');
$api_connection_protocol = ((isset ($oasl_config ['api_connection_protocol']) AND $oasl_config ['api_connection_protocol'] == 'http') ? 'http' : 'https');
$sidebox_title = (isset ($oasl_config ['sidebox_title']) ? $oasl_config ['sidebox_title'] : '');
$send_mail_customers = (isset ($oasl_config ['send_mail_customers']) ? $oasl_config ['send_mail_customers'] : 1);
$send_mail_admin = (isset ($oasl_config ['send_mail_admin']) ? $oasl_config ['send_mail_admin'] : 1);

//Compute enabled providers
$enabled_providers = array ();
if (!empty ($oasl_config ['enabled_providers']))
{
	$enabled_providers = explode (',', $oasl_config ['enabled_providers']);
	$enabled_providers = array_map ("strtolower", $enabled_providers);
	$enabled_providers = array_map ("trim", $enabled_providers);
}

//Compute available providers
$available_providers = array ();
if (!empty ($oasl_config ['available_providers']))
{
	$available_providers = unserialize ($oasl_config ['available_providers']);
}

/**
 *
 * SAVE SETTINGS
 *
 */
if (!empty ($_POST ['oasl_action']) AND $_POST ['oasl_action'] == 'save_settings')
{
	// API Subdomain
	$api_subdomain = (isset ($_POST ['api_subdomain']) ? trim ($_POST ['api_subdomain']) : '');

	//Full domain entered
	if (preg_match ("/([a-z0-9\-]+)\.api\.oneall\.com/i", $api_subdomain, $matches))
	{
		$api_subdomain = $matches [1];
	}

	$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_subdomain', `data`='" . zen_db_input ($api_subdomain) . "' ON DUPLICATE KEY UPDATE `data`='" .  zen_db_input ($api_subdomain) . "'";
	$result = $db->Execute ($sql);

	// API Secret
	$api_secret = (isset ($_POST ['api_secret']) ? trim ($_POST ['api_secret']) : '');
	$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_secret', `data`='" . zen_db_input ($api_secret) . "' ON DUPLICATE KEY UPDATE `data`='" .  zen_db_input ($api_secret) . "'";
	$result = $db->Execute ($sql);

	// API Key
	$api_key = (isset ($_POST ['api_key']) ? trim ($_POST ['api_key']) : '');
	$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_key', `data`='" . zen_db_input ($api_key) . "' ON DUPLICATE KEY UPDATE `data`='" .  zen_db_input ($api_key) . "'";
	$result = $db->Execute ($sql);

	// API Connection Handler
	$api_connection_handler = ((isset ($_POST ['api_connection_handler']) AND $_POST ['api_connection_handler'] == 'fsockopen') ? 'fsockopen' : 'curl');
	$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_connection_handler', `data`='" . zen_db_input ($api_connection_handler) . "' ON DUPLICATE KEY UPDATE `data`='" .  zen_db_input ($api_connection_handler) . "'";
	$result = $db->Execute ($sql);

	// API Connection Protocol
	$api_connection_protocol = ((isset ($_POST ['api_connection_protocol']) AND $_POST ['api_connection_protocol'] == 'http') ? 'http' : 'https');
	$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_connection_protocol', `data`='" . zen_db_input ($api_connection_protocol) . "' ON DUPLICATE KEY UPDATE `data`='" .  zen_db_input ($api_connection_protocol) . "'";
	$result = $db->Execute ($sql);

	// Title
	$sidebox_title = (isset ($_POST ['sidebox_title']) ? trim ($_POST ['sidebox_title']) : '');
	$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='sidebox_title', `data`='" . zen_db_input ($sidebox_title) . "' ON DUPLICATE KEY UPDATE `data`='" .  zen_db_input ($sidebox_title) . "'";
	$result = $db->Execute ($sql);
	
	// Send email to customers
	$send_mail_customers = (empty ($_POST ['send_mail_customers']) ? 0 : 1);
	$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='send_mail_customers', `data`='" . zen_db_input ($send_mail_customers) . "' ON DUPLICATE KEY UPDATE `data`='" .  zen_db_input ($send_mail_customers) . "'";
	$result = $db->Execute ($sql);
	
	// Send email to admin
	$send_mail_admin = (empty ($_POST ['send_mail_admin']) ? 0 : 1);
	$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='send_mail_admin', `data`='" . zen_db_input ($send_mail_admin) . "' ON DUPLICATE KEY UPDATE `data`='" .  zen_db_input ($send_mail_admin) . "'";
	$result = $db->Execute ($sql);
	
	//Providers
	if (isset ($_POST['providers']) AND is_array ($_POST['providers']))
	{
		$enabled_providers = array();
		foreach ($_POST['providers'] AS $provider_key => $is_enabled)
		{
			if ( ! empty ($is_enabled))
			{
				$enabled_providers[] = strtolower(trim($provider_key));
			}
		}

		$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='enabled_providers', `data`='" . zen_db_input (implode(",", $enabled_providers)) . "' ON DUPLICATE KEY UPDATE `data`='" . zen_db_input (implode(",", $enabled_providers)) . "'";
		$result = $db->Execute ($sql);
	}
}

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
		<title>OneAll Social Login Config</title>
			<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
			<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
			<script language="javascript" src="includes/menu.js"></script>
			<script language="javascript" src="includes/general.js"></script>
			<script language="javascript" src="includes/oneallsociallogin/jquery-1.10.0.min.js"></script>
			<script language="javascript" src="includes/oneallsociallogin/admin.js"></script>
			<script type="text/javascript">
				<!--
					var OASL_AJAX_PATH  = '<?php echo zen_href_link ('oneallsociallogin'); ?>';
				//-->
			</script>
	</head>
	<body>
		<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
		<table border="0" width="100%" cellspacing="2" cellpadding="2">
  			<tr>
				<td width="100%" valign="top">
					<?php
						echo zen_draw_form ('oneallsociallogin', 'oneallsociallogin', '', 'post');
						echo zen_draw_hidden_field ('oasl_action', 'save_settings');
					?>
					<table border="0" width="100%" cellspacing="0" cellpadding="2">
						<tr>
							<td>
								<table border="0" width="100%" cellspacing="0" cellpadding="0">
          							<tr>
            							<td class="pageHeading">
            								OneAll Social Login Config - 
            									<span style="font-size:12px">
            										<a href="http://support.oneall.com" target="_blank">OneAll Support Forum</a> |
            										<a href="http://docs.oneall.com/plugins/guide/social-login-zen-cart/#3b" target="_blank">Plugin Documentation</a>
            									</span>
            							</td>
            							<td class="pageHeading" align="right">
            								<?php echo zen_draw_separator ('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?>
            							</td>
									</tr>
								</table>
							</td>
      					</tr>
      					<tr>
      						<td class="formAreaTitle">
      							Notice
      						</td>
      					</tr>
      					<tr>
      				<td class="formArea">
      					<table border="0" cellspacing="2" cellpadding="2">
      						<tr>
      						 	<td class="main">
     						 		Per default Social Login will be added to the sidebar of your shop. If your shop does not have a sidebar, then our <a href="http://docs.oneall.com/plugins/guide/social-login-zen-cart/#3b" target="_blank">documentation</a> will help you manually add the social login icons to any other location.
      						 	</td>
      						</tr>
      					</table>
      				</td>
      			</tr>
      			<tr>
      				<td><br /><br /></td>
      			</tr> 	
      						 	
      			<tr>
      				<td class="formAreaTitle">
      					API Connection
      				</td>
      			</tr>
      			<tr>
      				<td class="formArea">
      					<table border="0" cellspacing="2" cellpadding="2">
      						<tr>
      						 	<td class="main" style="width:200px">API Connection Handler:</td>
      						 	<td class="main">
	      						 	<?php
									  		 echo zen_draw_radio_field ('api_connection_handler', 'curl', ($api_connection_handler <> 'fsockopen'), '', 'id="api_connection_handler_curl"');
									  		 echo '<label class="radioButtonLabel" for="api_connection_handler_curl">Use PHP CURL to communicate with the API <strong>(Default)</strong></label>';
									   ?>
											<br />
	      						 	<?php
									  		 echo zen_draw_radio_field ('api_connection_handler', 'fsockopen', ($api_connection_handler == 'fsockopen'), '', 'id="api_connection_handler_fsockopen"');
									  		 echo '<label class="radioButtonLabel" for="api_connection_handler_fsockopen">Use PHP FSOCKOPEN to communicate with the API </label>';
									   ?>
										</td>
      						 </tr>
      						 <tr>
      							<td class="main" style="width:200px">
      								API Connection Protocol:
      							</td>
      						 	<td class="main">
	      						 	<?php
									  	 echo zen_draw_radio_field ('api_connection_protocol', 'https', ($api_connection_protocol <> 'http'), '', 'id="api_connection_protocol_https"');
									  	 echo '<label class="radioButtonLabel" for="api_connection_protocol_https">Communication via HTTPS on port 443 <strong>(Default)</strong></label>';
									   ?>
										<br />
											<?php
									  	 echo zen_draw_radio_field ('api_connection_protocol', 'http', ($api_connection_protocol == 'http'), '', 'id="api_connection_protocol_http"');
									  	 echo '<label class="radioButtonLabel" for="api_connection_protocol_http">Communication via HTTP on port 80</label>';
									   ?>
								</td>
      						 </tr>
      					</table>
      				</td>
      			</tr>
      			<tr>
      				<td>
      					<table border="0" cellspacing="2" cellpadding="2">
      						<tr>
      							<td>
      								<input type="button" id="autodetect_button" value="Click here to autodetect the best connection settings" />
      							</td>
      							<td>
      								<strong id="autodetect_result"></strong>
      							</td>
      						</tr>
      					</table>
      				</td>
      			</tr>
				<tr>
      				<td>&nbsp;</td>
      			</tr>
      			<tr>
      				<td class="formAreaTitle">
      					API Settings - <a href="https://app.oneall.com/applications/" target="_blank">Click here to create and view your API Credentials</a>
      				</td>
      			</tr>
      			<tr>
      				<td class="formArea">
      					<table border="0" cellspacing="2" cellpadding="2">
      						<tr>
      						 	<td class="main" style="width:200px">API Subdomain:</td>
      						 	<td class="main"><?php echo zen_draw_input_field ('api_subdomain', htmlspecialchars ($api_subdomain), 'size="35" id="api_subdomain"'); ?></td>
      						 </tr>
      						 <tr>
      						 	<td class="main" style="width:200px">API Public Key:</td>
      						 	<td class="main"><?php echo zen_draw_input_field ('api_key', htmlspecialchars ($api_key), 'size="35" id="api_key"'); ?></td>
      						 </tr>
      						 <tr>
      						 	<td class="main" style="width:200px">API Private Key:</td>
      						 	<td class="main"><?php echo zen_draw_input_field ('api_secret', htmlspecialchars ($api_secret), 'size="35" id="api_secret"'); ?></td>
      						 </tr>
      					</table>
      				</td>
      			</tr>
      			<tr>
      				<td>
      					<table border="0" cellspacing="2" cellpadding="2">
      						<tr>
      							<td>
      								<input type="button" id="verify_button" value="Click here to verify the API settings" />
      							</td>
      							<td>
      								<strong id="verify_result"></strong>
      							</td>
      						</tr>
      					</table>
      				</td>
      			</tr>
      			<tr>
      				<td>&nbsp;</td>
      			</tr>
      			<tr>
      				<td class="formAreaTitle">
      					General Settings
      				</td>
      			</tr>
      			<tr>
      				<td class="formArea">
      					<table border="0" cellspacing="2" cellpadding="2">
      						<tr>
      						 	<td class="main" style="width:200px">Social Login Title:</td>
      						 	<td class="main"><?php echo zen_draw_input_field ('sidebox_title', htmlspecialchars ($sidebox_title), 'size="35" id="sidebox_title"'); ?></td>
      						 </tr>    
      						<tr>
      						 	<td class="main" style="width:200px">
      						 		Send email to customers?
      						 	</td>
      						 	<td class="main">
	      							<?php 
										echo zen_draw_radio_field ('send_mail_customers', '0', ($send_mail_customers == '0'), '', 'id="send_mail_customers_0"');
									  	echo '<label class="radioButtonLabel" for="send_mail_customers_0">No</label>';
									?>
									
									<?php
										echo zen_draw_radio_field ('send_mail_customers', '1', ($send_mail_customers <> '0'), '', 'id="send_mail_customers_1"');
									 	echo '<label class="radioButtonLabel" for="send_mail_customers_1">Yes, send email to new customers</label>';
									?>
								</td>
      						 </tr>        			
      						 <tr>
      						 	<td class="main" style="width:200px">
      						 		Send email to admin?
      						 	</td>
      						 	<td class="main">
	      							<?php 
										echo zen_draw_radio_field ('send_mail_admin', '0', ($send_mail_admin == '0'), '', 'id="send_mail_admin_0"');
									  	echo '<label class="radioButtonLabel" for="send_mail_admin_0">No</label>';
									?>
	      							<?php
										echo zen_draw_radio_field ('send_mail_admin', '1', ($send_mail_admin <> '0'), '', 'id="send_mail_admin_1"');
									 	echo '<label class="radioButtonLabel" for="send_mail_admin_1">Yes, inform admin of new customers</label>';
									?>									
								</td>
      						 </tr>       						 
      					</table>
      				</td>
      			</tr>
      			<tr>
      				<td>&nbsp;</td>
      			</tr>      			
      			<tr>
      				<td class="formAreaTitle">
      					Social Networks - <a href="https://app.oneall.com/insights/" target="_blank">Click here to view your Social Login statistics</a>
      				</td>
      			</tr>
						<tr>
      				<td class="formArea">
      					<table border="0" cellspacing="2" cellpadding="2">
      						<?php
							  foreach ($available_providers AS $provider_key => $provider_name)
							  {
							  ?>
      									<tr>
      						 				<td class="main" style="width:200px">
      						 					<?php echo $provider_name; ?>
      						 				</td>
      						 				<td class="main">
      						 					<?php
													   echo zen_draw_radio_field ('providers[' . $provider_key . ']', '1', in_array ($provider_key, $enabled_providers), '', 'id="provider-' . $provider_key . '-1"');
													   echo '<label class="radioButtonLabel" for="provider-' . $provider_key . '-1">Enabled</label>';
												   ?>

      						 					<?php
													   echo zen_draw_radio_field ('providers[' . $provider_key . ']', '0', !in_array ($provider_key, $enabled_providers), '', 'id="provider-' . $provider_key . '-0"');
													   echo '<label class="radioButtonLabel" for="provider-' . $provider_key . '-0">Disabled</label>';
												   ?>
      						 				</td>
      						 			</tr>
      								<?php
									  }
									  ?>
      					</table>
      				</td>
      			</tr>
      			<tr>
      				<td>&nbsp;</td>
      			</tr>
      			<tr>
      				<td>
      					<?php
						  echo zen_image_submit ('button_update.gif', IMAGE_UPDATE);
						  ?>
      				</td>
      			</tr>
      		</table>
      	</td>
    	</tr>
		</table>
		<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
	<br />
	</body>
</html>
<?php 

require(DIR_WS_INCLUDES . 'application_bottom.php'); 
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

/**
 *
 * INSTALLATION SCRIPT
 * READ THE FILE INSTALL.TXT FIRST
 *
 */
require('includes/application_top.php');


//Define tables
if (!defined ('TABLE_ONEALLSOCIALLOGIN'))
{
	define ('TABLE_ONEALLSOCIALLOGIN', 1);
	define ('TABLE_ONEALLSOCIALLOGIN_CONFIG', DB_PREFIX . 'oasl_config');
	define ('TABLE_ONEALLSOCIALLOGIN_USER', DB_PREFIX . 'oasl_user');
	define ('TABLE_ONEALLSOCIALLOGIN_IDENTITY', DB_PREFIX . 'oasl_identity');
}

// Available providers
$providers = array (
	'facebook' => 'Facebook',
	'twitter' => 'Twitter',
	'google' => 'Google',
	'linkedin' => 'LinkedIn',
	'yahoo' => 'Yahoo',
	'github' => 'Github.com',
	'foursquare' => 'Foursquare',
	'youtube' => 'YouTube',
	'skyrock' => 'Skyrock.com',
	'openid' => 'OpenID',
	'wordpress' => 'Wordpress.com',
	'hyves' => 'Hyves',
	'paypal' => 'PayPal',
	'livejournal' => 'LiveJournal',
	'steam' => 'Steam Community',
	'windowslive' => 'Windows Live',
	'blogger' => 'Blogger',
	'disqus' => 'Disqus',
	'stackexchange' => 'StackExchange',
	'vkontakte' => 'VKontakte',
	'odnoklassniki' => 'Odnoklassniki.ru',
	'mailru' => 'Mail.ru'
);

// Output
$messages = array ();

// Cleanup layout_boxes
$sql = "SELECT `layout_id` FROM " . TABLE_LAYOUT_BOXES . " WHERE `layout_box_name` = 'oneallsociallogin.php'";
$rows = $db->Execute ($sql);
while (!$rows->EOF)
{
	// Remove
	$sql = "DELETE FROM " . TABLE_LAYOUT_BOXES . " WHERE `layout_id` = '" . $rows->fields ['layout_id'] . "'";
	$result = $db->Execute ($sql);
	$messages [] = "Database entry [" . TABLE_LAYOUT_BOXES . ":" . $rows->fields ['layout_id'] . "] removed";

	// Goto next row
	$rows->MoveNext ();
}

// Add entry to layout_boxes
$sql = "INSERT INTO " . TABLE_LAYOUT_BOXES . " SET `layout_template` = 'classic', `layout_box_name` = 'oneallsociallogin.php', `layout_box_status` = 1, `layout_box_location` = 1, `layout_box_sort_order` = -1, `layout_box_sort_order_single` = 2, `layout_box_status_single` = 1";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_LAYOUT_BOXES . ":" . $db->Insert_ID () . "] added";


// Cleanup admin_pages
$sql = "SELECT `page_key` FROM " . TABLE_ADMIN_PAGES . " WHERE `page_key` = 'configOneallSocialLoginSettings'";
$rows = $db->Execute ($sql);
while (!$rows->EOF)
{
	// Remove
	$sql = "DELETE FROM " . TABLE_ADMIN_PAGES . " WHERE `page_key` = '" . $rows->fields ['page_key'] . "'";
	$result = $db->Execute ($sql);
	$messages [] = "Database entry [" . TABLE_ADMIN_PAGES . ":" . $rows->fields ['page_key'] . "] removed";

	// Goto next row
	$rows->MoveNext ();
}

// Calculate sort_order
$sql = "SELECT MAX(sort_order)+1 AS sort_order FROM " . TABLE_ADMIN_PAGES;
$result = $db->Execute ($sql);
$sort_order = $result->fields ['sort_order'];

// Add admin page
$sql = "INSERT INTO " . TABLE_ADMIN_PAGES . " SET `page_key` = 'configOneallSocialLoginSettings', `language_key` = 'BOX_CONFIGURATION_ONEALLSOCIALOGIN', `main_page` = 'FILENAME_CONFIGURATION', `page_params` = 'gID=" . $configuration_group_id . "', `menu_key` = 'configuration', `display_on_menu` = 'Y', `sort_order` = '" . $sort_order . "'";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_ADMIN_PAGES . ":configOneallSocialLoginSettings] added";

//Add user table
$sql = "CREATE TABLE IF NOT EXISTS " . TABLE_ONEALLSOCIALLOGIN_USER . " (`oasl_user_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `customers_id` int(11) unsigned NOT NULL, `user_token` varchar(48) NOT NULL, PRIMARY KEY (`oasl_user_id`))";
$result = $db->Execute ($sql);
$messages [] = "Database table [" . TABLE_ONEALLSOCIALLOGIN_USER . "] added";

// Add identity table
$sql = "CREATE TABLE IF NOT EXISTS " . TABLE_ONEALLSOCIALLOGIN_IDENTITY . " (`oasl_identity_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `oasl_user_id` int(11) unsigned NOT NULL, `identity_token` varchar(48) NOT NULL, `identity_provider` varchar(64) NOT NULL, `num_logins` int(10) unsigned NOT NULL, PRIMARY KEY (`oasl_identity_id`))";
$result = $db->Execute ($sql);
$messages [] = "Database table [" . TABLE_ONEALLSOCIALLOGIN_IDENTITY . "] added";

// Add config table
$sql = "CREATE TABLE IF NOT EXISTS " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " (`oasl_config_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `tag` varchar(32) NOT NULL, `data` text NOT NULL, PRIMARY KEY (`oasl_config_id`), UNIQUE KEY `tag` (`tag`))";
$result = $db->Execute ($sql);
$messages [] = "Database table [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . "] added";

// Add available providers
$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='available_providers', `data`='" . serialize ($providers) . "' ON DUPLICATE KEY UPDATE `data`='" . serialize ($providers) . "'";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":available_providers] added";

// Add enabled providers
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='enabled_providers', `data`='facebook,google,twitter,linkedin'";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":enabled_providers] added";

// Add API subdomain
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_subdomain', `data`=''";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":api_subdomain] added";

// Add API key
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_key', `data`=''";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":api_key] added";

// Add API secret
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_secret', `data`=''";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":api_secret] added";

// Add API connection protocol
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_connection_handler', `data`='curl'";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":api_connection_protocol] added";

// Add API connection https flag
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_connection_protocol', `data`='https'";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":api_connection_use_https] added";

// Add sidebox title
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='sidebox_title', `data`='Connect with'";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":sidebox_title] added";

// Add account linking flag
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='flag_account_linking', `data`='1'";
$result = $db->Execute ($sql);
$messages [] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":flag_account_linking] added";

//Done!
$messages [] = "<strong>Done! Please remove this file now.</strong>"

?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>OneAll Social Login: Installation</title>
	</head>
	<body>
		<h1>OneAll Social Login: Installation</h1>
		<h2>Status:</h2>
		<ul>
			<li>
			<?php
				echo implode ("</li><li>", $messages);
			?>
			</li>
		</ul>
	</body>
</html>
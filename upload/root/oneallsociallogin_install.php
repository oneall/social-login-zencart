<?php
/**
 * @package       OneAll Social Login
 * @copyright     Copyright 2011-Present http://www.oneall.com
 * @license       GNU/GPL 2 or later
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
require 'includes/application_top.php';

//Define tables
if (!defined('TABLE_ONEALLSOCIALLOGIN'))
{
    define('TABLE_ONEALLSOCIALLOGIN', 1);
    define('TABLE_ONEALLSOCIALLOGIN_CONFIG', DB_PREFIX . 'oasl_config');
    define('TABLE_ONEALLSOCIALLOGIN_USER', DB_PREFIX . 'oasl_user');
    define('TABLE_ONEALLSOCIALLOGIN_IDENTITY', DB_PREFIX . 'oasl_identity');
}

// Available providers
$providers = array(
    'amazon' => 'Amazon',
    'battlenet' => 'Battle.net',
    'blogger' => 'Blogger',
    'discord' => 'Discord',
    'disqus' => 'Disqus',
    'dribbble' => 'Dribbble',
    'facebook' => 'Facebook',
    'foursquare' => 'Foursquare',
    'github' => 'Github.com',
    'google' => 'Google',
    'instagram' => 'Instagram',
    'line' => 'Line',
    'linkedin' => 'LinkedIn',
    'livejournal' => 'LiveJournal',
    'mailru' => 'Mail.ru',
    'meetup' => 'Meetup',
    'mixer' => 'Mixer',
    'odnoklassniki' => 'Odnoklassniki',
    'openid' => 'OpenID',
    'paypal' => 'PayPal',
    'pinterest' => 'Pinterest',
    'pixelpin' => 'PixelPin',
    'reddit' => 'Reddit',
    'skyrock' => 'Skyrock.com',
    'soundcloud' => 'SoundCloud',
    'stackexchange' => 'StackExchange',
    'steam' => 'Steam',
    'tumblr' => 'Tumblr',
    'twitch' => 'Twitch.tv',
    'twitter' => 'Twitter',
    'vimeo' => 'Vimeo',
    'vkontakte' => 'VKontakte',
    'weibo' => 'Weibo',
    'windowslive' => 'Windows Live',
    'wordpress' => 'WordPress.com',
    'xing' => 'Xing',
    'yahoo' => 'Yahoo',
    'youtube' => 'YouTube'
);

// Output
$messages = array();

///////////////////////////////////////////////////////////////////////////////////////////////////////////
// TEMPLATES
///////////////////////////////////////////////////////////////////////////////////////////////////////////

// This is the default template directory
$default_template_dir = 'includes/templates/template_default/';

// This is the current template directory
$current_template_dir = DIR_WS_TEMPLATE;

// Overwrite existing files?
$replace_templates = true;

// Make sure the template directory actually exists
if (!empty($current_template_dir) && is_dir($current_template_dir))
{
    // Copy the files
    if ($default_template_dir != $current_template_dir)
    {
        // These are the files that we need to copy
        $files = array();
        $files[] = 'jscript/jscript_oneallsociallogin.php';
        $files[] = 'sideboxes/oneallsociallogin.php';
        $files[] = 'templates/tpl_oneallsociallogin_default.php';
        $files[] = 'templates/tpl_oneallsociallogin_widget.php';

        // Debug
        $messages[] = "Default template directory [" . $default_template_dir . "]";
        $messages[] = "Current template directory [" . $current_template_dir . "]";

        // Loop through files
        foreach ($files as $file)
        {
            // Make sure the file exists
            if (file_exists($default_template_dir . $file))
            {
                // Copy it to the template directory
                if (!file_exists($current_template_dir . $file) || $replace_templates)
                {
                    if (copy($default_template_dir . $file, $current_template_dir . $file))
                    {
                        $messages[] = "Copying template file to [" . $current_template_dir . $file . "]";
                    }
                    else
                    {
                        $messages[] = "ERROR! Please manually copy the file [" . $default_template_dir . $file . "] to [" . $current_template_dir . $file . "]";
                    }
                }
                else
                {
                    $messages[] = "ERROR! Template file missing [" . $default_template_dir . $file . "] Please re-upload the plugin";
                }
            }
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////
// LAYOUT BOXES
///////////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT `layout_id` FROM " . TABLE_LAYOUT_BOXES . " WHERE `layout_box_name` = 'oneallsociallogin.php'";
$rows = $db->Execute($sql);
while (!$rows->EOF)
{
    // Remove
    $sql = "DELETE FROM " . TABLE_LAYOUT_BOXES . " WHERE `layout_id` = '" . $rows->fields['layout_id'] . "'";
    $result = $db->Execute($sql);
    $messages[] = "Database entry [" . TABLE_LAYOUT_BOXES . ":" . $rows->fields['layout_id'] . "] removed";

    // Goto next row
    $rows->MoveNext();
}

// Defaults layouts for the sidebox
$layout_templates = array('classic');

// Read available layouts
$sql = "SELECT DISTINCT(layout_template) AS layout_template FROM " . TABLE_LAYOUT_BOXES;
$rows = $db->Execute($sql);
while (!$rows->EOF)
{
    $layout_template = $rows->fields['layout_template'];

    // This is to prevent re-adding the default layouts
    if (!in_array($layout_template, $layout_templates))
    {
        $layout_templates[] = $layout_template;
    }

    // Goto next row
    $rows->MoveNext();
}

// Add entry to layout_boxes
foreach ($layout_templates as $layout_template)
{
    $sql = "INSERT INTO " . TABLE_LAYOUT_BOXES . " SET `layout_template` = '" . $layout_template . "', `layout_box_name` = 'oneallsociallogin.php', `layout_box_status` = 1, `layout_box_location` = 1, `layout_box_sort_order` = -1, `layout_box_sort_order_single` = 2, `layout_box_status_single` = 1";
    $result = $db->Execute($sql);
    $messages[] = "Database entry [" . TABLE_LAYOUT_BOXES . ":" . $db->Insert_ID() . "] added, layout [" . $layout_template . "]";
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////
// ADMIN PAGES
///////////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT `page_key` FROM " . TABLE_ADMIN_PAGES . " WHERE `page_key` = 'configOneallSocialLoginSettings'";
$rows = $db->Execute($sql);
while (!$rows->EOF)
{
    // Remove
    $sql = "DELETE FROM " . TABLE_ADMIN_PAGES . " WHERE `page_key` = '" . $rows->fields['page_key'] . "'";
    $result = $db->Execute($sql);
    $messages[] = "Database entry [" . TABLE_ADMIN_PAGES . ":" . $rows->fields['page_key'] . "] removed";

    // Goto next row
    $rows->MoveNext();
}

// Calculate sort_order
$sql = "SELECT MAX(sort_order)+1 AS sort_order FROM " . TABLE_ADMIN_PAGES;
$result = $db->Execute($sql);
$sort_order = $result->fields['sort_order'];

// Add admin page
$sql = "INSERT INTO " . TABLE_ADMIN_PAGES . " SET `page_key` = 'configOneallSocialLoginSettings', `language_key` = 'BOX_CONFIGURATION_ONEALLSOCIALOGIN', `main_page` = 'FILENAME_CONFIGURATION', `page_params` = 'gID=" . $configuration_group_id . "', `menu_key` = 'configuration', `display_on_menu` = 'Y', `sort_order` = '" . $sort_order . "'";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ADMIN_PAGES . ":configOneallSocialLoginSettings] added";

///////////////////////////////////////////////////////////////////////////////////////////////////////////
// USER
///////////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "CREATE TABLE IF NOT EXISTS " . TABLE_ONEALLSOCIALLOGIN_USER . " (`oasl_user_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `customers_id` int(11) unsigned NOT NULL, `user_token` varchar(48) NOT NULL, PRIMARY KEY (`oasl_user_id`))";
$result = $db->Execute($sql);
$messages[] = "Database table [" . TABLE_ONEALLSOCIALLOGIN_USER . "] added";

///////////////////////////////////////////////////////////////////////////////////////////////////////////
// IDENTITY
///////////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "CREATE TABLE IF NOT EXISTS " . TABLE_ONEALLSOCIALLOGIN_IDENTITY . " (`oasl_identity_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `oasl_user_id` int(11) unsigned NOT NULL, `identity_token` varchar(48) NOT NULL, `identity_provider` varchar(64) NOT NULL, `num_logins` int(10) unsigned NOT NULL, PRIMARY KEY (`oasl_identity_id`))";
$result = $db->Execute($sql);
$messages[] = "Database table [" . TABLE_ONEALLSOCIALLOGIN_IDENTITY . "] added";

///////////////////////////////////////////////////////////////////////////////////////////////////////////
// CONFIG
///////////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "CREATE TABLE IF NOT EXISTS " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " (`oasl_config_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `tag` varchar(32) NOT NULL, `data` text NOT NULL, PRIMARY KEY (`oasl_config_id`), UNIQUE KEY `tag` (`tag`))";
$result = $db->Execute($sql);
$messages[] = "Database table [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . "] added";

// Settings \ Available providers
$sql = "INSERT INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='available_providers', `data`='" . serialize($providers) . "' ON DUPLICATE KEY UPDATE `data`='" . serialize($providers) . "'";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":available_providers] added";

// Settings \ Enabled providers
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='enabled_providers', `data`='facebook,google,twitter,linkedin'";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":enabled_providers] added";

// Settings \ API subdomain
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_subdomain', `data`=''";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":api_subdomain] added";

// Settings \ API key
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_key', `data`=''";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":api_key] added";

// Settings \ API secret
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_secret', `data`=''";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":api_secret] added";

// Settings \ API connection protocol
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_connection_handler', `data`='curl'";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":api_connection_protocol] added";

// Settings \ API connection https flag
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='api_connection_protocol', `data`='https'";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":api_connection_use_https] added";

// Settings \ Sidebox title
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='sidebox_title', `data`='Connect with'";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":sidebox_title] added";

// Settings \ Account linking flag
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='flag_account_linking', `data`='1'";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":flag_account_linking] added";

// Settings \ Send email to customers
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='send_mail_customers', `data`='1'";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":send_mail_customers] added";

// Settings \ Send email to customers
$sql = "INSERT IGNORE INTO " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " SET `tag`='send_mail_admin', `data`='1'";
$result = $db->Execute($sql);
$messages[] = "Database entry [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . ":send_mail_admin] added";

//Done!
$messages[] = "<strong>Done! Please remove this file now.</strong>"

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
echo implode("</li><li>", $messages);
?>
			</li>
		</ul>
	</body>
</html>

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
 * SHOW ONEALL SOCIAL LOGIN SIDEBOX
 *
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

//Compute enabled providers
$oasl_enabled_providers = array ();
if (!empty ($oasl_config ['enabled_providers']))
{
	$oasl_enabled_providers = explode (',', $oasl_config ['enabled_providers']);
	$oasl_enabled_providers = array_map ("strtolower", $oasl_enabled_providers);
	$oasl_enabled_providers = array_map ("trim", $oasl_enabled_providers);
}

//Setup parameters
$oasl_sidebox_title = (!empty ($oasl_config ['sidebox_title']) ? $oasl_config ['sidebox_title'] : '');
$oasl_sidebox_rand = mt_rand (10000, 99999);
$oasl_sidebox_providers = implode ("','", $oasl_enabled_providers);
$oasl_sidebox_callback = zen_href_link ('oneallsociallogin', 'origin=');

//Setup sidebox
$title = $oasl_sidebox_title;
$content = <<<HEREDOC
	<div class="oneall_social_login_providers" id="oneall_social_login_providers_$oasl_sidebox_rand"></div>
	<script type="text/javascript">
		oneall.api.plugins.social_login.build("oneall_social_login_providers_{$oasl_sidebox_rand}", {
			"providers": ['$oasl_sidebox_providers'],
			"callback_uri": '$oasl_sidebox_callback' + encodeURIComponent(window.location.href),
		});
	</script>
HEREDOC;


?>
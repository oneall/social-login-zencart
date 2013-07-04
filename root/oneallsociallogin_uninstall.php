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
 * REMOVAL SCRIPT
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

// Output
$messages = array ();

// Check if Social Login is installed.
$sql = "SHOW TABLES like '".TABLE_ONEALLSOCIALLOGIN_CONFIG."'";
$result = $db->Execute ($sql);
$num_entries = $result->RecordCount();

// There are no entries : Social Login is not installed
if (empty ($num_entries))
{
	$messages [] = "Could not find the table <em>".TABLE_ONEALLSOCIALLOGIN_CONFIG."</em> : Social Login does not seem to be installed";
	$messages [] = "<strong>UnInstallation Aborted!</strong>";
}
else
{
	// Prevent removal if there are already entries.
	$sql = "SELECT COUNT(*) AS total FROM " . TABLE_ONEALLSOCIALLOGIN_USER;
	$result = $db->Execute ($sql);
	$num_users = $result->fields ['total'];

	// There are already entries : cannot remove at this time
	if ( ! empty ($num_users))
	{
		$messages [] = "For security reasons Social Login cannot be uninstalled at this time: there are already entries in the table <em>".TABLE_ONEALLSOCIALLOGIN_USER."</em>";
		$messages [] = "Please manually empty the table <em>".TABLE_ONEALLSOCIALLOGIN_USER."</em> and then try again.";
		$messages [] = "<strong>UnInstallation Aborted!</strong>";
	}
	else
	{
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

		//Remove users table
		$sql = "DROP TABLE IF EXISTS " . TABLE_ONEALLSOCIALLOGIN_USER;
		$result = $db->Execute ($sql);
		$messages [] = "Database table [" . TABLE_ONEALLSOCIALLOGIN_USER . "] removed";

		// Remove identity table
		$sql = "DROP TABLE IF EXISTS " . TABLE_ONEALLSOCIALLOGIN_IDENTITY;
		$result = $db->Execute ($sql);
		$messages [] = "Database table [" . TABLE_ONEALLSOCIALLOGIN_IDENTITY . "] removed";

		// Remove config table
		$sql = "DROP TABLE IF EXISTS " . TABLE_ONEALLSOCIALLOGIN_CONFIG;
		$result = $db->Execute ($sql);
		$messages [] = "Database table [" . TABLE_ONEALLSOCIALLOGIN_CONFIG . "] removed";

		//Done!
		$messages [] = "<strong>Done! Please remove this file now.</strong>";
	}
}

?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>OneAll Social Login: Removal</title>
	</head>
	<body>
		<h1>OneAll Social Login: Removal</h1>
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
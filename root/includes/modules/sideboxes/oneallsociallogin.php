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
 * SIDEBOX SETUP
 *
 */

//The sidebox is only displayed if the user is not logged in
if (empty ($_SESSION ['customer_id']) AND ! defined ('DISABLE_ONEALLSOCIALLOGIN'))
{
	//Sidebox Start
	$zco_notifier->notify ('NOTIFY_START_ONEALLSOCIALLOGIN_SIDEBOX');

	//Include Sidebox
	require($template->get_template_dir ('oneallsociallogin.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/oneallsociallogin.php');

	//SideBox end
	$zco_notifier->notify ('NOTIFY_END_ONEALLSOCIALLOGIN_SIDEBOX');

	//Show Template
	require($template->get_template_dir ($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default);
}
?>
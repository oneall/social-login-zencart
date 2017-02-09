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

/**
 * SHOW ONEALL SOCIAL LOGIN WIDGET. INCLUDE LIKE THIS:
 *
 * <?php
 * require ($template->get_template_dir ('tpl_oneallsociallogin_widget.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_oneallsociallogin_widget.php');
 * ?>
 */

// Might already exist if included multiple times
if (!function_exists ('oneall_sociallogin_widget'))
{
	// Displays the widget
	function oneall_sociallogin_widget ($db)
	{
		// Configuration values
		$cfg_values = array();
		
		// Read config
		$query = "SELECT `tag`, `data` FROM " . TABLE_ONEALLSOCIALLOGIN_CONFIG;
		$rows = $db->Execute ($query);
		
		while ( !$rows->EOF )
		{
			$cfg_values [$rows->fields ['tag']] = $rows->fields ['data'];
			$rows->MoveNext ();
		}
		
		// Compute enabled providers
		$enabled_providers = array();
		if (!empty ($cfg_values ['enabled_providers']))
		{
			$enabled_providers = explode (',', $cfg_values ['enabled_providers']);
			$enabled_providers = array_map ("strtolower", $enabled_providers);
			$enabled_providers = array_map ("trim", $enabled_providers);
		}
		
		// Setup parameters
		$widget_providers = implode ("','", $enabled_providers);
		$widget_callback = zen_href_link ('oneallsociallogin', 'origin=');
		$widget_container_id = 'oneall_social_login_providers_'.mt_rand (10000, 99999);
		
		?>
			<div class="oneall_social_login-widget">
				<div class="oneall_social_login_providers" id="<?php echo $widget_container_id;?>"></div>
					<script type="text/javascript">
						var _oneall = _oneall || [];
						_oneall.push(['social_login', 'set_providers', ['<?php echo $widget_providers; ?>']]);
						_oneall.push(['social_login', 'set_callback_uri', '<?php echo $widget_callback; ?>' + encodeURIComponent(window.location.href)]);
						_oneall.push(['social_login', 'do_render_ui', '<?php echo $widget_container_id; ?>']);
					</script>
				</div>
			</div>
		<?php
	}
}

//Display Widget
oneall_sociallogin_widget ($db);
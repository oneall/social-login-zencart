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
 * INCLUDE ONEALL SOCIAL LIBRARY
 *
 */


// Read config
$query = "SELECT `data` FROM " . TABLE_ONEALLSOCIALLOGIN_CONFIG . " WHERE `tag`='api_subdomain' LIMIT 1";
$oasl_config = $db->Execute ($query);

//Display libary if subdomain found
if (!empty ($oasl_config->fields ['data']))
{
?>
	<!-- OneAl Social Login | http://www.oneall.com/ -->
	<script type="text/javascript">
		var oneall_subdomain = '<?php echo $oasl_config->fields ['data']; ?>';
		var oneall_js_protocol = (("https:" == document.location.protocol) ? "https" : "http");
		document.write(unescape("%3Cscript src='"+oneall_js_protocol+"://"+oneall_subdomain+".api.oneall.com/socialize/library.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
<?php
}

?>
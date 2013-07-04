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
 * COLLECT ADDITIONAL USER DATA
 *
 */

?>
<div class="centerColumn" id="accountEditDefault">
	<?php echo zen_draw_form ('oneallsociallogin', zen_href_link ('oneallsociallogin'), 'post') . zen_draw_hidden_field ('action', 'process'); ?>
		<?php if ($messageStack->size ('oneallsociallogin') > 0) echo $messageStack->output ('oneallsociallogin'); ?>
		<h2><?php echo sprintf (OASL_CONNECTED_WITH, $user_data ['identity_provider']); ?></h1>
		<p>
			<?php echo OASL_TAKE_MINUTE_TO_REVIEW; ?>
			<?php echo sprintf (OASL_READY_FOR, $user_data ['identity_provider']); ?>
		</p>
		<fieldset>
			<legend><?php echo OASL_REVIEW_DETAILS; ?></legend>
			<div class="alert forward"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
			<br class="clearBoth" />
			<?php
				if (ACCOUNT_GENDER == 'true')
				{
					$gender = (!empty ($user_data ['user_gender']) ? $user_data ['user_gender'] : '');
					echo zen_draw_radio_field ('gender', 'm', $gender, 'id="gender-male"') . '<label class="radioButtonLabel" for="gender-male">' . MALE . '</label>' . zen_draw_radio_field ('gender', 'f', $female, 'id="gender-female"') . '<label class="radioButtonLabel" for="gender-female">' . FEMALE . '</label>' . (zen_not_null (ENTRY_GENDER_TEXT) ? '<span class="alert">' . ENTRY_GENDER_TEXT . '</span>' : '');
					?>
						<br class="clearBoth" />
					<?php
				}
			?>

			<label class="inputLabel" for="firstname"><?php echo ENTRY_FIRST_NAME; ?></label>
			<?php
				$firstname = (!empty ($user_data ['user_first_name']) ? $user_data ['user_first_name'] : '');
				echo zen_draw_input_field ('firstname', $firstname, 'id="firstname"') . (zen_not_null (ENTRY_FIRST_NAME_TEXT) ? '<span class="alert">' . ENTRY_FIRST_NAME_TEXT . '</span>' : '');
			?>
			<br class="clearBoth" />

			<label class="inputLabel" for="lastname"><?php echo ENTRY_LAST_NAME; ?></label>
			<?php
				$lastname = (!empty ($user_data ['user_last_name']) ? $user_data ['user_last_name'] : '');
				echo zen_draw_input_field ('lastname', $lastname, 'id="lastname"') . (zen_not_null (ENTRY_LAST_NAME_TEXT) ? '<span class="alert">' . ENTRY_LAST_NAME_TEXT . '</span>' : '');
			?>
			<br class="clearBoth" />

			<?php
				if (ACCOUNT_DOB == 'true')
				{
					?>
						<label class="inputLabel" for="dob"><?php echo ENTRY_DATE_OF_BIRTH; ?></label>
						<?php
							$birthdate = (!empty ($user_data ['user_birthdate']) ? $user_data ['user_birthdate'] : '');
							echo zen_draw_input_field ('dob', $birthdate, 'id="dob"') . (zen_not_null (ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="alert">' . ENTRY_DATE_OF_BIRTH_TEXT . '</span>' : '');
						?>
						<br class="clearBoth" />
					<?php
				}
			?>

			<label class="inputLabel" for="email-address"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
			<?php
				$email = (!empty ($user_data ['user_email']) ? $user_data ['user_email'] : '');
				echo zen_draw_input_field ('email_address', $email, 'id="email-address"') . (zen_not_null (ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="alert">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>' : '');
			?>
			<br class="clearBoth" />

			<label class="inputLabel" for="telephone"><?php echo ENTRY_TELEPHONE_NUMBER; ?></label>
			<?php
				$telephone = (!empty ($user_data ['user_phone']) ? $user_data ['user_phone'] : '');
				echo zen_draw_input_field ('telephone', $telephone, 'id="telephone"') . (zen_not_null (ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="alert">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>' : '');
			?>
			<br class="clearBoth" />

			<label class="inputLabel" for="country"><?php echo ENTRY_COUNTRY; ?></label>
			<?php
				$country = (!empty ($user_data ['user_country_id']) ? $user_data ['user_country_id'] : '');
				echo zen_get_country_list('country_id', $country, 'id="country" ') . (zen_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="alert">' . ENTRY_COUNTRY_TEXT . '</span>': '');
			?>
			<br class="clearBoth" />

		</fieldset>
		<div class="buttonRow forward"><?php echo zen_image_submit (BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT); ?></div>
		<br class="clearBoth" />
	</form>
</div>
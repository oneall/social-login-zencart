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
 * LANGUAGE STRINGS
 *
 */

define('OASL_EMAIL_SUBJECT', 'Welcome to ' . STORE_NAME);
define('OASL_EMAIL_GREET_MR', 'Dear Mr. %s,' . "\n\n");
define('OASL_EMAIL_GREET_MS', 'Dear Ms. %s,' . "\n\n");
define('OASL_EMAIL_GREET_NONE', 'Dear %s' . "\n\n");
define('OASL_CONNECTED_WITH', 'You have connected with %s !');
define('OASL_TAKE_MINUTE_TO_REVIEW', 'Please take a minute to review and complete your account information.');
define('OASL_READY_FOR', 'Once you have reviewed your details, your account is ready to use and you can sign in with %s.');
define('OASL_REVIEW_DETAILS', 'Please review your details');

//Email Settings
define('OASL_EMAIL_WELCOME', 'We wish to welcome you to <strong>' . STORE_NAME . '</strong>.');
define('OASL_EMAIL_TEXT', 'You are now registered with our store and have account privileges:  With your account, you can now take part in the <strong>various services</strong> we have to offer you. Some of these many services include:' . "\n\n<ul>" . '<li><strong>Order History</strong> - View the details of orders you have completed with us.' . "\n\n" . '<li><strong>Permanent Cart</strong> - Any products added to your online cart remain there until you remove them, or check them out.' . "\n\n" . '<li><strong>Address Book</strong> - We can deliver your products to an address other than yours! This is perfect to send birthday gifts direct to the birthday-person themselves.' . "\n\n" . '<li><strong>Products Reviews</strong> - Share your opinions on our products with other customers.' . "\n\n</ul>");
define('OASL_EMAIL_CONTACT', 'For help with any of our online services, please email the store-owner: <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">'. STORE_OWNER_EMAIL_ADDRESS ." </a>\n\n");
define('OASL_EMAIL_GV_CLOSURE', "\n" . 'Sincerely,' . "\n\n" . STORE_OWNER . "\nStore Owner\n\n". '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">'.HTTP_SERVER . DIR_WS_CATALOG ."</a>\n\n");

?>
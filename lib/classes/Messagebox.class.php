<?php
/**
 * Messagebox.class.php
 *
 * html-boxes for different kinds of messages
 *
 * LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author 		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @copyright	2009 Stud.IP
 * @license		http://www.gnu.org/licenses/gpl.html GPL Licence 3
 * @since 		Stud.IP version 1.10
 * @package 	studip
 * @subpackage 	layout
 *
 */

/**
 * class Messagebox
 *
 * usage:
 *
 * echo Messagebox::error('Nachricht', array('optional details'));
 *
 * use the optional parameter $close_details for displaying the messagebox with
 * closed details
 *
 * echo Messagebox::success('Nachricht', array('optional details'), true);
 *
 */
class Messagebox
{
	/**
	 * This function shows an error-messagebox. use it only for systemerrors or
	 * securityproblems
	 *
	 * @param string $message
	 * @param array() $details
	 * @param boolean $close_details
	 * @return string html-output of the messagebox
	 */
	public static function error($message, $details = '', $close_details = false)
	{
		$class = 'messagebox_error';
		return $GLOBALS['template_factory']->render('shared/message_box', compact('class', 'message', 'details', 'close_details'));
	}

	/**
	 * This function shows a warning-messagebox. use it for validation errors,
	 * problems and other wrong user-input
	 *
	 * @param string $message
	 * @param array() $details (optional)
	 * @param boolean $close_details (optional)
	 * @return string html-output of the messagebox
	 */
	public static function warning($message, $details = '', $close_details = false)
	{
		$class = 'messagebox_warning';
		return $GLOBALS['template_factory']->render('shared/message_box', compact('class', 'message', 'details', 'close_details'));
	}

	/**
	 * This function shows a success-messagebox. Use it for confirmation of user
	 * interaction
	 *
	 * @param string $message
	 * @param array() $details (optional)
	 * @param boolean $close_details (optional)
	 * @return string html-output of the messagebox
	 */
	public static function success($message, $details = '', $close_details = false)
	{
		$class = 'messagebox_success';
		return $GLOBALS['template_factory']->render('shared/message_box', compact('class', 'message', 'details', 'close_details'));
	}

	/**
	 * This function shows a success-messagebox. Use it for all other status
	 * informations.
	 *
	 * @param string $message
	 * @param array() $details (optional)
	 * @param boolean $close_details (optional)
	 * @return string html-output of the messagebox
	 */
	public static function info($message, $details = '', $close_details = false)
	{
		$class = 'messagebox_info';
		return $GLOBALS['template_factory']->render('shared/message_box', compact('class', 'message', 'details', 'close_details'));
	}
}
?>
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
 *
 */
class Messagebox
{
	/**
	 * Enter description here...
	 *
	 * @param string $message
	 * @param array() $details
	 * @param boolean $open_details
	 * @return string html-output of the messagebox
	 */
	public static function error($message, $details = '', $open_details = false)
	{
		$message = _('Systemfehler! ').$message;
		$class = 'messagebox_error';
		return $GLOBALS['template_factory']->render('shared/message_box', compact('class', 'message', 'details', 'open_details'));
	}

	/**
	 * Enter description here...
	 *
	 * @param string $message
	 * @param array() $details
	 * @param boolean $open_details
	 * @return string html-output of the messagebox
	 */
	public static function warning($message, $details = '', $open_details = false)
	{
		$class = 'messagebox_warning';
		return $GLOBALS['template_factory']->render('shared/message_box', compact('class', 'message', 'details', 'open_details'));
	}

	/**
	 * Enter description here...
	 *
	 * @param string $message
	 * @param array() $details
	 * @param boolean $open_details
	 * @return string html-output of the messagebox
	 */
	public static function success($message, $details = '', $open_details = false)
	{
		$class = 'messagebox_success';
		return $GLOBALS['template_factory']->render('shared/message_box', compact('class', 'message', 'details', 'open_details'));
	}

	/**
	 * Enter description here...
	 *
	 * @param string $message
	 * @param array() $details
	 * @param boolean $open_details
	 * @return string html-output of the messagebox
	 */
	public static function info($message, $details = '', $open_details = false)
	{
		$class = 'messagebox_info';
		return $GLOBALS['template_factory']->render('shared/message_box', compact('class', 'message', 'details', 'open_details'));
	}
}
?>
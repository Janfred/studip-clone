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
 * example of use it:
 * echo Messagebox::get('WARNING')->show('Warning Nachricht', array('Detail Fehler 1', 'Fehler 2', 'Fehler3'));
 *
 */
class Messagebox
{
	public static $INFO = 'messagebox_info';
	public static $ERROR = 'messagebox_error';
	public static $WARNING = 'messagebox_warning';
	public static $SUCCESS = 'messagebox_success';

	private $type;
	private $class;
	private $id;

	private static $instance;

	/**
	 * this is the constructor of this class. it creates an unique id and sets
	 * the type of the messagebox
	 *
	 * @param string $type
	 */
	public function __construct($type='INFO')
	{
		$this->id = md5(uniqid('messagebox'));
		$this->type = $type;
		$this->class = Messagebox::$$type;
	}

	/**
	 * creates an object of this class
	 *
	 * @param string $type
	 * @return Messagebox object
	 */
	public function get($type)
	{
		Messagebox::$instance = new Messagebox($type);
		return Messagebox::$instance;
	}

	/**
	 * creates the html of the messagebox with a message and optional details
	 * from an array()
	 *
	 * @param string $message
	 * @param array() $details
	 * @return html of the messagebox
	 */
	public function show($message, $details='')
	{
		//Zuviele Meldungen benutzen HTML
		//$message = ($this->type == 'ERROR')? htmlready(_('Systemfehler: ').$message) : htmlready($message);
		//Ersatz:
		$message = ($this->type == 'ERROR')? _('Systemfehler! ').$message : $message;
		$id = $this->id;
		$class = $this->class;
		return $GLOBALS['template_factory']->render('shared/message_box', compact('message', 'details', 'id', 'class'));
	}
}
?>
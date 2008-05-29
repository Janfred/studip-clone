<?
# Lifter002: TODO
/**
* msgs_support.inc.php
* 
* library for the messages (error, info and other)
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		support
* @module		msgs_support.inc.php
* @package		support
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// msgs_resources.inc.php
// Alle Meldungen, die in der Ressourcenverwaltung ausgegeben werden
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


$this->msg[1] = array (
		"mode" => "error",
		"msg"=> _("Sie k&ouml;nnen die Punktzahl nicht kleiner als die bereits verbrauchten Punkte einstellen!"));
$this->msg[2] = array (
		"mode" => "error",
		"msg"=> _("Ung&uuml;ltiges Anfangsdatum des Vertrages!"));
$this->msg[3] = array (
		"mode" => "error",
		"msg"=> _("Ung&uuml;ltiges Enddatum des Vertrages!"));
$this->msg[4] = array (
		"mode" => "msg",
		"msg"=> _("Der Vertrag wurde gel&ouml;scht"));
$this->msg[5] = array (
		"mode" => "msg",
		"msg"=> _("Die Anfrage wurde gel&ouml;scht"));
$this->msg[6] = array (
		"mode" => "msg",
		"msg"=> _("Ung&uuml;ltiges Datum der Anfrage!"));
$this->msg[7] = array (
		"mode" => "error",
		"msg"=> _("Bitte geben Sie einen Bearbeiter/eine Bearbeiterin f&uuml;r die Bearbeitungszeit an!"));	
$this->msg[8] = array (
		"mode" => "error",
		"msg"=> _("Bitte w&auml;hlen Sie zun&auml;chst einen Vertrag aus, um die Anfragen einzusehen oder zu bearbeiten!"));
$this->msg[9] = array (
		"mode" => "info",
		"msg"=> _("Es existieren keine TutorInnen oder AutorInnen, die mit der Anfrage verkn&uuml;pft werden k&ouml;nnten."));

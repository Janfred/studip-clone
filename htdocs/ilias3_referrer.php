<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ilias3_referrer.php
//
// Copyright (c) 2005 Arne Schroeder <schroeder@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("autor");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

require_once ($ABSOLUTE_PATH_STUDIP."/config.inc.php");

//include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
//include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

if ($ELEARNING_INTERFACE_ENABLE)
{
	require_once ($ABSOLUTE_PATH_STUDIP."/" . $RELATIVE_PATH_ELEARNING_MODULES . "elearning/ELearningUtils.class.php");
	ELearningUtils::bench("start");

	$GLOBALS['ALWAYS_SELECT_DB'] = true;

	

	if (isset($ELEARNING_INTERFACE_MODULES[$cms_select]["name"]))
	{

		ELearningUtils::loadClass($cms_select);
		// init session now
		$sess_id = $connected_cms[$cms_select]->user->getSessionId();
		$connected_cms[$cms_select]->terminate();
		
		$parameters = "?sess_id=$sess_id";
		if (isset($client_id))
			$parameters .= "&client_id=$client_id";
		if (isset($target))
			$parameters .= "&target=$target";
		if (isset($ref_id))
			$parameters .= "&ref_id=$ref_id";
		if (isset($type))
			$parameters .= "&type=$type";
		
		// refer to studip_referrer.php
		header("Location: ".$ELEARNING_INTERFACE_MODULES[$cms_select]["ABSOLUTE_PATH_ELEARNINGMODULES"] . $ELEARNING_INTERFACE_MODULES[$cms_select]["target_file"] . $parameters);
		exit();
	}
	 
/**/	
	if ($debug != "")
	{
		ELearningUtils::bench("error");
		ELearningUtils::showbench();
	}
}

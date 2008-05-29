<?
# Lifter002: TODO
/**
* supportConfig.inc.php
* 
* Configuration file for studip module supportDB
* 
* @access		public
* @package		support
* @modulegroup	support
* @module		supportConfig.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// supportConfig.inc.php
// Konfiguration fuer das Stud.IP Modul SupportDB
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, 
// Ralf Stockmann <rstockm@gwdg.de>, André Noack André Noack <andre.noack@gmx.net>
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


//get the base date for point-calculation from the request (opposite would be from event)
$BASE_DATE_FROM_REQUEST = TRUE;

//change the rate, if the duration laps into another range
$CHANGE_RATE = FALSE;

//Sunday
$POINTS[0][] = array ("begin_hour" => 0, "begin_min" => 0, "end_hour" => 7, "end_min" =>59, "min" => 60, "ratio" => 8);
$POINTS[0][] = array ("begin_hour" => 8, "begin_min" => 0, "end_hour" => 19, "end_min" =>59, "min" => 60, "ratio" => 6);
$POINTS[0][] = array ("begin_hour" => 20, "begin_min" => 0, "end_hour" => 23, "end_min" =>59, "min" => 60, "ratio" => 8);

//Monday
$POINTS[1][] = array ("begin_hour" => 0, "begin_min" => 0, "end_hour" => 7, "end_min" =>59, "min" => 60, "ratio" => 6);
$POINTS[1][] = array ("begin_hour" => 8, "begin_min" => 0, "end_hour" => 19, "end_min" =>59, "min" => 15, "ratio" => 1);
$POINTS[1][] = array ("begin_hour" => 20, "begin_min" => 0, "end_hour" => 23, "end_min" =>59, "min" => 60, "ratio" => 6);

//Tuesday
$POINTS[2][] = array ("begin_hour" => 0, "begin_min" => 0, "end_hour" => 7, "end_min" =>59, "min" => 60, "ratio" => 6);
$POINTS[2][] = array ("begin_hour" => 8, "begin_min" => 0, "end_hour" => 19, "end_min" =>59, "min" => 15, "ratio" => 1);
$POINTS[2][] = array ("begin_hour" => 20, "begin_min" => 0, "end_hour" => 23, "end_min" =>59, "min" => 60, "ratio" => 6);

//Wednesday
$POINTS[3][] = array ("begin_hour" => 0, "begin_min" => 0, "end_hour" => 7, "end_min" =>59, "min" => 60, "ratio" => 6);
$POINTS[3][] = array ("begin_hour" => 8, "begin_min" => 0, "end_hour" => 19, "end_min" =>59, "min" => 15, "ratio" => 1);
$POINTS[3][] = array ("begin_hour" => 20, "begin_min" => 0, "end_hour" => 23, "end_min" =>59, "min" => 60, "ratio" => 6);

//Thursday
$POINTS[4][] = array ("begin_hour" => 0, "begin_min" => 0, "end_hour" => 7, "end_min" =>59, "min" => 60, "ratio" => 6);
$POINTS[4][] = array ("begin_hour" => 8, "begin_min" => 0, "end_hour" => 19, "end_min" =>59, "min" => 15, "ratio" => 1);
$POINTS[4][] = array ("begin_hour" => 20, "begin_min" => 0, "end_hour" => 23, "end_min" =>59, "min" => 60, "ratio" => 6);

//Friday
$POINTS[5][] = array ("begin_hour" => 0, "begin_min" => 0, "end_hour" => 7, "end_min" =>59, "min" => 60, "ratio" => 6);
$POINTS[5][] = array ("begin_hour" => 8, "begin_min" => 0, "end_hour" => 19, "end_min" =>59, "min" => 15, "ratio" => 1);
$POINTS[5][] = array ("begin_hour" => 20, "begin_min" => 0, "end_hour" => 23, "end_min" =>59, "min" => 60, "ratio" => 6);

//Saturday
$POINTS[6][] = array ("begin_hour" => 0, "begin_min" => 0, "end_hour" => 7, "end_min" =>59, "min" => 60, "ratio" => 8);
$POINTS[6][] = array ("begin_hour" => 8, "begin_min" => 0, "end_hour" => 19, "end_min" =>59, "min" => 60, "ratio" => 6);
$POINTS[6][] = array ("begin_hour" => 20, "begin_min" => 0, "end_hour" => 23, "end_min" =>59, "min" => 60, "ratio" => 8);

//Holiday
$POINTS["holiday"][] = array ("begin_hour" => 0, "begin_min" => 0, "end_hour" => 7, "end_min" =>59, "min" => 60, "ratio" => 8);
$POINTS["holiday"][] = array ("begin_hour" => 8, "begin_min" => 0, "end_hour" => 19, "end_min" =>59, "min" => 60, "ratio" => 6);
$POINTS["holiday"][] = array ("begin_hour" => 20, "begin_min" => 0, "end_hour" => 23, "end_min" =>59, "min" => 60, "ratio" => 8);
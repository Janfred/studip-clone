<?
/**
* calendar_misc_func.inc.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>
* @version	$Id$
* @access		public
* @modulegroup	calendar
* @module		calendar
* @package	calendar_misc_func
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// calendar_misc_func.inc.php
// 
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>
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


function cmp ($a, $b) {
	$start_a = date("Gi", $a->getStart());
	$start_b = date("Gi", $b->getStart());
	if($start_a == $start_b)
		return 0;
	if($start_a < $start_b)
		return -1;
	return 1;
}

function cmp_list ($a, $b) {
	$start_a = $a->getStart();
	$start_b = $b->getStart();
	if($start_a == $start_b)
		return 0;
	if($start_a < $start_b)
		return -1;
	return 1;
}

?>

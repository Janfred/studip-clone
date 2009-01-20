<?
/*
links_siteinfo.inc.php - Navigation fuer das Impressum.
Copyright (C) 2008	Ansgar Bockstiegel <ansgar.bockstiegel@uos.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

global $view,$dynstradd;

require_once 'lib/include/reiter.inc.php';

$structure = array();

$db = DBManager::get();

$sql = "SELECT rubric_id, name
	    FROM `siteinfo_rubrics`
        ORDER BY position, rubric_id ASC";

$result = $db->query($sql);
$rubrics = $result->fetchAll();
foreach($rubrics AS $rubric){
	$structure['r'.$rubric[0]] = array('topKat' => '', 
                                   'name' => $rubric[1], 
                                   'link' => 'dispatch.php/siteinfo/show/'.$rubric[0],
                                   'active' => FALSE);
}

$sql = "SELECT detail_id, rubric_id, name
        FROM siteinfo_details
    	ORDER BY position, detail_id ASC";

$result = $db->query($sql);
$details = $result->fetchAll();
foreach($details AS $detail){
	$structure['r'.$detail[1].'_d'.$detail[0]] = array('topKat' => 'r'.$detail[1], 
                                                  'name' => $detail[2], 
                                                  'link' => 'dispatch.php/siteinfo/show/'.$detail[1].'/'.$detail[0],
                                                  'active' => FALSE);
}

$structure = $dynstradd ? array_merge($structure, $dynstradd) : $structure;
$reiter=new reiter;
$reiter->create($structure, $view);

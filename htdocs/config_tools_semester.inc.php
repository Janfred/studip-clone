<?
/*
config_tools_semester.inc.php -Tool zum Bereistellen grundlegender Daten des 
aktuellen und des folgenden Semesters
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

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

//Checken ob es sich um vergangene Semester handelt + checken, welches das aktuelle Semester ist und Daten daraus verwenden
$i=1;
for ($i; $i <= sizeof($SEMESTER); $i++)
	{ 
	if ($SEMESTER[$i]["ende"] < time()) $SEMESTER[$i]["past"]=TRUE;
	if (($SEMESTER[$i]["beginn"] < time()) && ($SEMESTER[$i]["ende"] >time()))
		{
		$VORLES_BEGINN=$SEMESTER[$i]["vorles_beginn"];
		$VORLES_ENDE=$SEMESTER[$i]["vorles_ende"];
		$SEM_BEGINN=$SEMESTER[$i]["beginn"];
		$SEM_ENDE=$SEMESTER[$i]["ende"];
		$SEM_NAME=$SEMESTER[$i]["name"];
		$SEM_ID=$i;
		if ($i<sizeof ($SEMESTER))
			{
			$VORLES_BEGINN_NEXT=$SEMESTER[$i+1]["vorles_beginn"];
			$VORLES_ENDE_NEXT=$SEMESTER[$i+1]["vorles_ende"];
			$SEM_BEGINN_NEXT=$SEMESTER[$i+1]["beginn"];
			$SEM_ENDE_NEXT=$SEMESTER[$i+1]["ende"];
			$SEM_NAME_NEXT=$SEMESTER[$i+1]["name"];			
			$SEM_ID_NEXT=$i+1;
			}
		}
	}
?>
<?
/**
* ScheduleWeek.class.php
* 
* creates a grafical schedule view for different purposes, ie. a personal timetable
* or a timetable for a ressource like a room, a device or a building
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>
* @version		$Id$
* @access		public
* @package		resources
* @modulegroup	resources_modules
* @module		ScheduleWeek.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ScheduleWeek.class.php
// Modul zum Erstellen grafischer Belegungspl&auml;ne
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>
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

class ScheduleWeek {
	var $events;				//the events that will be shown
	var $cell_allocations;			//internal Array
	var $start_hour;			//First hour to display from
	var $end_hour;				//Last hout to display to
	var $show_days;				//Arrays of the days that should be shown
	var $show_dates;			//If setted, the dates of each day will be shown
	var $start_date;			//the timestamp of the first day (monday) of the viewed week 
	var $categories = array(		//the categories configuration (color's and bg-image)
		"0"=>array("bg-picture"=>"/studip/pictures/calendar/category5_small.jpg", "border-color"=>"#505064"),
		"1"=>array("bg-picture"=>"/studip/pictures/calendar/category3_small.jpg", "border-color"=>"#5C2D64"),
		"2"=>array("bg-picture"=>"/studip/pictures/calendar/category9_small.jpg", "border-color"=>"#957C29"),
		"3"=>array("bg-picture"=>"/studip/pictures/calendar/category11_small.jpg", "border-color"=>"#66954F"),
		"4"=>array("bg-picture"=>"/studip/pictures/calendar/category13_small.jpg", "border-color"=>"#951408"),
		);
	
	//Kontruktor
	function ScheduleWeek ($start_hour = '', $end_hour = '', $show_days = '', $show_dates = '', $start_date = '') {
		$this->start_hour=$start_hour;
		$this->end_hour=$end_hour;
		
		if ((!$this->start_hour) && (!$this->end_hour)) {
			$this->start_hour = 8;
			$this->end_hour = 20;
		}
			
		if (!$show_days) {
			$this->show_days[1]=TRUE;
			$this->show_days[2]=TRUE;
			$this->show_days[3]=TRUE;
			$this->show_days[4]=TRUE;
			$this->show_days[5]=TRUE;
			$this->show_days[6]=TRUE;
			$this->show_days[7]=TRUE;
		} else
			$this->show_days=show_days;
			
		if ($show_dates)
			$this->show_dates=$show_dates;
		if ($start_date)
			$this->start_date=$start_date;
		if ((!$show_dates) && ($start_date))
			$this->show_dates=TRUE;	
		//the base_date have to be 0:00
		$this->base_date = mktime(0, 0, 0, date("n", $this->start_date), date("j",$this->start_date),  date("Y",$this->start_date));		
	}


	function addEvent ($name, $start_time, $end_time, $link='', $add_info='', $category=0) {
		if (date ("G", $end_time) >= $this->start_hour) {
			$week_day=date("w", $start_time);
			
			if ($week_day == 0)
				$week_day = 7;
				
			if (date ("G", $end_time) > $this->end_hour) {
				$rows = ((( $this->end_hour - date("G", $start_time))+1) *4);
				$rows = $rows - (int)(date("i", $start_time) / 15);
			} else 
				$rows = ceil(((date("G", $end_time) - date("G", $start_time)) * 4) + ((date("i", $end_time) - date("i", $start_time)) / 15));
				
			if (date ("G", $start_time) < $this->start_hour) {
				$rows = $rows - (($this->start_hour - date ("G", $start_time)) *4);
				$rows = $rows + (int)(date ("i", $start_time)/ 15);
				$idx_corr_h = $this->start_hour - date ("G", $start_time);
				$idx_corr_m = (0 - date ("i", $start_time)) ;
			} else {
				$idx_corr_h = 0;
				$idx_corr_m = 0;
			}
				
			$sort_index = 	date ("G", $start_time)+$idx_corr_h.(int)((date("i", $start_time)+$idx_corr_m) / 15).$week_day;			
			$id = md5(uniqid("rss"));
			$this->events[$id]=array (
							"sort_index" => $sort_index,
							"id" =>$id,
							"rows" => $rows,
							"name" => $name,
							"start_time" => $start_time,
							"end_time" => $end_time,
							"link" => $link,
							"add_info" => $add_info,
							"category" => $category
							);
		}
	}
	
	//private
	function createCellAllocation() {
		if (is_array($this->events)) {
			foreach ($this->events as $ms) {
				$m=1;
				$idx_tmp=$ms["sort_index"];
				if ($ms["rows"]>0) {
					for ($m; $m<=$ms["rows"]; $m++) {
						if ($m==1)
							$start_cell=TRUE; 
						else 
							$start_cell=FALSE;
					$this->cell_allocations[$idx_tmp][$ms["id"]] = $start_cell;
					
					//compense php sunday = 0 bullshit
					$day = date("w",$ms["start_time"]);
					if ($day == 0)
						$day = 7;
	
					if ((($idx_tmp % 100) - $day) == 30)
						$idx_tmp=$idx_tmp+70;
					else
						$idx_tmp=$idx_tmp+10;	
					}
				} else
					$this->cell_allocations[$idx_tmp][$ms["id"]] = TRUE;
			}
		}
	}
	
	//private
	function handleOverlaps() {
		
		$i=1;
		for ($i; $i<7; $i++) {
			$n=$this->start_hour;
			for ($n; $n<$this->end_hour+1; $n++) {
				$l=0;
				for ($l; $l<4; $l++) {
					$idx=($n*100)+($l*10)+$i;
					if ($this->cell_allocations[$idx]) 
						if (sizeof($this->cell_allocations[$idx])>0) {
							$rows=0;
							$start_idx=$idx;
							while ($cs = each ($this->cell_allocations [$idx]))
								if ($cs[1])
									if ($this->events[$cs[0]]["rows"]>$rows) $rows=$this->events[$cs[0]]["rows"];
							reset ($this->cell_allocations[$idx]);
							if ($rows>1) {
								$s=2;
								for ($s; $s<=$rows; $s++) {
									$l++;
									if ($l>=4) {
										$l=0; 
										$n++;
									}
									$idx=($n*100)+($l*10)+$i;
									while ($cs = each ($this->cell_allocations[$idx]))
										if ($cs[1]) {
											$this->cell_allocations[$idx][$cs[0]]=FALSE;
											$this->cell_allocations[$start_idx][$cs[0]]=TRUE;
											if ($this->events[$cs[0]]["rows"] > $rows -$s +1)
												$rows=$rows+($this->events[$cs[0]]["rows"]-($rows-$s +1));
										}
										reset ($this->cell_allocations[$idx]);
								}
							}
							$cs = each (array_slice ($this->cell_allocations[$start_idx], 0));
							reset ($this->cell_allocations[$start_idx]);
							$this->events[$cs[0]]["rows"] = $rows;
						}
				}
			}
		}	
	}
	
	//private
	function createHtmlOutput() {
		$glb_colspan=0;
		if ($this->show_days[1]) $glb_colspan++;
		if ($this->show_days[2]) $glb_colspan++;
		if ($this->show_days[3]) $glb_colspan++;
		if ($this->show_days[4]) $glb_colspan++;
		if ($this->show_days[5]) $glb_colspan++;
		if ($this->show_days[6]) $glb_colspan++;
		if ($this->show_days[7]) $glb_colspan++;
		
		?>
		<table <? if ($this->print_view) { ?> bgcolor="#eeeeee" <? } ?> width ="99%" align="center" cellspacing=1 cellpadding=0 border=0>
			<tr>
				<td width="10%" align="center" class="rahmen_steelgraulight" ><?=_("Zeit");?>
				</td>
				<? if ($this->show_days[1]) {?>
				<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Montag");?>
				<? if ($this->show_dates) print "<br /><font size=-1>".date("d.m.y", $this->start_date)."</font>" ?>
				</td><?}
				if ($this->show_days[2]) {?>
				<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Dienstag");?>
				<? if ($this->show_dates) print "<br /><font size=-1>".date("d.m.y", mktime (0,0,0,date("n",$this->start_date), date("j",$this->start_date)+1, date("Y",$this->start_date)))."</font>" ?>
				</td><?}
				if ($this->show_days[3]) {?>
				<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Mittwoch");?>
				<? if ($this->show_dates) print "<br /><font size=-1>".date("d.m.y", mktime (0,0,0,date("n",$this->start_date), date("j",$this->start_date)+2, date("Y",$this->start_date)))."</font>" ?>
				</td><?}
				if ($this->show_days[4]) {?>
				<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Donnerstag");?>
				<? if ($this->show_dates) print "<br /><font size=-1>".date("d.m.y", mktime (0,0,0,date("n",$this->start_date), date("j",$this->start_date)+3, date("Y",$this->start_date)))."</font>" ?>
				</td><?}
				if ($this->show_days[5]) {?>
				<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Freitag");?>
				<? if ($this->show_dates) print "<br /><font size=-1>".date("d.m.y", mktime (0,0,0,date("n",$this->start_date), date("j",$this->start_date)+4, date("Y",$this->start_date)))."</font>" ?>
				</td><?}
				if ($this->show_days[6]) {?>
				<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Samstag");?>
				<? if ($this->show_dates) print "<br /><font size=-1>".date("d.m.y", mktime (0,0,0,date("n",$this->start_date), date("j",$this->start_date)+5, date("Y",$this->start_date)))."</font>" ?>
				</td><?}
				if ($this->show_days[7]) {?>
				<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Sonntag");?>
				<? if ($this->show_dates) print "<br /><font size=-1>".date("d.m.y", mktime (0,0,0,date("n",$this->start_date), date("j",$this->start_date)+6, date("Y",$this->start_date)))."</font>" ?>
				</td><?}?>
			</tr>
		<?


		//Aufbauen der eigentlichen Tabelle
		$i=$this->start_hour;

		for ($i; $i < ($this->end_hour+1); $i++) {
			$k=0;
			for ($k; $k<4; $k++) {
				if ($k==0)  {
					echo "<tr><td align=\"center\" class=\"rahmen_steelgraulight\" rowspan=4>"; 
					if ($i<10) echo "0";
					echo $i, ":00 "._("Uhr")."</td>";
				}
				else echo "<tr>";
				$l=1;
				for ($l; $l<8; $l++) {
					//ausgeblendete Tage skippen
					if (($l==1) && (!$this->show_days[1])) $l=2;
					if (($l==2) && (!$this->show_days[2])) $l=3;
					if (($l==3) && (!$this->show_days[3])) $l=4;
					if (($l==4) && (!$this->show_days[4])) $l=5;
					if (($l==5) && (!$this->show_days[5])) $l=6;
					if (($l==6) && (!$this->show_days[6])) $l=7;
					if (($l==7) && (!$this->show_days[7])) $l=8;
		
					$idx=($i*100)+($k*10)+$l;
					unset($cell_content);
					$m=0;
					if ($this->cell_allocations[$idx])
						while ($cs = each ($this->cell_allocations [$idx]))
							$cell_content[]=array("id"=>$cs[0], "start_cell"=>$cs[1]);
					if ((!$this->cell_allocations[$idx]) || ($cell_content[0]["start_cell"]))	
						echo "<td ";
					$u=0;
					if (($this->cell_allocations[$idx]) && ($cell_content[0]["start_cell"])) {
						$r=0;
						foreach ($cell_content as $cc) {
							if ($r==0) {
								printf ("style=\"vertical-align:top; font-size:10px; color:#FFFFFF; background-image:url(%s); border-style:solid; border-width:1px; border-color:%s;\" valign=\"top\" rowspan=\"%s\" >",
									$this->categories[$this->events[$cell_content[0]["id"]]["category"]]["bg-picture"], $this->categories[$this->events[$cell_content[0]["id"]]["category"]]["border-color"], $this->events[$cell_content[0]["id"]]["rows"]);
								echo "<table width=\"100%\" cellspacing=0 cellpadding=0 border=0><tr>";
							} else
								echo "</td></tr><tr>";
							printf ("<td style=\"vertical-align:top; font-size:10px; height:15px; color:#FFFFFF; background-image:url(%s); border-style:solid; border-width:1px; border-color:%s;\" >", 
								$this->categories[$this->events[$cell_content[0]["id"]]["category"]]["bg-picture"], $this->categories[$this->events[$cell_content[0]["id"]]["category"]]["border-color"]);
							if (($print_view) && ($r!=0))
								echo "<hr width=\"100%\">";
							$r++;
							printf ("<div style=\"font-size:10px; height:15px; background-color:%s; ",
								$this->categories[$this->events[$cell_content[0]["id"]]["category"]]["border-color"]);
							if (!$print_view)
								echo "color:#FFFFFF;";
							echo " \">".date ("H:i",  $this->events[$cc["id"]]["start_time"]);
							if  ($this->events[$cc["id"]]["start_time"] <> $this->events[$cc["id"]]["end_time"]) 
								echo " - ",  date ("H:i",  $this->events[$cc["id"]]["end_time"]);
							//if ($this->events[$cc["id"]]["ort"]) echo ",  ", $this->events[$cc["id"]]["ort"];
							echo "</div>";
							echo "</td></tr><tr><td>";
								echo  "<a style=\"color: #FFFFFF;font-size:10px;\" href=\"".$this->events[$cc["id"]]["link"]."\"><font size=-1>";
								echo htmlReady(substr($this->events[$cc["id"]]["name"], 0,50));
								if (strlen($this->events[$cc["id"]]["name"])>50)
									echo "..."; 
								echo"</font></a>";
							//if ($this->events[$cc["id"]]["dozenten"]) echo "<br><div align=\"right\"><font size=-1>", $this->events[$cc["id"]]["dozenten"], "</font></div>";
							//if ($this->events[$cc["id"]]["personal_sem"]) echo "<div align=\"right\"><a href=\"",$PHP_SELF, "?cmd=delete&d_sem_id=",$this->events[$cc["id"]]["id"], "\"><img border=0 src=\"./pictures/trash.gif\" alt=\"Dieses Feld aus der Auswahl l&ouml;schen\">&nbsp;</a></div>";
						}
						echo "</td></tr></table></td>";
					}
					if (!$this->cell_allocations[$idx]) {
						if (($k == 3) && ($this->add_link)) {
							$add_link_timestamp = $this->base_date + (($l-1) * 24 * 60 * 60) + ($i * 60 * 60);
							echo sprintf ("class=\"steel1\" align=\"right\" valign=\"bottom\"><a href=\"%s%s\"><img src=\"pictures/calplus.gif\" %s border=\"0\"/></a></td>", 
									$this->add_link, $add_link_timestamp, tooltip(sprintf(_("Eine neue Belegung von %s bis %s Uhr anlegen"), date ("H:i", $add_link_timestamp), date ("H:i", $add_link_timestamp + (60 * 60)))));
						} else
							echo "class=\"steel1\" align=\"right\"></td>"; 
					}
				}
				echo "</tr>\n";
			}
		}

		if ($print_view) {
			echo "<tr><td colspan=$glb_colspan><i><font size=-1>&nbsp; "._("Erstellt am")." ",date("d.m.y", time())," um ", date("G:i", time())," Uhr.</font></i></td><td align=\"right\"><font size=-2><img src=\"pictures/logo2b.gif\"><br />&copy; ", date("Y", time())," v.$SOFTWARE_VERSION&nbsp; &nbsp; </font></td></tr></tr>";
		} else {;
			//print view bottom
		}
		?>
			</td>
		</tr>
	</table>
	<?
	}
	
	function showSchedule($mode="html") {
		$this->createCellAllocation();
		$this->handleOverlaps();
		switch ($mode) {
			case "html":
			default:
				$this-> createHtmlOutput();
		}
	}
}
?>
<?
/**
* ShowSchedules.class.php
* 
* view schedule/assigns for a ressource-object
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		ShowSchedules.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ShowSchedule.class.php
// stellt Assign/graphische Uebersicht der Belegungen dar
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

require_once ($RELATIVE_PATH_RESOURCES."/lib/AssignEventList.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/views/ScheduleWeek.class.php");
require_once ($ABSOLUTE_PATH_STUDIP."/cssClassSwitcher.inc.php");

$cssSw = new cssClassSwitcher;

/*****************************************************************************
ShowSchedules - schedule view
/*****************************************************************************/

class ShowSchedules {
	var $ressource_id;		//viewed ressource object
	var $user_id;			//viewed user
	var $range_id;			//viewed range
	var $start_time;		//time to start
	var $end_time;			//time to end
	var $length_factor;		//the used length factor for calculations, only used for viewing
	var $length_unit;		//the used length unit for calculations, only used for viewing
	var $week_offset;		//offset for the week view
	var $used_view;		//the used view, submitted to the sub classes
		
	//Konstruktor
	function ShowSchedules ($resource_id='', $user_id='', $range_id='') {
		$this->db=new DB_Seminar;
		$this->db2=new DB_Seminar;
		$this->resource_id=$resource_id;
		$this->user_id=$user_id;
		$this->range_id=$range_id;
	}
	
	function setLengthFactor ($value) {
		$this->length_factor = $value;
	}	
	
	function setLengthUnit ($value) {
		$this->length_unit = $value;
	}
	
	function setStartTime ($value) {
		$this->start_time = $value;
	}
	
	function setEndTime ($value) {
		$this->end_time = $value;
	}
	
	function setWeekOffset ($value) {
		$this->week_offset = $value;
	}
	
	function setUsedView($value) {
		$this->used_view = $value;
	}
	
	function navigator () {
		global $cssSw, $view_mode;
		
	 	//match start_time & end_time for a whole week
	 	$dow = date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) >1)
	 		$offset = 1 - date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) <1)
		 	$offset = -6;
	 
 		$start_time = mktime (0, 0, 0, date("n",$this->start_time), date("j", $this->start_time)+$offset+($this->week_offset*7), date("Y", $this->start_time));
 		$end_time = mktime (23, 59, 0, date("n",$start_time), date("j", $start_time)+6, date("Y", $start_time));
	
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?navigate=TRUE&quick_view=view_schedule&quick_view_mode=<?=$view_mode?>">
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan="2"><font size=-1><b><?=_("Zeitraum:")?></b></font>
				</td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%" rowspan="2">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="30%" rowspan="2" valign="top"><font size=-1>
					<font size=-1>Beginn:&nbsp; 
					<input type="text" name="schedule_begin_day" size=2 maxlength=2 value="<? if (!$start_time) echo date("d",time()); else echo date("d",$start_time); ?>">.
					<input type="text" name="schedule_begin_month" size=2 maxlength=2 value="<? if (!$start_time) echo date("m",time()); else echo date("m",$start_time); ?>">.
					<input type="text" name="schedule_begin_year" size=4 maxlength=4 value="<? if (!$start_time) echo date("Y",time()); else echo date("Y",$start_time); ?>"><br /> 
					&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; <input type="IMAGE" name="jump" border="0"<? echo makeButton("auswaehlen", "src") ?> /><br />
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="66%" valign="top"><font size=-1>
					<input type="text" name="schedule_length_factor" size=2 maxlength=2 / value="<? if (!$this->length_factor) echo "1"; else echo $this->length_factor; ?>">
					&nbsp; <select name="schedule_length_unit">
						<option <? if ($this->length_unit  == "d") echo "selected" ?> value="d"><?=_("Tag(e)")?></option>
						<option <? if ($this->length_unit  == "w") echo "selected" ?> value="w"><?=_("Woche(n)")?></option>
						<option <? if ($this->length_unit  == "m") echo "selected" ?> value="m"><?=_("Monat(e)")?></option>
						<option <? if ($this->length_unit  == "y") echo "selected" ?> value="y"><?=_("Jahre(e)")?></option>
					</select>&nbsp; als Liste
					&nbsp; <input type="IMAGE" name="start_list" <?=makeButton("ausgeben", "src") ?> border=0 vallue="<?=_("ausgeben")?>" /><br />
				</td>
			</tr>
			<tr>
					<td class="<? echo $cssSw->getClass() ?>" width="66%" valign="top"><font size=-1>
					<i>oder</i>&nbsp;  eine Woche grafisch
					&nbsp; <input type="IMAGE" name="start_graphical" <?=makeButton("ausgeben", "src") ?> border=0 vallue="<?=_("ausgeben")?>" /><br />&nbsp; 
				</td>
			</tr>
			</form>
		</table>
	<?
	}

	function showScheduleList() {
		global $PHP_SELF, $cssSw, $view_mode;

		 //select view to jump from the schedule
		 if ($this->used_view == "openobject_schedule")
		 	$view = "openobject_assign";
		 else
			$view = "edit_object_assign";

		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" align="center"><br />
					<? echo "<b>Anzeige vom ", date ("d.m.Y", $this->start_time), " bis ", date ("d.m.Y", $this->end_time)."</b><br />";?>
					<br />
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%">
					<?				
					$assign_events=new AssignEventList ($this->start_time, $this->end_time, $this->resource_id, '', '', TRUE);
					echo "<br /><font size=-1>"._("Anzahl der Belegungen in diesem Zeitraum:")." ", $assign_events->numberOfEvents()."</font>";
					echo "<br /><br />";
					while ($event=$assign_events->nextEvent()) {
						echo "<a href=\"$PHP_SELF?quick_view=".$view."&quick_view_mode=".$quick_view_mode."&edit_assign_object=".$event->getAssignId()."\">".makeButton("eigenschaften")."</a>";
						printf ("&nbsp; <font size=-1>"._("Belegung ist von <b>%s</b> bis <b>%s</b>, belegt von <b>%s</b>")."</font><br />", date("d.m.Y H:i", $event->getBegin()), date("d.m.Y H:i", $event->getEnd()), $event->getName());
					}
					?>
				</td>
			</tr>
		</table>
		<br /><br />
	<?
	}
	
	function showScheduleGraphical() {
		global $RELATIVE_PATH_RESOURCES, $PHP_SELF, $cssSw, $view_mode;
	 	
	 	//match start_time & end_time for a whole week
	 	$dow = date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) >1)
	 		$offset = 1 - date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) <1)
		 	$offset = -6;

		 //select view to jump from the schedule
		 if ($this->used_view == "openobject_schedule")
		 	$view = "openobject_assign";
		 else
			$view = "edit_object_assign";
		 
 		$start_time = mktime (0, 0, 0, date("n",$this->start_time), date("j", $this->start_time)+$offset+($this->week_offset*7), date("Y", $this->start_time));
 		$end_time = mktime (23, 59, 59, date("n",$start_time), date("j", $start_time)+6, date("Y", $start_time));

	 	$schedule=new ScheduleWeek(FALSE, FALSE, FALSE, TRUE, $start_time) ;
		
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">
					<img src="pictures/blank.gif" height="35" border="0"/>					
				</td>
				<td class="<? echo $cssSw->getClass() ?>"  width="10%" align="left">&nbsp;
					<a href="<? echo $PHP_SELF ?>?quick_view=<?=$this->used_view?>&quick_view_mode=<?=$view_mode?>&previous_week=TRUE"><img src="pictures/calendar_previous.gif" <? echo tooltip (_("Vorherige Woche anzeigen")) ?>border="0" /></a>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="76%" align="center">
					<? echo "<b>Anzeige der Woche vom ", date ("d.m.Y", $start_time), " bis ", date ("d.m.Y", $end_time)."</b> (".strftime("%V", $start_time).". "._("Woche").")";?>
					<br />
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="10%" align="center">&nbsp;
					<a href="<? echo $PHP_SELF ?>?quick_view=<?=$this->used_view?>&quick_view_mode=<?=$view_mode?>&next_week=TRUE"><img  valign="middle"  src="pictures/calendar_next.gif" <? echo tooltip (_("Nächste Woche anzeigen")) ?>border="0" /></a>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan="3">
					<?						
					$assign_events=new AssignEventList ($start_time, $end_time, $this->resource_id, '', '', TRUE);
					echo "<br /><font size=-1>"._("Anzahl der Belegungen in diesem Zeitraum:")." ", $assign_events->numberOfEvents()."</font>";
					echo "<br />&nbsp; ";
					while ($event=$assign_events->nextEvent()) {
						$schedule->addEvent($event->getName(), $event->getBegin(), $event->getEnd(), 
											"$PHP_SELF?quick_view=$view&quick_view_mode=".$view_mode."&edit_assign_object=".$event->getAssignId());
					}
					$schedule->showSchedule("html");
					echo "<br />&nbsp; ";
					?>
				</td>
			</tr>
		</table>
	<?
	}
}

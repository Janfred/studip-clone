<?



/*

calendar_settings.inc 0.8-20020701

Persoenlicher Terminkalender in Stud.IP.

Copyright (C) 2001 Peter Thienel <pthien@gmx.de>



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



// User-Variablen initialisieren

//if(!isset($calendar_user_control_data)){

//	$user->register("calendar_user_control_data");

//}



require_once("visual.inc.php");



/*// store default-values

if(empty($calendar_user_control_data["view"])){

	$calendar_user_control_data = array(

		"view"           => "showweek",

		"start"          => 9,

		"end"            => 20,

		"step_day"       => 900,

		"step_week"      => 3600,

		"type_week"      => "LONG",

		"holidays"       => TRUE,

		"sem_data"       => TRUE,

		"link_edit"      => FALSE

	);

}*/



// store user-settings

if($cmd_cal == "chng_cal_settings"){

	$calendar_user_control_data = array(

		"view"           => $cal_view,

		"start"          => $cal_start,

		"end"            => $cal_end,

		"step_day"       => $cal_step_day,

		"step_week"      => $cal_step_week,

		"type_week"      => $cal_type_week,

		"holidays"       => $cal_holidays,

		"sem_data"       => $cal_sem_data,

		"link_edit"      => $cal_link_edit

	);

}



$css_switcher = new cssClassSwitcher();



// print out form

?>

<table width="100%" cellspacing="0" cellpadding="0" border="0">

	<tr>

		<td class="topic">&nbsp;<img src="pictures/meinetermine.gif" border="0" align="absmiddle" alt=""><b>&nbsp;Einstellungen f&uuml;r meinen Terminkalender anpassen</b>

		</td>

	</tr>

	<tr>

		<td class="blank">&nbsp;

			<blockquote>

			Hier k&ouml;nnen Sie die Ansicht Ihres pers&ouml;nlichen Terminkalenders anpassen.

			<br>

			</blockquote>

			<table width="99%" border="0" cellpadding="0" cellspacing="0" border="0" align="center">

	<tr>

		<td class="blank">

			<form method="post" action="<? echo $PHP_SELF ?>?cmd_cal=chng_cal_settings">

			<table width ="100%" cellspacing="0" cellpadding="2" border="0">

				<tr>

					<td class="<? echo $css_switcher->getClass(); ?>" width="10%">

						<blockquote><p><b>Startansicht einstellen:</b></p></blockquote>

					</td>

					<td class="<? echo $css_switcher->getClass(); ?>" width="90%">

						<select name="cal_view" size="1">

							<option value="showweek"<? if($calendar_user_control_data["view"] == "showweek") echo " selected"; ?>>Wochenansicht</option>

							<option value="showday"<? if($calendar_user_control_data["view"] == "showday") echo " selected"; ?>>Tagesansicht</option>

							<option value="showmonth"<? if($calendar_user_control_data["view"] == "showmonth") echo " selected"; ?>>Monatsansicht</option>

							<option value="showyear"<? if($calendar_user_control_data["view"] == "showyear") echo " selected"; ?>>Jahresansicht</option>

						</select>

					</td>

				</tr>

				<tr><? $css_switcher->switchClass(); ?>

				<td class="<? echo $css_switcher->getClass(); ?>">

					<blockquote><p><b>Zeitraum der Tages- und Wochensansicht:</b></p></blockquote>

				</td>

				<td class="<? echo $css_switcher->getClass(); ?>">

					<?	    

			   		echo"<select name=\"cal_start\">";

	   					for ($i=0; $i<=23; $i++)

		  					{

					  		if ($i==$calendar_user_control_data["start"]) 

					  			{

					  			echo "<option selected value=".$i.">";

					  			if ($i<10)  echo "0".$i.":00";

					  			else echo $i.":00";

					  			echo "</option>";

					  			}

		       					else 

		       						{

					  			echo "<option value=".$i.">";

					  			if ($i<10)  echo "0".$i.":00";

					  			else echo $i.":00";

					  			echo "</option>";

					  			}

		  					}

			    		echo"</select>";

					?>

					&nbsp;Uhr bis

					<?	    

			   		echo"<select name=\"cal_end\">";

	   					for ($i=0; $i<=23; $i++)

		  					{

					  		if ($i==$calendar_user_control_data["end"]) 

					  			{

					  			echo "<option selected value=".$i.">";

					  			if ($i<10)  echo "0".$i.":00";

					  			else echo $i.":00";

					  			echo "</option>";

					  			}

		       					else 

		       						{

					  			echo "<option value=".$i.">";

					  			if ($i<10)  echo "0".$i.":00";

					  			else echo $i.":00";

					  			echo "</option>";

					  			}

		  					}

			    		echo"</select>";

					?>

					&nbsp;Uhr.

					</td>

				</tr>

				<tr><? $css_switcher->switchClass(); ?>

					<td class="<? echo $css_switcher->getClass(); ?>">

						<blockquote><p><b>Zeitintervall der Tagesansicht:</b></p></blockquote>

					</td>

					<td class="<? echo $css_switcher->getClass(); ?>">

						<select name="cal_step_day" size="1">

							<option value="600"<? if($calendar_user_control_data["step_day"] == 600) echo " selected"; ?>>10 Minuten</option>

							<option value="900"<? if($calendar_user_control_data["step_day"] == 900) echo " selected"; ?>>15 Minuten</option>

							<option value="1800"<? if($calendar_user_control_data["step_day"] == 1800) echo " selected"; ?>>30 Minuten</option>

							<option value="3600"<? if($calendar_user_control_data["step_day"] == 3600) echo " selected"; ?>>1 Stunde</option>

							<option value="7200"<? if($calendar_user_control_data["step_day"] == 7200) echo " selected"; ?>>2 Stunden</option>

						</select>

					</td>

				</tr>

				<tr><? $css_switcher->switchClass(); ?>

					<td class="<? echo $css_switcher->getClass(); ?>">

						<blockquote><p><b>Zeitintervall der Wochenansicht:</b></p></blockquote>

					</td>

					<td class="<? echo $css_switcher->getClass(); ?>">

						<select name="cal_step_week" size="1">

							<option value="1800"<? if($calendar_user_control_data["step_week"] == 1800) echo " selected"; ?>>30 Minuten</option>

							<option value="3600"<? if($calendar_user_control_data["step_week"] == 3600) echo " selected"; ?>>1 Stunde</option>

							<option value="7200"<? if($calendar_user_control_data["step_week"] == 7200) echo " selected"; ?>>2 Stunden</option>

						</select>

					</td>

				</tr>

				<tr><? $css_switcher->switchClass(); ?>

					<td class="<? echo $css_switcher->getClass(); ?>">

						<blockquote><p><b>Wochenansicht definieren:</b></p></blockquote>

					</td>

					<td class="<? echo $css_switcher->getClass(); ?>">

						<input type="radio" name="cal_type_week" value="LONG"<? if($calendar_user_control_data["type_week"] == "LONG") echo " checked"; ?>>&nbsp;7 Tage-Woche<br>

						<input type="radio" name="cal_type_week" value="SHORT"<? if($calendar_user_control_data["type_week"] == "SHORT") echo " checked"; ?>>&nbsp;5 Tage-Woche

					</td>

				</tr>

				<tr><? $css_switcher->switchClass(); ?>

					<td class="<? echo $css_switcher->getClass(); ?>">

						<blockquote><p><b>Feiertage/Semesterdaten:</b></p></blockquote>

					</td>

					<td class="<? echo $css_switcher->getClass(); ?>">

						<input type="checkbox" name="cal_holidays" value="TRUE"<? if($calendar_user_control_data["holidays"]) echo " checked"; ?>>&nbsp;Feiertage anzeigen<br>

						<input type="checkbox" name="cal_sem_data" value="5"<? if($calendar_user_control_data["sem_data"]) echo " checked"; ?>>&nbsp;Semesterdaten anzeigen

					</td>

				</tr>

				<tr><? $css_switcher->switchClass(); ?>

					<td class="<? echo $css_switcher->getClass(); ?>">

						<blockquote><p><b>Komfortfunktionen</b></p></blockquote>

					</td>

					<td class="<? echo $css_switcher->getClass(); ?>">

						<input type="checkbox" name="cal_link_edit" value="TRUE"<? if($calendar_user_control_data["link_edit"]) echo " checked"; ?>>&nbsp;Bearbeiten-Link in Wochenansicht

					</td>

				</tr>

				<tr><? $css_switcher->switchClass(); ?>

					<td class="<? echo $css_switcher->getClass(); ?>">&nbsp;

					</td>

					<td class="<? echo $css_switcher->getClass(); ?>">

					<?

						// sorgt fuer Ruecksprung in letzte Ansicht in kalender.php

						if(substr(strrchr($PHP_SELF, "/"), 1) == "calendar.php" && !empty($calendar_sess_control_data["view_prv"]))

							echo '<input type="hidden" name="cmd" value="'.$calendar_sess_control_data["view_prv"].'">';

						if($atime)

							echo '<input type="hidden" name="atime" value="'.$atime.'">';

					?>

						<input type="hidden" name="view" value="calendar">

						<p><br /><input type="IMAGE" src="pictures/buttons/uebernehmen-button.gif" border=0 value="&Auml;nderungen &uuml;bernehmen"></font>&nbsp; </p>

					</td>

				</tr>

			</table>

			<br />

		</td>

	</tr>

</table>

		</form>
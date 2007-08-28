<?
/*
blockveranstaltungs_assistent.inc.php - Terminverwaltung von Stud.IP

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

/*  Diese funktion bekommt als eingabe die seminar_id und die Daten, die aus der
		Form kommen. Das sind:
									'start_day'
									'start_month'
									'start_year'
									'end_day'
									'end_month'
									'end_year'
									'start_hour'
									'start_minute'
									'end_hour'
									'end_minute'
									'create_topic'
									'create_folder'

		Wenn Verzeichnisse oder Themen im Forum angelegt werden sollen, wird das mit den
		beiden anderen Parametern gesteuert.

		Funktion liefert messages oder errors...
*/

function day_checked($day) {
	global $_POST;
	if (isset($_POST['days'])) {
		foreach ($_POST['days'] as $cur_day) {
			if ($cur_day == $day) return ' checked=checked';
		}
	}
	return '';
}

function create_block_schedule_dates($seminar_id, $form_data)
{

$messages =  Array('seminar_id'=> _("Kein Seminar gewählt!"),
									 'start_day' => _("Startdatum: kein Tag angegeben."),
									 'start_month'=> _("Startdatum: kein Monat angegeben."),
									 'start_year' => _("Startdatum: kein Jahr angegeben."),
									 'end_day' => _("Enddatum: kein Tag angegeben."),
									 'end_month'=> _("Enddatum: kein Monat angegeben."),
									 'end_year' => _("Enddatum: kein Jahr angegeben."),
									'start_hour' => _("Startzeitpunkt: keine Stunde angegeben."),
									'start_minute'=> _("Startzeitpunkt: keine Minuten angegeben.") ,
									'end_hour'=> _("Endzeitpunkt: keine Stunde angegeben."),
									'end_minute'=> _("Endzeitpunkt: keine Minuten angegeben."),
									'no_days_in_timeslot' => _("Keiner der ausgewählten Tage liegt in dem angegebenen Zeitraum!"));

	// do checks
	foreach($form_data as $key=>$value)
	{
		// check if form was filled
		if(in_array($key, array_keys($messages))
				&& $value==null)
		{
			$errors[] = $messages[$key];
		}
	}


	// done checks, if $error is filled, an error occurred
	if ($errors != null) {
		return array('ready' => false, 'errors' => $errors);
	}

	if ($form_data["block_submit_x"] && $errors==null)
	{ /// create the schedule dates
		$start_time = mktime($form_data["start_hour"],
				$form_data["start_minute"],
				0,
				$form_data["start_month"],
				$form_data["start_day"],
				$form_data["start_year"]);

		$end_time = mktime($form_data["end_hour"],
				$form_data["end_minute"],
				0,
				$form_data["start_month"],
				$form_data["start_day"],
				$form_data["start_year"]);

		if ($start_time == -1 || $end_time ==-1)
		{
			$errors[] = "Startdatum: fehlerhafte Zeitangabe";
		} else
		{
			if ($start_time==$end_time)
			{
				$errors[] = "Start- und Endzeitpunkt sind gleich!";
			} else if ($start_time>$end_time)
			{
				$errors[] = "Startzeitpunkt liegt vor Endzeitpunkt!";
			}
		}

		$absolute_end_time = mktime($form_data["end_hour"],
				$form_data["end_minute"],
				0,
				$form_data["end_month"],
				$form_data["end_day"],
				$form_data["end_year"]);

		if ($start_time > $absolute_end_time)
		{
			$errors[] = "Startdatum liegt vor Enddatum!";
		}


		if ($end_time == -1)
		{
			$errors[] = "Enddatum: fehlerhafte Zeitangabe";
		}

		if (sizeof($errors)==0)
		{
			$delta_time = $end_time - $start_time;

			$tmp_start_day_nr = date("w", $start_time);

			$tmp_start_time = $start_time;

			/// reset day index
			$day_counter = 0;

			// real starting time
			$start_time = strtotime("+".$day_counter." days",$start_time);
			$tmp_start_time = $start_time;
			// real end time
			$tmp_end_time = $tmp_start_time+$delta_time;

			if (!is_array($form_data['days'])) {
				$form_data['days'] = array();
			}

			$inserted = 0;
			// generate the schedule dates
			while($tmp_end_time <= $absolute_end_time)
			{
				if(in_array(date("l",$tmp_start_time),$form_data["days"]) || $form_data["every_day"]=='1')
				{
					$schedule_dates[] = Array("start_time"=> $tmp_start_time, "end_time"=>$tmp_end_time,"astext"=> date("d.m.Y ",$tmp_start_time));
					$inserted ++;
				}
				$tmp_start_time = strtotime("+1 day "." ".$form_data["start_hour"].":".$form_data["start_minute"], $tmp_start_time);
				$tmp_end_time = strtotime("+1 day ".$form_data["end_hour"].":".$form_data["end_minute"], $tmp_end_time);
			}
		}
	}

	if (!isset($schedule_dates)) {
		$errors[] = $messages['no_days_in_timeslot'];
	}

	//echo "<pre>".print_r($GLOBALS,true)."<pre>";;
	if (!isset($form_data["block_submit_x"]) || $errors != null)
	{ // show the form
		return array('ready' => false, 'errors' => $errors);
	} else
	{
		// create data needed for schedule dates
		$name_common = "";
		$description_common = "Hier kann zu diesem Termin diskutiert werden";
		$folder_description = 'Ablage für Ordner und Dokumente zu diesem Termin';
		$range_id = "$seminar_id";
		$autor_id = $user->id;
		$mkdate = $chdate = time();
		$messages = Array();
		// step through dates and insert into db
		$db = new DB_Seminar;
		foreach($schedule_dates as $date)
		{
			$start_time = $date["start_time"];
			$end_time = $date["end_time"];
			$name = "Kein Titel";
			$topic_name = $folder_name = "Sitzung: $name am: ".$date["astext"];
			$user_id = $GLOBALS["user"]->id;
			$user_host = $GLOBALS["REMOTE_ADDR"];
			$user_fullname = get_fullname($user_id);
			$date_id = md5(uniqid(rand(),true));
			$topic_id=null;

			$query = "INSERT INTO termine (termin_id, range_id, autor_id, content, description, date, end_time, mkdate, chdate, date_typ, topic_id, raum)"
						. " VALUES ('$date_id', '$seminar_id', '$user_id', '$name', '', '$start_time', '$end_time', '$mkdate', '$chdate', '1', '$topic_id', NULL)";
			$db->query($query);
			// status messages
			$status[] = $date["astext"];
		}
		//echo "message".print_r($messages,true)."<br>";
		return array('ready' => true, 'status' => $status);
	}
}

?>

	<table width="100%" border="0" cellpadding="5" cellspacing="0">
		<tr><td class="blank" width="100%">
			<table border="0" width="100%" cellspacing="0" cellpadding="0" class="blank">
				<form action="<? echo $PHP_SELF; ?>?cmd=<? if(!empty($calendar_sess_control_data["view_prv"])) echo $calendar_sess_control_data["view_prv"]; else echo "showweek"; ?>" method="post">
				<tr>
					<th width="2%"><a href="gruppe.php"><img src='pictures/gruppe.gif' alt='Gruppe &auml;ndern' border=0></a></th>
					<th width="63%"><a href="<? echo $PHP_SELF ?>?cmd=bind&sortby=Name&order=<? echo $order; ?>">Name</a></th>
					<th width="7%"><a href="<? echo $PHP_SELF ?>?cmd=bind&sortby=count&order=<? echo $order; ?>">Termine</a></th>
					<th width="13%"><b>besucht</b></th>
					<th width="13%"><a href="<? echo $PHP_SELF ?>?cmd=bind&sortby=status&order=<? echo $order; ?>">Status</a></th>
					<th width="2%">&nbsp;</th>
				</tr>
	<?
		$css_switcher = new cssClassSwitcher();
		$css_switcher->switchClass();
		
		while($db->next_record()){
			$style = $css_switcher->getClass();
			printf("<tr><td class=\"gruppe%s\"><img src=\"pictures/blank.gif\" alt=\"Gruppe\" border=\"0\" width=\"15\" height=\"12\"></td>\n", $db->f("gruppe"));
			printf("<td class=\"%s\">&nbsp;&nbsp;%s</td>\n", $style, format(htmlReady(mila($db->f("Name")))));
			printf("<td class=\"%s\" align=\"center\">%s</td>\n", $style, $db->f("count"));
			if($loginfilenow[$db->f("Seminar_id")] == 0)
				printf("<td class=\"%s\" align=\"center\">nicht besucht</td>\n", $style);
			else
				printf("<td class=\"%s\" align=\"center\">%s</td>", $style, date("d.m.Y", $loginfilenow[$db->f("Seminar_id")]));
			printf("<td class=\"%s\" align=\"center\">%s</td>\n", $style, $db->f("status"));
			if($calendar_user_control_data["bind_seminare"][$db->f("Seminar_id")])
				$is_checked = " checked";
			else
				$is_checked = "";
			printf("<td class=\"%s\"><input type=\"checkbox\" name=\"sem[%s]\" value=\"TRUE\"%s></tr>\n", $style, $db->f("Seminar_id"), $is_checked);
			$css_switcher->switchClass();
		}
		echo "<tr><td class=\"blank\">&nbsp;</td></tr>\n";
		echo '<tr><td class="blank" colspan="6" align="center">&nbsp;<input type="image" src="./pictures/buttons/auswaehlen-button.gif" border="0"></td></tr>';
		// Dummy-Wert damit $sem auch ohne ausgewaehlte Seminare ausgewertet wird
		echo "\n<input type=\"hidden\" name=\"sem[1]\" value=\"FALSE\">\n";
		printf('<input type="hidden" name="atime" value="%s">', $atime);
		echo "\n</form>\n";
		echo "</table>";
		echo "\n</td></tr><tr><td class=\"blank\">&nbsp;";
?>

<?php
if ($msg_autor_id == "____%system%____"){
	echo"\n<table width=\"100%\" border=0 cellpadding=2 cellspacing=0><tr><td>" .
		"<font size=-1><b>"
		. _("automatisch erzeugte Systemnachricht:") . " </b><hr>".formatReady($msg_text)."</font></td></tr></table>";
} else {
	echo"\n<table width=\"100%\" border=0 cellpadding=2 cellspacing=0>" .
		"<tr><td class='blank' colspan='2' valign='middle'><font size=-1>"
		.sprintf(_("Nachricht von: <b>%s</b>"),get_fullname_from_uname($msg_snd,'full',true))
		."<hr>".formatReady($msg_text)."</font></td></tr>";
		echo"\n<tr><td class='blank' colspan='2' valign='middle' align='center'><font size=-1>"
		. "<a href='Javascript: studipim.write_to(\"$msg_snd\", \"".get_fullname_from_uname($msg_snd,'full',true)."\", \"".(substr($msg_subject, 0, 3) != "RE:" ? "RE: ".$msg_subject  :  $msg_subject )."\", \"\")'><img " . makeButton("antworten","src") . tooltip(_("Diese Nachricht direkt beantworten")) . " border=0></a>"
		. "&nbsp;<a href='Javascript: studipim.write_to(\"$msg_snd\", \"".get_fullname_from_uname($msg_snd,'full',true)."\", \"".(substr($msg_subject, 0, 3) != "RE:" ? "RE: ".$msg_subject  :  $msg_subject )."\", \"".$sms_reply_text."\")'><img " . makeButton("zitieren","src") . tooltip(_("Diese Nachricht direkt beantworten")) . " border=0></a>"
		. "&nbsp;<a href='Javascript: studipim.cleanup()'><img " . makeButton("abbrechen","src") . tooltip(_("Vorgang abbrechen")) . " border=0></a>" .
			"</td></tr></table>";
}
?>
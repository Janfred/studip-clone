<?
		$subject = "Neuer Administrator in Ihrer Einrichtung angelegt";
		
		$mailbody = sprintf("%s %s %s,\n\n"
		."In der Einrichtung '%s' wurde %s %s %s als Administrator eingetragen und steht Ihnen als neuer Ansprechpartner bei Fragen oder Problemen im StudIP zur Verfügung. ",($db->f('geschlecht') == 0)?"Lieber Herr":"Liebe Frau",$db->f('Vorname'),$db->f('Nachname'),htmlReady($inst_name),($UserManagement->user_data['user_info.geschlecht']==0)?"Herr":"Frau",$UserManagement->user_data['auth_user_md5.Vorname'],$UserManagement->user_data['auth_user_md5.Nachname']);
		
?>

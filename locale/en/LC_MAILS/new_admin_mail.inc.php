<?
		$subject = "New administrator in your institution";
		
		$mailbody = sprintf("Dear %s %s,\n\n"
		."%s %s %s has been registered as an administrator in the institution '%s' and will support you from now on in handling with StudIP.",$db->f('Vorname'),$db->f('Nachname'),($UserManagement->user_data['user_info.geschlecht']==0)?"Mr.":"Mrs.",$UserManagement->user_data['auth_user_md5.Vorname'],$UserManagement->user_data['auth_user_md5.Nachname'],htmlReady($inst_name));
		
?>

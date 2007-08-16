<?
/**
 * Abstract implementation for the user mapping used by StudIPAuthCAS
 * @author Dennis Reil, <Dennis.Reil@offis.de>
 * @version $Revision$
 * $Id$
 */
class CASAbstractUserDataMapping {
	var $data;
   var $conn;
	var $error_msg;
	var $ldapinfo;
	    		
	function CASAbstractUserDataMapping(){
		
	}
	
	// ggf. anzupassen
	function getUserData($key,$username){
		$config = $GLOBALS['CASAbstractUserDataMapping_CONFIG'];
		if (empty($this->ldapinfo)){
			if (!($this->conn = ldap_connect($config["host"], $config["port"]))) {
				$this->error_msg = _("Keine Verbindung zum LDAP Server m?glich.");
				return false;
			}
			if (!($r = ldap_set_option($this->conn,LDAP_OPT_PROTOCOL_VERSION,$config["protocol_version"]))){
				$this->error_msg = _("Setzen der LDAP Protokolversion fehlgeschlagen.");
				return false;
			}
			// Zum LDAP-Server verbinden und den Benutzer suchen	
			if (!($r = @ldap_bind($this->conn, $config["adminuser"], $config["adminpass"]))){
				$this->error_msg =_("Admin-Bind fehlgeschlagen.");
				return false;
			}
			if (!($result = @ldap_search($this->conn, $config["base_dn"],"uid=" . $username))){
				$this->error_msg = _("Durchsuchen des LDAP Baumes fehlgeschlagen.");
				return false;
			}
			if (!ldap_count_entries($this->conn, $result)){
				$this->error_msg = sprintf(_("%s wurde nicht unterhalb von %s gefunden."),"uid=" . $username,$config["base_dn"]);
				return false;
			}
			$this->ldapinfo = @ldap_get_entries($this->conn, $result);										
		}				
		if ($this->ldapinfo["count"] == 1){
			$info = $this->ldapinfo[0];
			switch ($key){
				// daten aus dem ldap
				
				case "givenname":				
					return trim(str_replace($info["sn"][0],'', $info["cn"][0]));
				case "surname":
					return $info["sn"][0];
				case "status":						
				 //alle mitarbeiter die im tree staff sind, bekommen status "dozent"
					if (!(strpos(strtolower($info["dn"]),"staff") === false)){
						return "dozent";
					}
					// alle studies die im tree student sind, bekommen status "tutor"
					else if (!(strpos(strtolower($info["dn"]),"student") === false)){
						return "tutor";
					}
					//alle admins aus dem ldap-tree erhalten status "admin"
					else if (!(strpos(strtolower($info["dn"]),"admin") === false)){							
						return "admin";
					}
					else {
						return "autor";
					}					
				case "email":
					return $info["mail"][0];
			}
		}
		else {
			$error_msg = _("Mehrere Nutzer zu diesem Benutzernamen gefunden.");
			return false;
		}
	}
}
?>
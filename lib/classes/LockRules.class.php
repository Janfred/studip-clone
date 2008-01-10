<?
/**
* LockRules.class.php
* 
* 
*
* @author		Mark Sievers <msievers@uos.de> 
* @version		$Id: LockRules.class.php,v 1.7 2003/11/13 07:56:11 msievers Exp $
* @access		public
* @modulegroup
* @module			
* @package		
*/

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


if (version_compare(PHP_VERSION, '5.2', '<'))
{
  require_once('vendor/phpxmlrpc/xmlrpc.inc');
  require_once('vendor/phpxmlrpc/jsonrpc.inc');
  require_once('vendor/phpxmlrpc/json_extension_api.inc');
}

class LockRules {
	var $db;


	function LockRules() {
		$this->db = new DB_Seminar;
	}

  function getLockText()
  {
    /* return "<font color=\"aaaaaa\" size=\"2\">"._("&nbsp;Feld gesperrt&nbsp;")."<img src=\"".$GLOBALS['ASSETS_URL']."images/info.gif\" ".tooltip(_("Sie dürfen nicht alle Daten dieser Veranstaltung verändern. Diese Sperrung ist von einem/einer AdministratorIn vorgenommen worden."),TRUE,TRUE)."></font>";
    */
    #return '<font size="-1>">&nbsp; ' ."<i>" ._("(Das Feld ist f&uuml;r die Bearbeitung gesperrt und kann nur durch einen Administrator ver&auml;ndert werden.)")."</i>" . "</font>";
    return '';
  }
  
  function output_locked_fielddata($field_data)
  {
    $return =  $field_data ? htmlReady($field_data) 
      : "<font size=\"-1\"><i>". ("k.A.")."</i></font>";
      
    $return .= "<br/>" . $this->getLockText();
    
    return $return;
  }
  
	function getAllLockRules() {
		$i=0;
		$sql = "SELECT * FROM lock_rules";
		if  (!$this->db->query($sql)) {
			echo "Error! query not succeeded";
			return 0;
		}
		if ($this->db->num_rows()==0) {
			return 0;
		}
		while ($this->db->next_record()) {
			$lockdata[$i] = $this->wrapLockRules();
			$i++;
		}		
		return $lockdata;
	
	}

    function getSemLockRule($sem_id) {
        $sql = "SELECT lock_rule FROM seminare Where Seminar_id = '".$sem_id."'";
		if  (!$this->db->query($sql)) {
			echo "Error! query not succeeded";
			return 0;
		}
		if ($this->db->num_rows()==0) {
			return 0;
		}
		$this->db->next_record();
        return $this->getLockRule($this->db->f("lock_rule"));
    }

	function getLockRule($lock_id) {
		$sql = "SELECT * FROM lock_rules WHERE lock_id = '".$lock_id."'";
		if  (!$this->db->query($sql)) {
			echo "Error! query not succeeded";
			return 0;
		}
		if ($this->db->num_rows()==0) {
			return 0;
		}
		$this->db->next_record();
		return $this->wrapLockRules();
	}
	
	function wrapLockRules() {
		$lockdata = array();
		$lockdata["lock_id"]		= $this->db->f("lock_id");
		$lockdata["name"] 			= $this->db->f("name");
		$lockdata["description"]	= $this->db->f("description");

    $lockdata['attributes'] = json_decode($this->db->f("attributes"), true);

		return $lockdata;
	}

	function insertNewLockRule($lockdata) {
		$lock_id = md5(uniqid("Legolas"));
		
		$json_attributes = json_encode($lockdata['attributes']);

		$sql = "INSERT INTO lock_rules (lock_id, name, description, attributes) VALUES ('".$lock_id."', '".$lockdata["name"]."', '".$lockdata["description"]."', '".$json_attributes."')";
		if (!$this->db->query($sql)) {
			echo "Error! insert_query not succeeded";
			return 0;
		}
		return $lock_id;
	}
// update!!!	
	function updateExistingLockRule($lockdata) {
		$json_attributes = json_encode($lockdata['attributes']);
		
   		if (!$this->db->query($query = "UPDATE lock_rules SET ".
    	            "name='".$lockdata["name"]."', ".
					"description='".$lockdata["description"]."', ".
					"attributes='".$json_attributes."' ".
					"WHERE lock_id='".$lockdata["lock_id"]."'")) {
                        return 0;
                    }
    	else return 1;
	}

	function getLockRuleByName($name) {
		$sql = "SELECT lock_id FROM lock_rules WHERE name='".$name."'";
		if  (!$this->db->query($sql)) {
			echo "Error! query not succeeded";
			return 0;
		}
		if ($this->db->num_rows()==0) {
			return 0;
		}
		$this->db->next_record();
		return $this->db->f("lock_id");;
	}

	function deleteLockRule($lock_id) {
		$sql = "DELETE FROM lock_rules WHERE lock_id='".$lock_id."'";
		if (!$this->db->query($sql)) {
			echo "Error! Query not succeeded";
			return 0;
		}
		return 1;
	}

}


?>

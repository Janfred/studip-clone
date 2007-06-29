<?
page_open(array('sess' => 'Seminar_Session', 'auth' => 'Seminar_Default_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User'));

if (!array_key_exists('ajax_cmd', $_REQUEST)) die;


switch($_REQUEST['ajax_cmd']) {
	case 'studipNote':
		require_once 'lib/classes/StudipNote.class.php';
		$a_note = new StudipNote();
		switch($_REQUEST['ajax_cmd2']) {
			case 'update':
				$a_note->updateNote();
			 	break;
		 	case 'form':
				$a_note->getNoteForForm();
				break;
			default:
				$a_note->getNote();
		}
		break;
	default: ;
}
?>

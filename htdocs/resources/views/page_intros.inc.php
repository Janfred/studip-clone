<?
if ($resources_data["actual_object"]) {
	$currentObject=new ResourceObject($resources_data["actual_object"]);
	$currentObjectTitelAdd=": ".(($currentObject->getCategoryName()) ? $currentObject->getCategoryName() : _("Hierachieebene"));
	if ($currentObjectTitelAdd)
		$currentObjectTitelAdd=": ";
	$currentObjectTitelAdd=": ".$currentObject->getName()."&nbsp;<font size=-1>(".$currentObject->getOwnerName().")</font>";
}

switch ($resources_data["view"]) {
	//Reiter "Uebersicht"
	case "resources":
	case "_resources":
		$page_intro="Auf dieser Seite k&ouml;nnen Sie durch alle Ressourcen bzw. Ebenen, auf die Sie Zugriff haben, navigieren und Ressourcen verwalten.";
		$title="&Uuml;bersicht der Ressourcen";
	break;
	case "search":
		$page_intro="Sie k&ouml;nnen hier nach Ressourcen suchen. Sie haben die M&ouml;glichkeit, &uuml;ber ein Stichwort oder bestimmte Eigenschaften Ressourcen suchen oder sich durch die Ebenen navigieren.";
		$title="Suche nach Ressourcen";
	break;
	
	//Reiter "Listen"
	case "lists":
	case "_lists":
	case "export_lists":
	case "search_list":
		if ($resources_data["list_open"])
			$page_intro="Sie sehen alle Eintr&auml;ge in der Ebene <b>".getResourceObjectName($resources_data["list_open"])."</b>.";
		$title="Bearbeiten und Ausgeben von Listen";
		if ($resources_data["list_open"])
			$title.=" - Ebene: ".getResourceObjectName($resources_data["list_open"]);
		$infobox = array(
					array  ("kategorie"  => "Information:", 
							"eintrag" => array (
								array ("icon" => "pictures/ausruf_small.gif", 	
									"text"  => ($resources_data["list_recurse"]) ? "Untergeordnete Ebenen werden ausgegeben." : "Untergeordnete Ebenen werden <u>nicht</u> ausgegeben."))),
					array  ("kategorie" => "Aktionen:", 
							"eintrag" => array (
								array	("icon" =>  (!$resources_data["list_recurse"]) ? "pictures/on_small.gif" : "pictures/off_small.gif",
									"text"  => ($resources_data["list_recurse"]) ? "Ressourcen in untergeordneten Ebenen <br /><a href=\"$PHPSELF?nrecurse_list=TRUE\"><u>nicht</u> ausgeben.</a>" : "Ressourcen in untergeordneten Ebenen <br /><a href=\"$PHP_SELF?recurse_list=TRUE\">mit ausgeben</a>"))));
		$infopic = "pictures/rooms.jpg";
	break;

	//Reiter "Objekt"
	case "objects":
	case "edit_object_assign":
		$page_intro="Hier k&ouml;nnen Sie Ressourcen-Belegungen bearbeiten und neue anlegen.";
		$title="Belegungen bearbeiten".$currentObjectTitelAdd;
	break;
	case "edit_object_properties":
		$page_intro="Hier k&ouml;nnen Sie Ressourcen-Eigenschaften bearbeiten.";
		$title="Eigenschaften bearbeiten".$currentObjectTitelAdd;
	break;
	case "edit_object_perms":
		$page_intro="Hier k&ouml;nnen Sie Berechtigungen f&uuml;r den Zugriff auf die Ressource vergeben oder den Besitzer &auml;ndern. <br /><font size=\"-1\"><b>Achtung:</b> Alle hier erteilten Berechtigungen gelten ebenfalls f&uuml;r die Ressourcen, die der gew&auml;hlten Ressource untergeordnetet sind!</font>";
		$title="Eigenschaften bearbeiten".$currentObjectTitelAdd;
	break;
	case "view_schedule":
		$page_intro="Hier k&ouml;nnen Sie sich die Belegunszeiten der Ressource anzeigen lassen und auf unterschiedliche Art und Weise darstellen lassen.";
		$title="Belegungszeiten ausgeben".$currentObjectTitelAdd;
	break;
	
	//Reiter "Anpassen"	
	case "settings":
	case "edit_types":
		$page_intro="Verwalten Sie auf dieser Seite die Ressourcen-Typen, wie etwa R&auml;ume, Ger&auml;te oder Geb&auml;ude. Sie k&ouml;nnen jedem Typ beliebig viele Eigenschaften zuordnen.";
		$title="Typen bearbeiten";
	break;
	case "edit_properties":
		$page_intro="Verwalten Sie auf dieser Seite die eizelnen Eigenschaften. Diese Eigenschaften k&ouml;nnen Sie beliebigen Ressourcen-Typen zuweisen.";
		$title="Eigenschaften verwalten bearbeiten";
	break;
	case "edit_perms":
		$page_intro="Verwalten Sie hier alle Administratoren, die administrative Rechte oder Belegungsrechte &uuml;ber &uuml;ber alle Ressourcen besitzen.";
		$title="Ressourcen-Administratoren bearbeiten";
	break;
	
	//all the intros in an open object (Veranstaltung, Einrichtung)
	case "openobject_main":
		$page_intro="Auf dieser Seite sehen sie alle der ".$SessSemName["art_generic"]." zugeordneten Ressourcen.";
		$title=$SessSemName["header_line"]." - Ressourcen&uuml;bersicht";
	break;
	case "openobject_details":
	case "view_details":
		if ($resources_data["actual_object"])
			$page_intro="Hier sehen Sie detaillierte Informationen der Ressource <b>".$currentObject->getName()."</b> (".(($currentObject->getCategoryName()) ? $currentObject->getCategoryName() : _("Hierachieebene")).").";
		if ($resources_data["view_mode"] == "oobj")
			$title=$SessSemName["header_line"]." - Ressourcendetails";
		else
			$title="Anzeige der Ressourceneigenschaften";
		$infobox = array(
					array  ("kategorie" => "Aktionen:", 
							"eintrag" => array (
								array	("icon" => "pictures/suchen.gif",
									"text"  => ($resources_data["view_mode"] == "no_nav") ? "<a href=\"$PHP_SELF?view=search\">zur&uuml;ck zur Suche</a>" : "<a href=\"$PHP_SELF\">zur&uuml;ck zur &Uuml;bersicht</a>"),
)));
		$infopic = "pictures/schedule.jpg";
	break;
	case "openobject_schedule":
		if ($resources_data["actual_object"])
			$page_intro="Hier k&ouml;nnen Sie sich die Belegungszeiten der Ressource <b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().") ausgeben lassen.";
		$title=$SessSemName["header_line"]." - Ressourcenbelegung";
	break;
	case "openobject_assign":
		if ($resources_data["actual_object"])
			$page_intro="Bearbeiten von Belegungen der Ressource <b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().").";
		$title=$SessSemName["header_line"]." - Bearbeiten der Belegung";
	break;
	case "edit_object_perms":
		if ($resources_data["actual_object"])
			$page_intro="Vergeben von Rechten auf die Ressource <b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().").";
		$title="Vergeben von Berechtigungen - Objekt".$currentObjectTitelAdd;
	break;
	//default
	default:
		$page_intro="Sie befinden sich in der Ressurcenverwaltung von Stud.IP. Sie k&ouml;nnen hier R&auml;ume, Geb&auml;ude, Ger&auml;te und andere Ressourcen verwalten.";
		$title="&Uuml;bersicht der Ressourcen";
	break;
	}
?>
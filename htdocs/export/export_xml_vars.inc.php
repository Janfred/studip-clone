<?

// Variablen mit den XML-Bezeichnern
// "TABELLENSPALTE" =>		"XML-BEZEICHNER"
$xml_groupnames_fak = array(
	"group"			=>		"fakultaeten",
	"object"			=>		"fakultaet"
);

$xml_names_fak = array( 
	"Name"			=>		"name"
);

$xml_groupnames_inst = array(
	"object"			=>		"institut",
	"childobject"		=>		"fakultaet"
);

$xml_names_inst = array( 
	"type"			=>		"type",
	"Name"			=>		"name",
	"Strasse"			=>		"strasse",
	"Plz"			=>		"plz",
	"url"				=>		"homepage",
	"telefon"			=>		"telefon",
	"email"			=>		"email",
	"fax"			=>		"fax"
);

$xml_groupnames_lecture = array(
	"group"			=>		"seminare",
	"subgroup1"		=>		"gruppe",
	"subgroup2"		=>		"untergruppe",
	"object"			=>		"seminar",
	"childgroup1"		=>		"termine",
	"childgroup2"		=>		"dozenten",
	"childobject2"		=>		"dozent"
);

$xml_names_lecture = array( 
	"Name"			=>		"titel",
	"Untertitel"		=>		"untertitel",
	"status"			=>		"status",
	"Beschreibung"	=>		"beschreibung",
	"Ort"			=>		"raum",
	"Sonstiges"		=>		"sonstiges",
	"art"				=>		"art", 
	"teilnehmer"		=>		"teilnehmer",
	"vorrausetzungen"	=>		"voraussetzung",
	"lernorga"		=>		"lernorga",
	"leistungsnachweis"=>		"schein",
	"ects"			=>		"ects",
	"bereich"			=>		"bereich",
	"metadata_dates"	=>		array("vorbesprechung", "erstertermin", "termin")
);

$xml_groupnames_person = array(
	"group"			=>		"personen",
	"subgroup1"		=>		"gruppe",
	"object"			=>		"person"
);

$xml_names_person = array( 
	"title_front"		=>		"titel",
	"Vorname"		=>		"vorname",
	"Nachname"		=>		"nachname",
	"title_rear"		=>		"titel2",
	"geschlecht"		=>		"geschlecht",
	"sprechzeiten"		=>		"sprechzeiten",
	"raum"			=>		"raum",
	"Telefon"			=>		"telefon",
	"Fax"			=>		"fax",
	"Email"			=>		"email",
	"Home"			=>		"homepage",
	"name"			=>		"statusgruppe",
	"privadr"			=>		"adresse",
	"privatnr"			=>		"privatnummer"
);
?>
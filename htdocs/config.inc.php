<?
/**
* config.inc.php
* 
* Configuration file for studip. In this file you can change the options of many
* Stud.IP Settings. Please note: to setup the system, set the basic settings in the
* local.inc of the phpLib package first.
* 
* @access		public
* @package		studip_core
* @modulegroup	library
* @module		config.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// functions.php
// Stud.IP Kernfunktionen
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, 
// Ralf Stockmann <rstockm@gwdg.de>, André Noack André Noack <andre.noack@gmx.net>
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



//Generische Systemeinstellungen
$AUTH_LIFETIME=30;						//Zeit bis zu einem automatischem Logout in Minuten (wird zur Zeit nur zu Anzeigezwecken verwendet...)
$SOFTWARE_VERSION="0.9 rc 4";


//Daten ueber die Uni
    // der Name wird in der local.inc festgelegt
$UNI_URL="http://www.uni-goettingen.de";
$UNI_LOGOUT_ADD="Und hier geht's direkt zum <a href=\"http://studentenwerk.stud.uni-goettingen.de/mensa/mensen/alle_heute.php\"><b>Mensaplan</b></a>&nbsp;;-)";
$UNI_CONTACT="goettingen@studip.de";

//die IDs der Veranstaltungen, die beim Hochstufen auf autor eingetragen werden
$AUTO_INSERT_SEM[1]="3f43debe372cfd7d4da6afa4ca40616f";
$AUTO_INSERT_SEM[2]="db3ef283064dc8b34aa8e8df579998da";
//weitere Veranstaltungen koennen mit ihren IDs hier angefuegt werden


//Daten ueber Uniferien
$HOLIDAY[1]=array("beginn"=>mktime(0,0,0,12,21,2002), "ende"=>mktime (23,59,59,1,5,2003));		# Beginn und Ende der Weihnachtsferien 2002
$HOLIDAY[2]=array("beginn"=>mktime(0,0,0,6,9,2003), "ende"=>mktime (23,59,59,6,15,2003));		# Beginn und Ende der Pfingstferien 2003
$HOLIDAY[3]=array("beginn"=>mktime(0,0,0,12,20,2003), "ende"=>mktime (23,59,59,1,4,2004));		# Beginn und Ende der Weihnachtsferien 2003
//weitere Feiertage und Ferien koennen hier angefuegt werden


//Daten ueber Semester (ACHTUNG: Der Beginn der Vorlesungszeit muss ein Montag, 00:00 Uhr und das Ende ein Sonntag, 23:59 sein!)
$SEMESTER[1]=array("name"=>"WS 2000/01", "beginn"=>mktime(0,0,0,10,1,2000), "ende"=>mktime(23,59,59,3,31,2001), "vorles_beginn"=>mktime(0,0,0,10,14,2000), "vorles_ende"=>mktime(23,59,59,2,17,2001), "past"=>FALSE); 		# Daten ueber das WS 2000/01
$SEMESTER[2]=array("name"=>"SS 2001", "beginn"=>mktime(0,0,0,4,1,2001), "ende"=>mktime(23,59,59,9,30,2001), "vorles_beginn"=>mktime(0,0,0,4,16,2001), "vorles_ende"=>mktime(23,59,59,7,15,2001), "past"=>FALSE); 			# Daten ueber das SS 2001
$SEMESTER[3]=array("name"=>"WS 2001/02", "beginn"=>mktime(0,0,0,10,1,2001), "ende"=>mktime(23,59,59,3,31,2002), "vorles_beginn"=>mktime(0,0,0,10,15,2001), "vorles_ende"=>mktime(23,59,59,2,17,2002), "past"=>FALSE); 		# Daten ueber das WS 2001/02
$SEMESTER[4]=array("name"=>"SS 2002", "beginn"=>mktime(0,0,0,4,1,2002), "ende"=>mktime(23,59,59,9,30,2002), "vorles_beginn"=>mktime(0,0,0,4,8,2002), "vorles_ende"=>mktime(23,59,59,7,7,2002), "past"=>FALSE); 				# Daten ueber das SS 2002
$SEMESTER[5]=array("name"=>"WS 2002/03", "beginn"=>mktime(0,0,0,10,1,2002), "ende"=>mktime(23,59,59,3,31,2003), "vorles_beginn"=>mktime(0,0,0,10,14,2002), "vorles_ende"=>mktime(23,59,59,2,14,2003), "past"=>FALSE); 		# Daten ueber das WS 2002/03
$SEMESTER[6]=array("name"=>"SS 2003", "beginn"=>mktime(0,0,0,4,1,2003), "ende"=>mktime(23,59,59,9,30,2003), "vorles_beginn"=>mktime(0,0,0,4,21,2003), "vorles_ende"=>mktime(23,59,59,7,18,2003), "past"=>FALSE); 			# Daten ueber das SS 2003
$SEMESTER[7]=array("name"=>"WS 2003/04", "beginn"=>mktime(0,0,0,10,1,2003), "ende"=>mktime(23,59,59,3,31,2004), "vorles_beginn"=>mktime(0,0,0,10,13,2003), "vorles_ende"=>mktime(23,59,59,2,14,2004), "past"=>FALSE); 		# Daten ueber das WS 2003/04
//weitere Semester koennen hier angefuegt werden


//Festlegen der zulaessigen Typen fuer Veranstaltungen
$SEM_TYPE_MISC_NAME="sonstige"; //dieser Name wird durch die allgemeine Bezechnung (=Veranstaltung ersetzt)
$SEM_TYPE[1]=array("name"=>"Vorlesung", "en"=>"Lecture", "class"=>1);
$SEM_TYPE[2]=array("name"=>"Grundstudium", "en"=>"Basic classes", "class"=>1);
$SEM_TYPE[3]=array("name"=>"Hauptstudium", "en"=>"Advanced classes", "class"=>1);
$SEM_TYPE[4]=array("name"=>"Seminar", "en"=>"Seminar", "class"=>1);
$SEM_TYPE[5]=array("name"=>"Praxisveranstaltung", "en"=>"Practical course", "class"=>1);
$SEM_TYPE[6]=array("name"=>"Colloquium", "en"=>"Colloqia", "class"=>1);
$SEM_TYPE[7]=array("name"=>"Forschungsgruppe", "en"=>"Research group", "class"=>1);
$SEM_TYPE[8]=array("name"=>"Arbeitsgruppe", "en"=>"Workgroup", "class"=>5);
$SEM_TYPE[9]=array("name"=>"sonstige", "en"=>"Miscellaneous", "class"=>1); 
$SEM_TYPE[10]=array("name"=>"Forschungsgruppe", "en"=>"Research group", "class"=>2); 
$SEM_TYPE[11]=array("name"=>"sonstige", "en"=>"Miscellaneous", "class"=>2); 
$SEM_TYPE[12]=array("name"=>"Gremiumsveranstaltung", "en"=>"Board meeting", "class"=>3); 
$SEM_TYPE[13]=array("name"=>"sonstige", "en"=>"Miscellaneous", "class"=>3); 
$SEM_TYPE[14]=array("name"=>"Kulturforum", "en"=>"Cultural forum", "class"=>4); 
$SEM_TYPE[15]=array("name"=>"sonstige", "en"=>"Miscellaneous", "class"=>4); 
$SEM_TYPE[16]=array("name"=>"Praktikum", "en"=>"Practical course", "class"=>1); 
$SEM_TYPE[17]=array("name"=>"Lehrveranstaltung nach PVO-Lehr I", "en"=>"", "class"=>1); 
$SEM_TYPE[18]=array("name"=>"Anleitung zu selbständigen wissenschaftlichen Arbeiten", "en"=>"", "class"=>1); 
$SEM_TYPE[19]=array("name"=>"Sprachkurs", "en"=>"Language Course", "class"=>1);
$SEM_TYPE[20]=array("name"=>"Fachdidaktik", "en"=>"Didactics", "class"=>1);
$SEM_TYPE[21]=array("name"=>"Übung", "en"=>"Exercise Course", "class"=>1);
$SEM_TYPE[22]=array("name"=>"Proseminar", "en"=>"Proseminar", "class"=>1);
$SEM_TYPE[23]=array("name"=>"Oberseminar", "en"=>"Oberseminar", "class"=>1);
$SEM_TYPE[24]=array("name"=>"Arbeitsgemeinschaft", "en"=>"Workgroup", "class"=>1);
//weitere Typen koennen hier angefuegt werden


//Festlegen der zulaessigen Klassen fuer Veranstaltungen. Jeder sem_type referenziert auf eine dieser Klassen
$SEM_CLASS[1]=array("name"=>"Lehre", 					 	//the name of the class
					"compact_mode"=>FALSE, 			//indicates, if all fields are used in the creation process or only the fields that are necessary for workgroups
					"workgroup_mode"=>FALSE, 			//indicates, if the workgroup mode is used (to use different declarations)
					"only_inst_user"=>TRUE,				//indicates, that olny staff from the Einrichtungen which own the Veranstaltung, are allowed for tutor and dozent
					"turnus_default"=>0	, 				//indicates, whether the turnus field is default set to "regulary" (0), "not regulary" (1) or "no dates" (-1) in the creation process
					"default_read_level"=>1, 				//the default read acces level. "without signed in" (0), "signed in" (1), "password" (2)
					"default_write_level" =>1, 				//the default write acces level. "without signed in" (0), "signed in" (1), "password" (2)
					"bereiche"=>TRUE,					//indicates, if bereiche should be used
					"show_browse"=>TRUE, 				//indicates, if the hierachy-system should be shown in the search-process
					"write_access_nobody"=>FALSE, 		//indicates, if write access level 0 is possible. If this is not possibly, don't set default_write_level to 0
					"description"=>"Hier finden Sie alle im System registrierten Lehrveranstaltungen", 						//the description
					"create_description"=>"Verwenden Sie diese Kategorie, um normale Lehrveranstaltungen anzulegen");		//the description in the creation process

$SEM_CLASS[2]=array("name"=>"Forschung", 
					"compact_mode"=>TRUE, 
					"workgroup_mode"=>TRUE, 
					"only_inst_user"=>TRUE,
					"turnus_default"=>-1, 
					"default_read_level"=>2, 
					"default_write_level" =>2, 
					"bereiche"=>FALSE, 
					"show_browse"=>TRUE,
					"write_access_nobody"=>FALSE, 
					"description"=>"Hier finden Sie virtuelle Veranstaltungen zum Thema Forschung an der Universit&auml;t",
					"create_description"=>"In dieser Kategorie k&ouml;nnen sie virtuelle Veranstaltungen f&uuml;r Forschungsprojekte anlegen.");

$SEM_CLASS[3]=array("name"=>"Organisation", 
					"compact_mode"=>TRUE, 
					"workgroup_mode"=>TRUE, 
					"only_inst_user"=>FALSE,
					"turnus_default"=>-1, 
					"default_read_level"=>2, 
					"default_write_level" =>2, 
					"bereiche"=>FALSE, 
					"show_browse"=>TRUE,
					"write_access_nobody"=>TRUE, 
					"description"=>"Hier finden Sie virtuelle Veranstaltungen zu verschiedenen Uni-Gremien",
					"create_description"=>"Um virtuelle Veranstaltungen f&uuml;r Uni-Gremien anzulegen, verwenden Sie diese Kategorie");

$SEM_CLASS[4]=array("name"=>"Community", 
					"compact_mode"=>TRUE, 
					"workgroup_mode"=>FALSE, 
					"only_inst_user"=>FALSE,
					"turnus_default"=>-1, 
					"default_read_level"=>0, 
					"default_write_level" =>0, 
					"bereiche"=>FALSE, 
					"show_browse"=>FALSE,
					"write_access_nobody"=>TRUE, 
					"description"=>"Hier finden Sie virtuelle Veranstaltungen zu unterschiedlichen Themen",
					"create_description"=>"Wenn Sie Veranstaltungen als Diskussiongruppen zu unterschiedlichen Themen anlegen m&ouml;chten, verwenden Sie diese Kategorie.");

$SEM_CLASS[5]=array("name"=>"Arbeitsgruppen", 
					"compact_mode"=>FALSE, 
					"workgroup_mode"=>FALSE, 
					"only_inst_user"=>TRUE,
					"turnus_default"=>1, 
					"default_read_level"=>1, 
					"default_write_level" =>1, 
					"bereiche"=>FALSE, 
					"show_browse"=>FALSE,
					"write_access_nobody"=>FALSE, 
					"description"=>"Hier finden Sie verschiedene Arbeitsgruppen an der $UNI_NAME",
					"create_description"=>"Verwenden Sie diese Kategorie, um unterschiedliche Arbeitsgruppen anzulegen.");
//weitere Klassen koennen hier angefuegt werden. Bitte Struktur wie oben exakt uebernehmen.


//Festlegen der erlaubten oder verbotenen Dateitypen
$UPLOAD_TYPES=array( 	"default" =>												//Name bezeichnet den zugehoerigen SEM_TYPE, name "1" waere entsprechend die Definition der Dateiendungen fuer SEM_TYPE[1]; default wird verwendet, wenn es keine spezielle Definition fuer einen SEM_TYPE gibt
						array(	"type"=>"deny", 									//Type bezeichnet den grundsetzlichen Typ der Deklaration: deny verbietet alles ausser den angegebenen file_types, allow erlaubt alle ausser den angegebenen file_types
								"file_types" => array ("rtf", "xls", "ppt", "zip", "pdf", "txt"),	//verbotene bzw. erlaubte Dateitypen
								"file_sizes" => array (	"root" => 7 * 1048576,			//Erlaubte Groesse je nach Rechtestufe
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,
													"tutor" => 7 * 1048576,
													"autor" => 1.38 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							),
						"7" =>													//Kommentar: Leider wird der Sem_type noch immer alphanumerisch gespeichert, daher 
						array(	"type"=>"allow", 									//Bezeichnung ueber Namen und nicht ueber die jeweilige Nummer.... muss mal angepasst werden!
								"file_types" => array ("exe"),
								"file_sizes" => array (	"root" => 7 * 1048576,			
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,
													"tutor" => 7 * 1048576,													
													"autor" => 7 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							),
						"8" =>									
						array(	"type"=>"allow",
								"file_types" => array ("exe"),
								"file_sizes" => array (	"root" => 7 * 1048576,			
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,													
													"tutor" => 7 * 1048576,
													"autor" => 7 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							),
						"9" =>									
						array(	"type"=>"allow",
								"file_types" => array ("exe"),
								"file_sizes" => array (	"root" => 7 * 1048576,			
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,													
													"tutor" => 7 * 1048576,
													"autor" => 7 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							),
						"10" =>									
						array(	"type"=>"allow",
								"file_types" => array ("exe"),
								"file_sizes" => array (	"root" => 7 * 1048576,			
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,													
													"tutor" => 7 * 1048576,
													"autor" => 7 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							),
						"11" =>									
						array(	"type"=>"allow",
								"file_types" => array ("exe"),
								"file_sizes" => array (	"root" => 7 * 1048576,			
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,													
													"tutor" => 7 * 1048576,
													"autor" => 7 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							),
						"12" =>									
						array(	"type"=>"allow",
								"file_types" => array ("exe"),
								"file_sizes" => array (	"root" => 7 * 1048576,			
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,													
													"tutor" => 7 * 1048576,
													"autor" => 7 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							),
						"13" =>									
						array(	"type"=>"allow",
								"file_types" => array ("exe"),
								"file_sizes" => array (	"root" => 7 * 1048576,			
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,													
													"tutor" => 7 * 1048576,
													"autor" => 7 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							)
					);
//weitere Definitionen fuer spezielle Veranstaltungstypen koennen hier angefuegt werden. Bitte Struktur wie oben exakt uebernehmen.


//Festlegen von zulaessigen Bezeichnungen fuer Einrichtungen (=Institute)
$INST_TYPE[1]=array("name"=>"Einrichtung", "en"=>"Institute");
$INST_TYPE[2]=array("name"=>"Zentrum", "en"=>"Center");
$INST_TYPE[3]=array("name"=>"Lehrstuhl", "en"=>"Lehrstuhl");
$INST_TYPE[4]=array("name"=>"Abteilung", "en"=>"Unit");
$INST_TYPE[5]=array("name"=>"Fachbereich", "en"=>"Fachbereich");
$INST_TYPE[6]=array("name"=>"Seminar", "en"=>"Seminar");
$INST_TYPE[7]=array("name"=>"Fakultät");
//weitere Typen koennen hier angefuegt werden


//define the presets of statusgroups for Veranstaltungen (refers to the key of the $SEM_CLASS array)
$SEM_STATUS_GROUPS["default"] = array ("DozentInnen", "TutorInnen", "AutorInnen", "LeserInnen", "sonstige"); 	//the default. Don't delete this entry!
$SEM_STATUS_GROUPS["2"] = array ("Projektleitung", "Koordination", "Forschung", "Verwaltung", "sonstige");
$SEM_STATUS_GROUPS["3"] = array ("Organisatoren", "Mitglieder", "Ausschu&szlig;mitglieder", "sonstige");
$SEM_STATUS_GROUPS["4"] = array ("Moderatoren des Forums","Mitglieder", "sonstige");
$SEM_STATUS_GROUPS["5"] = array ("ArbeitsgruppenleiterIn", "Arbeitsgruppenmitglieder", "sonstige");
//you can add more specifig presets for the different classes 


//define the presets of statusgroups for Einrichtungen (refers to the key of the $INST_TYPE array)
$INST_STATUS_GROUPS["default"] = array ("DirektorIn", "HochschullehrerIn", "Lehrbeauftragte", "Zweitmitglied", "wiss. Hilfskraft","wiss. MitarbeiterIn",
									"stud. Hilfskraft", "Frauenbeauftragte", "Internetbeauftragte(r)", "StudentIn", "techn. MitarbeiterIn", "Sekretariat / Verwaltung", 
									"stud. VertreterIn");
//you can add more specifig presets for the different types 


//Festlegen von zulaessigen Funktion fuer Institutsmitarbeiter !!!!wird im Prinzip ersetzt durch die Statusgruppen und kann daher eigentlich weg!!!!
$INST_FUNKTION[1]=array("name"=>"StudentIn", "en"=>"Student");
$INST_FUNKTION[2]=array("name"=>"stud. VertreterIn", "en"=>"Collegiate representative");
$INST_FUNKTION[3]=array("name"=>"Sekretariat / Verwaltung", "en"=>"Secretariate");
$INST_FUNKTION[4]=array("name"=>"stud. Hilfskraft", "en"=>"Student assistant");
$INST_FUNKTION[5]=array("name"=>"wiss. Hilfskraft", "en"=>"Teaching assistant");
$INST_FUNKTION[6]=array("name"=>"wiss. Mitarbeiter", "en"=>"Scientific assistant");
$INST_FUNKTION[7]=array("name"=>"Lehrbeauftragte", "en"=>"Assistant professor");
$INST_FUNKTION[8]=array("name"=>"Zweitmitglied", "en"=>"Lecturers from other institutes");
$INST_FUNKTION[9]=array("name"=>"Akademischer Rat", "en"=>"Academic council");
$INST_FUNKTION[10]=array("name"=>"Akademischer Oberrat", "en"=>"Academic chiefcouncil");
$INST_FUNKTION[11]=array("name"=>"Akademischer Direktor", "en"=>"Academic principal");
$INST_FUNKTION[12]=array("name"=>"HochschullehrerIn", "en"=>"Lecturer");
$INST_FUNKTION[13]=array("name"=>"DirektorIn", "en"=>"Principal");
$INST_FUNKTION[14]=array("name"=>"Admin", "en"=>"Admin");
$INST_FUNKTION[15]=array("name"=>"techn. MitarbeiterIn", "en"=>"Technical assistant");
$INST_FUNKTION[16]=array("name"=>"Sonstige", "en"=>"Miscellaneous");
$INST_FUNKTION[17]=array("name"=>"DoktorandIn", "en"=>"Doctoral Candidate");
$INST_FUNKTION[18]=array("name"=>"DiplomandIn", "en"=>"Graduand");
$INST_FUNKTION[19]=array("name"=>"freie(r) MitarbeiterIn", "en"=>"assoziated member");
$INST_FUNKTION[20]=array("name"=>"Bibliothek", "en"=>"Library");
$INST_FUNKTION[21]=array("name"=>"Emeriti", "en"=>"Emeriti");
$INST_FUNKTION[22]=array("name"=>"ausserdem am Institut tätig", "en"=>"assoziated member");
$INST_FUNKTION[23]=array("name"=>"DozentIn institutsübergreifender Veranstaltungen", "en"=>"Lecturer of interinstitutional courses");
//weitere Funktionen koennen hier angefuegt werden


//Festlegen der Veranstaltungs Termin Typen
$TERMIN_TYP[1]=array("name"=>"Sitzung", "sitzung"=>1, "color"=>"#FF7F50"); 		//dieser Termin Typ wird immer als Seminarsitzung verwendet und im Ablaufplan entsprechend markiert. Der Titel kann veraendert werden, Eintraege aus dem Seminar Assistenten und Terminverwaltung fuer Seminar-Sitzungsterrmine bekommen jedoch immer diesen Typ
$TERMIN_TYP[2]=array("name"=>"Vorbesprechung", "sitzung"=>0, "color"=>"#DC143C"); 	//dieser Termin Typ wird immer als Vorbesprechung verwendet. Der Titel kann veraendert werden, Eintraege aus dem Seminar Assistenten fuer Vorbesprechungen bekommen jedoch immer diesen Typ
$TERMIN_TYP[3]=array("name"=>"Klausur", "sitzung"=>0, "color"=>"#FF0000");
$TERMIN_TYP[4]=array("name"=>"Exkursion", "sitzung"=>0, "color"=>"#FFA500");
$TERMIN_TYP[5]=array("name"=>"anderer Termin", "sitzung"=>0, "color"=>"#B8660B");
$TERMIN_TYP[6]=array("name"=>"Sondersitzung", "sitzung"=>0, "color"=>"#FFA500");
$TERMIN_TYP[7]=array("name"=>"Vorlesung", "sitzung"=>1, "color"=>"#FF7F50");
//weitere Typen koennen hier angefuegt werden


// Festlegen der Kategorien für persönlichen Terminkalender
$PERS_TERMIN_KAT[1]=array("name"=>"Sonstiges", "color"=>"#B8660B");
$PERS_TERMIN_KAT[2]=array("name"=>"Sitzung", "color"=>"#FF7F50");
$PERS_TERMIN_KAT[3]=array("name"=>"Vorbesprechung", "color"=>"#DC143C");
$PERS_TERMIN_KAT[4]=array("name"=>"Klausur", "color"=>"#FF0000");
$PERS_TERMIN_KAT[5]=array("name"=>"Exkursion", "color"=>"#FFA500");
$PERS_TERMIN_KAT[6]=array("name"=>"Sondersitzung", "color"=>"#FFA500");
$PERS_TERMIN_KAT[7]=array("name"=>"Prüfung", "color"=>"#FF0000");
$PERS_TERMIN_KAT[8]=array("name"=>"Telefonat", "color"=>"#6B8E23");
$PERS_TERMIN_KAT[9]=array("name"=>"Besprechung", "color"=>"#32CD23");
$PERS_TERMIN_KAT[10]=array("name"=>"Verabredung", "color"=>"#228B22");
$PERS_TERMIN_KAT[11]=array("name"=>"Geburtstag", "color"=>"#9932CC");
$PERS_TERMIN_KAT[12]=array("name"=>"Familie", "color"=>"#191970");
$PERS_TERMIN_KAT[13]=array("name"=>"Urlaub", "color"=>"#DB7093");
$PERS_TERMIN_KAT[14]=array("name"=>"Reise", "color"=>"#C71585");
// weitere Kategorien können hier angefügt werden

//Vorgaben für die Titelauswahl
$TITLE_FRONT_TEMPLATE = array("",_("Prof."),_("Prof. Dr."),_("Dr."),_("PD Dr."),_("Dr. des."),_("Dr. med."),_("Dr. rer. nat."),_("Dr. forest."),
							_("Dr. sc. agr."),_("Dipl.-Biol."),_("Dipl.-Chem."),_("Dipl.-Ing."),_("Dipl.-Sozw."),_("Dipl.-Geogr."),
							_("Dipl.-Geol."),_("Dipl.-Geophys."),_("Dipl.-Ing. agr."),_("Dipl.-Kfm."),_("Dipl.-Math."),_("Dipl.-Phys."),
							_("Dipl.-Psych."),_("M. Sc"),_("B. Sc"));
$TITLE_REAR_TEMPLATE = array("",_("M.A."),_("B.A."),_("M.S."),_("MBA"),_("Ph.D."),_("Dipl.-Biol."),_("Dipl.-Chem."),_("Dipl.-Ing."),_("Dipl.-Sozw."),_("Dipl.-Geogr."),
							_("Dipl.-Geol."),_("Dipl.-Geophys."),_("Dipl.-Ing. agr."),_("Dipl.-Kfm."),_("Dipl.-Math."),_("Dipl.-Phys."),
							_("Dipl.-Psych."),_("M. Sc"),_("B. Sc"));

//Shorts for Smiley
$SMILE_SHORT = array( //diese Kuerzel fuegen das angegebene Smiley ein (Dateiname + ".gif")
	":)"=>"smile" , 
	":-)"=>"asmile" , 
	":#:"=>"zwinker" , 
	":("=>"frown" , 
	":o"=>"redface" , 
	":D"=>"biggrin", 
	";-)"=>"wink");
	
//Shorts for symbols
$SYMBOL_SHORT = array( //use this shorts to insert an symbols (filename + ".gif") 
	"=)"=>"symbol03" , 
	"(="=>"symbol04" , 
	"(c)"=>"symbol05" , 
	"(r)"=>"symbol06" , 
	" tm "=>"symbol08");
?>

<?	

// Zeichenkette, die vor Ilias-Usernamen gesetzt wird:
// IM LAUFENDEN BETRIEB NICHT MEHR ÄNDERN!!!
$username_prefix = "studip_";

// Zuordnung von Stud.IP-Status zu ILIAS-Status
// DEFAULT: 1 = Gast, 2 = Superuser, 3 = StudentIn, 4 = MitarbeiterIn
$ilias_status = array(
"user" => "1",
"autor" => "3",
"tutor" => "3",
"dozent" => "4",
"admin" => "2",
"root" => "2",
);

// Zuordnung von Stud.IP-Status zu ILIAS-System-Gruppe
// DEFAULT: 1 = AdministratorIn, 2 = AutorIn, 3 = LernerIn, 4 = Gast
$ilias_systemgroup = array(
"user" => "4",
"autor" => "2",
"tutor" => "2",
"dozent" => "2",
"admin" => "1",
"root" => "1",
);
?>
<?php
/*
about.php - Anzeige de pesoenlichen Useseiten von Stud.IP
Copyight (C) 2000 Ralf Stockmann <stockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>, Niclas Nohlen <nnohlen@gwdg.de>

This pogam is fee softwae; you can edistibute it and/o
modify it unde the tems of the GNU Geneal Public License
as published by the Fee Softwae Foundation; eithe vesion 2
of the License, o (at you option) any late vesion.

This pogam is distibuted in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied waanty of
MERCHANTABILITY o FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Geneal Public License fo moe details.

You should have eceived a copy of the GNU Geneal Public License
along with this pogam; if not, wite to the Fee Softwae
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/


    page_open(aay("sess" => "Semina_Session", "auth" => "Semina_Auth", "pem" => "Semina_Pem", "use" => "Semina_Use"));
    $pem->check("use");

?>
<html>
<head>
<title>Stud.IP</title>
 <link el="stylesheet" hef="style.css" type="text/css">
<scipt language="Javascipt">
function open_im()
{
fenste=window.open("studipim.php","im_<?=$use->id;?>","scollbas=yes,width=400,height=300","esizable=no");
}
</scipt>
</head>
<!--
// hee i include my pesonal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php pint $auth->lifetime*60;?>; URL=logout.php">
-->
<body bgcolo=white>

<?php
 include "semina_open.php"; //hie weden die sessions initialisiet
?>

<!-- hie muessen Seiten-Initialisieungen passieen -->

<?php
include "heade.php";   //hie wid de "Kopf" nachgeladen
include ("show_news.php");
include ("show_dates.inc.php");
equie_once("functions.php");
equie_once("config.inc.php");
equie_once("dates.inc.php");
equie_once("messaging.inc.php");
equie_once("msg.inc.php");

$sess->egiste("about_data");
$msging=new messaging;

//Typen zu den Buddies beipacken
if ($cmd=="add_use")
	$msging->add_buddy ($add_uname, 0);
	
//Auf und Zuklappen Temine
if ($dopen)
   $about_data["dopen"]=$dopen;

if ($dclose)
   $about_data["dopen"]='';

//Auf und Zuklappen News
if ($nopen)
    $about_data["nopen"]=$nopen;

if ($nclose)
   $about_data["nopen"]='';

if ($sms_msg)
	$msg=awuldecode($sms_msg);

//Wenn kein Usename uebegeben wude, wid de eigene genommen:

if (!isset($usename) || $usename == "")
    $usename = $auth->auth["uname"];

 $db = new DB_Semina;
 $db2 = new DB_Semina;
 $db3 = new DB_Semina;

//3 zeilen wegen usename statt id zum aufuf... in $use_id steht jetzt die use_id (sic)
 $db->quey("SELECT * FROM auth_use_md5  WHERE usename ='$usename'");
 $db->next_ecod();
 $use_id=$db->f("use_id");

//Wenn e noch nicht in use_info eingetagen ist, kommt e ohne Wete ein
 $db->quey("SELECT * FROM use_info WHERE use_id ='$use_id'");
 if ($db->num_ows()==0) {
  $db->quey("INSERT INTO use_info (use_id) VALUES ('$use_id')");
 }

//Bin ich ein Inst_admin, und ist de use in meinem Inst Tuto ode Dozent?
 $db->quey("SELECT b.inst_pems FROM use_inst AS a LEFT JOIN use_inst AS b USING (Institut_id) WHERE (b.use_id = '$use_id') AND (b.inst_pems = 'auto' OR b.inst_pems = 'tuto' OR b.inst_pems = 'dozent') AND (a.use_id = '$use->id') AND (a.inst_pems = 'admin')");
 if ($db->num_ows())
  $admin_daf = TRUE;
 else $admin_daf = FALSE;

//He mit den Daten...
 $db->quey("SELECT use_info.* , auth_use_md5.* FROM auth_use_md5 LEFT JOIN use_info USING (use_id) WHERE auth_use_md5.use_id = '$use_id'");
 $db->next_ecod();

//daten anzeigen
 IF (($use_id==$use->id AND $pem->have_pem("auto")) OR $pem->have_pem("oot") OR $admin_daf == TRUE) { // Es weden die Editeite angezeigt, wenn ich &auml;nden daf
   
    ?>  
<table cellpadding="0" cellspacing="0" bode="0">
<t>
 <td class="links1b" align="ight" nowap><a  class="links1" hef="about.php?usename=<?echo $usename?>"><font colo="#000000" size=2><b>&nbsp; &nbsp; Alle&nbsp; &nbsp; </b></font></a><img sc="pictues/eite2.jpg" align=absmiddle></td>
 <td class="links1" align="ight" nowap><a  class="links1" hef="edit_about.php?view=Bild&usename=<?echo $usename?>"><font colo="#000000" size=2><b>&nbsp; &nbsp; Bild&nbsp; &nbsp; </b></font></a><img sc="pictues/eite1.jpg" align=absmiddle></td>
 <td class="links1" align="ight" nowap><a  class="links1" hef="edit_about.php?view=Daten&usename=<?echo $usename?>"><font colo="#000000" size=2><b>&nbsp; &nbsp; Daten&nbsp; &nbsp; </b></font></a><img sc="pictues/eite1.jpg" align=absmiddle></td>
 <td class="links1" align="ight" nowap><a  class="links1" hef="edit_about.php?view=Kaiee&usename=<?echo $usename?>"><font colo="#000000" size=2><b>&nbsp; &nbsp; Kaiee&nbsp; &nbsp; </b></font></a><img sc="pictues/eite1.jpg" align=absmiddle></td>
 <td class="links1" align="ight" nowap><a  class="links1" hef="edit_about.php?view=Lebenslauf&usename=<?echo $usename?>"><font colo="#000000" size=2><b>&nbsp; &nbsp; Lebenslauf&nbsp; &nbsp; </b></font></a><img sc="pictues/eite1.jpg" align=absmiddle></td>
 <td class="links1" align="ight" nowap><a  class="links1" hef="edit_about.php?view=Sonstiges&usename=<?echo $usename?>"><font colo="#000000" size=2><b>&nbsp; &nbsp; Sonstiges&nbsp; &nbsp; </b></font></a><img sc="pictues/eite1.jpg" align=absmiddle></td>
<? 	IF ($auth->auth["pem"]!="admin" AND $auth->auth["pem"]!="oot") {?>
	<td class="links1" align="ight" nowap><a  class="links1" hef="edit_about.php?view=Login"><font colo="#000000" size=2><b>&nbsp; &nbsp; MyStud.IP&nbsp; &nbsp; </b></font></a><img sc="pictues/eite1.jpg" align=absmiddle></td>
<?}?>
</t></table>
<table class=blank cellspacing=0 cellpadding=0 bode=0 width="100%">
<t><td class=steel1>&nbsp; 
</td></t><t><td class=eiteunten>&nbsp; </td></t></table>
<?

	}

?>

   <table align="cente" width="100%" bode="0" cellpadding="1" cellspacing="0" valign="top">
 <t><td class="topic" align="ight" colspan=2>&nbsp;</td></t>
<?
if ($msg)
	{
	echo"<t><td class=\"steel1\"colspan=2><b>";
	pase_msg ($msg, "§", "steel1");
	echo"</td></t>";
	}
?>
  
   <t><td class="steel1" align="cente" valign="cente"><img sc="pictues/blank.gif" width=205 height=5><b />
   <?

// hie wid das Bild ausgegeben

	if(!file_exists("./use/".$use_id.".jpg")) {
		echo "&nbsp;<img sc=\"./use/nobody.jpg\" width=\"200\" height=\"250\" alt=\"kein pes&ouml;nliches Bild vohanden\">";
	} else {
		?>&nbsp;<img sc="./use/<?echo $use_id; ?>.jpg" bode=1 alt="<?echo htmlReady($db->f("Voname"))." ".htmlReady($db->f("Nachname"));?>"></td><?
	}
    
	// Hie de Teil fue die Ausgabe de nomalen Daten
	?>
    <td class="steel1"  width="99%" valign ="top" owspan=2><b><blockquote>
    <? echo "<b><font size=7>",htmlReady($db->f("Voname")), " ", htmlReady($db->f("Nachname")),"</font></b><b><b>";?>
    <? echo "<b>&nbsp;e-mail: </b><a hef=\"mailto:", $db->f("Email"),"\">",htmlReady($db->f("Email")),"</a>","<b>";
		IF ($db->f("pivatn")!="") echo "<b>&nbsp;Telefon (pivat): </b>", htmlReady($db->f("pivatn")),"<b>";
		IF ($db->f("pivad")!="") echo "<b>&nbsp;Adesse (pivat): </b>", htmlReady($db->f("pivad")),"<b>";
		IF ($db->f("Home")!="") {
			$home=$db->f("Home");
			$home=FixLinks($home);
			echo "<b>&nbsp;Homepage: </b>",$home,"<b>";
		}

// Anzeige de Institute an denen (hoffentlich) studiet wid:

    $db3->quey("SELECT Institute.* FROM Institute LEFT JOIN use_inst USING (Institut_id) WHERE use_id = '$use_id' AND inst_pems = 'use'");
    IF ($db3->num_ows()) {
			echo "<b><b>&nbsp;Wo ich studiee:&nbsp;&nbsp;</b><b>";
      while ($db3->next_ecod()) {
      	echo "&nbsp; &nbsp; &nbsp; &nbsp;<a hef=\"institut_main.php?auswahl=".$db3->f("Institut_id")."\">".htmlReady($db3->f("Name"))."</a><b>";
    	}
		}

// Anzeige de Institute an denen geabeitet wid

		$db3->quey("SELECT * FROM use_inst WHERE use_id = '$use_id' AND inst_pems != 'use'");
		IF ($db3->num_ows()) {
			echo "<b><b>&nbsp;Wo ich abeite:&nbsp;&nbsp;</b><b>";
		}

   //schleife weil evtl. mehee spechzeiten und institut nicht gesetzt...

		while ($db3->next_ecod()) {
			$institut=$db3->f("Institut_id");
			$db2->quey("SELECT * FROM Institute WHERE Institut_id = '$institut'");
			$db2->next_ecod();
		      	echo "&nbsp; &nbsp; &nbsp; &nbsp;<a hef=\"institut_main.php?auswahl=".$db2->f("Institut_id")."\">".htmlReady($db2->f("Name"))."</a>";
			//echo "&nbsp; &nbsp; &nbsp;<b><a hef=\"".$db2->f("ul")."\" taget=\"_blank\">".htmlReady($db2->f("Name")),"</a></b>";
			//echo "&nbsp; &nbsp; &nbsp;<b>".fomatReady("[".tim($db2->f("Name"))."]".tim($db2->f("ul")))."</b>";
			
			IF ($db3->f("Funktion"))
				echo ", ",$INST_FUNKTION[$db3->f("Funktion")]["name"]; 
    	echo "<font size=-1>";
			IF ($db3->f("aum")!="")
				echo "<b><b>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Raum: </b>", htmlReady($db3->f("aum"));
			IF ($db3->f("spechzeiten")!="")
				echo "<b><b>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Spechzeit: </b>", htmlReady($db3->f("spechzeiten"));
			IF ($db3->f("Telefon")!="")
				echo "<b><b>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Telefon: </b>", htmlReady($db3->f("Telefon"));
			IF ($db3->f("Fax")!="")
				echo "<b><b>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Fax: </b>", htmlReady($db3->f("Fax"));

			echo "</font><b>";
		}
		echo "</blockquote></td></t>"
    ?>
    
		</td></t><t>
		<td class="steel1" height=99% align="left" valign="top">
		<?

	if ($usename==$auth->auth["uname"]) {
		if ($auth->auth["jscipt"])
			echo "<b>&nbsp; <a hef='javascipt:open_im();'>Stud.IP Messenge staten</a>";
		} else {
	        	echo "<b>&nbsp; Nachicht an Use: <a hef=\"sms.php?sms_souce_page=about.php&usename=$usename&cmd=wite&ec_uname=", $db->f("usename"),"\"><img sc=\"pictues/nachicht1.gif\" alt=\"Nachicht an Use veschicken\" bode=0 align=texttop></a>";
			if (!$my_buddies[$usename])
				echo "<b />&nbsp; <a hef=\"$PHP_SELF?cmd=add_use&add_uname=$usename&usename=$usename\">Zu Buddies hinzuf&uuml;gen</a>";
		}
    	

/// Die Anzeige de Stud.Ip-Scoe

		IF ($usename==$auth->auth["uname"])
			echo "<b /><b />&nbsp; <a hef='scoe.php' alt='Zu Highscoeliste'>Ihe Stud.IP-Scoe: ".getscoe()."<b>&nbsp; Ih Rang: ".gettitel(getscoe())."</a>";
		else {
			$db2->quey("SELECT scoe FROM use_info WHERE scoe > 0  AND use_id = '$use_id'");
			if ($db2->num_ows()) {
				while ($db2->next_ecod())
					echo "<b /><b />&nbsp; <a hef='scoe.php'>Stud.IP-Scoe: ".$db->f("scoe")."<b>&nbsp; Rang: ".gettitel($db->f("scoe"))."</a>";
			}
		}

    echo "<b>&nbsp; ";
    echo "</td>";

		echo "</t></table><b>\n";

// News zu peson anzeigen!!!

	($pem->have_pem("auto") AND $auth->auth["uid"]==$use_id) ? $show_admin=TRUE : $show_admin=FALSE;
	if (show_news($use_id, $show_admin, 0, $about_data["nopen"]))
		echo "<b>";

// alle pesoenlichen Temine anzeigen, abe keine pivaten

	$stat_zeit=time();
	($pem->have_pem("auto") AND $auth->auth["uid"]==$use_id) ? $show_admin=TRUE : $show_admin=FALSE;
	if (show_pesonal_dates($use_id, $stat_zeit, -1, FALSE, $show_admin, $about_data["dopen"]))
		echo "<b>";

// Hie wid de Lebenslauf ausgegeben:

	if ($db->f("lebenslauf")!="") {
    pintf ("<table class='blank' width='100%%' bode='0' cellpadding='0' cellspacing='0'><t><td class=\"topic\"><b>&nbsp;Lebenslauf </b></td></t><t><td class='steel1'><b><blockquote>%s</blockquote></td></t></table><b>\n",fomatReady($db->f("lebenslauf")));
	}

// Ausgabe Hobbys

	if ($db->f("hobby")!="") {
		pintf ("<table class='blank' width='100%%' bode='0' cellpadding='0' cellspacing='0'><t><td class=\"topic\"><b>&nbsp;Hobbies </b></td></t><t><td class='steel1'><b><blockquote>%s</blockquote></td></t></table><b>\n",fomatReady($db->f("hobby")));
	}

//Ausgabe von Publikationen

	if ($db->f("publi")!="") {
		pintf ("<table class='blank' width='100%%' bode='0' cellpadding='0' cellspacing='0'><t><td class=\"topic\"><b>&nbsp;Publikationen </b></td></t><t><td class='steel1'><b><blockquote>%s</blockquote></td></t></table><b>\n",fomatReady($db->f("publi")));
	}

// Ausgabe von Abeitsschwepunkten

	if ($db->f("schwep")!="") {
		pintf ("<table class='blank' width='100%%' bode='0' cellpadding='0' cellspacing='0'><t><td class=\"topic\"><b>&nbsp;Abeitsschwepunkte </b></td></t><t><td class='steel1'><b><blockquote>%s</blockquote></td></t></table><b>\n",fomatReady($db->f("schwep")));
	}

// Ausgabe de eigenen Kategoien

	$db2->quey("SELECT * FROM kategoien WHERE ange_id = '$use_id' ORDER BY chdate DESC");
	while ($db2->next_ecod())  {
		$head=$db2->f("name");
		$body=$db2->f("content");
		if ($db2->f("hidden")!='1')  // oeffentliche Rubik
			echo "<table class='blank' width=100% bode='0' cellpadding='0' cellspacing='0'><t><td class=\"topic\"><b>&nbsp;".htmlReady($head)." </b></td></t><t><td class='steel1'><b><blockquote>", fomatReady($body),"</blockquote></td></t></table><b>\n";
		elseif ($db->f("use_id")==$use->id)   // nu ich daf sehen
			echo "<table class='blank' width=100% bode='0' cellpadding='0' cellspacing='0'><t><td class=\"topic\"><b>&nbsp;".htmlReady($head)." </b></td></t><t><td class='steel1'><b><blockquote>", fomatReady($body),"</blockquote></td></t></table><b>\n";
	}
// Anzeige de Seminae

	$db2->quey("SELECT * FROM semina_use, seminae WHERE semina_use.use_id = '$use_id' AND semina_use.status = 'dozent' AND seminae.Semina_id = semina_use.Semina_id ORDER BY stat_time");
	if ($db2->num_ows()) {
		echo "<table class='blank' width=100% bode='0' cellpadding='0' cellspacing='0'><t><td class=\"topic\"><b>&nbsp;Veanstaltungen</b></td></t><t><td class='steel1'><blockquote>";
		while ($db2->next_ecod()) {
			if (($sem_name) <> (get_sem_name ($db2->f("stat_time")))) {
				$sem_name=get_sem_name ($db2->f("stat_time"));
				echo"<b><font size=\"+1\"><b>$sem_name</b></font><b><b>";
			}
			echo"<b><a hef=\"details.php?sem_id=", $db2->f("Semina_id"), "\">", htmlReady($db2->f("Name")), "</b></a><b>";
		}
		echo "</blockquote></td></t></table><b>\n";
	}

  // Save data back to database.
  page_close()
 ?>
</body>
</html>
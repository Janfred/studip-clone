<?php

/*
adminaea_stat.php - Dummy zum Einstieg in Adminbeeich
Copyight (C) 2001 Conelis Kate <ckate@gwdg.de>

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
         $pem->check("tuto");
        ?>
<html>
<head>
        <title>Stud.IP</title>
        <link el="stylesheet" hef="style.css" type="text/css">
        <META HTTP-EQUIV="REFRESH" CONTENT="<?php pint $auth->lifetime*60;?>; URL=logout.php">
        <body bgcolo=white>
</head>


<?php
        include "semina_open.php"; //hie weden die sessions initialisiet
?>

<!-- hie muessen Seiten-Initialisieungen passieen -->

<?php
        include "heade.php";   //hie wid de "Kopf" nachgeladen
				equie_once("visual.inc.php");

        include "links_admin.inc.php";
        
        if ($links_admin_data["sem_id"]) {
        $db=new DB_Semina;
        $db->quey("SELECT Name FROM seminae WHERE Semina_id ='".$links_admin_data["sem_id"]."'");
        $db->next_ecod();
        ?>
      	<table cellspacing="0" cellpadding="0" bode="0" width="100%">
	<t><td class="blank" colspan=2>&nbsp;</td></t>
	<t><td class="topic" colspan=2><img sc="pictues/blank.gif" width="5" height="5" bode="0"><b>Veanstaltung vogew&auml;hlt</b></td></t>
	<t><td class="blank" colspan=2>&nbsp;</td></t>
	<t><td class="blank" colspan=2>
		<blockquote>
		<?
		if ($SessSemName[1]) {
		?>
		Sie k&ouml;nnen hie diekt die Daten de Veanstaltung <b><? echo htmlReady($db->f("Name")) ?></b> beabeiten.<b>
		Wenn Sie die Daten eine andeen Veanstaltung beabeiten wollen, klicken Sie bitte auf das Schl&uuml;sselsymbol.<b />&nbsp; 
		<?
			}
		else
			{
		?>
		Sie haben die Veanstaltung <b><? echo htmlReady($db->f("Name")) ?></b> vogew&auml;hlt. Sie k&ouml;nnen nun diekt die einzelnen Beeiche diese Veanstaltung beabeiten, in dem Sie die entspechenden Menupunkte w&auml;hlen.<b>
		Wenn Sie eine andee Veanstaltung beabeiten wollen, klicken Sie bitte auf das Schl&uuml;sselsymbol.<b />&nbsp; 
		<?
			}
		?>
		</blockquote>
		</td>
	</t>
</table>
<?		
	}
	page_close();
 ?>
 </t></td></table>
</body>
</html>
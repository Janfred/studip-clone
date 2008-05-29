<?php
# Lifter002: TODO
  
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("root");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

## Include this, if you do not use auto_prepend
include($_PHPLIB["libdir"] . "table.inc");

## We need a database connection and a table object for later...
$db = new DB_Seminar;
$t  = new Table;
$t->heading = "on";
$t->check   = "sid";

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins
?>

<table border=0 align="center" cellspacing=0 cellpadding=0 width=100%>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;<?=_("Im System gespeicherte Sessions")?></b></td>
</tr>
<tr>
	<td class="blank" colspan=2>
		&nbsp;
	</td>
</tr>

<?php
##
## Act on submission
##

## Handle gc: manual garbage collection
if( !isset( $gc ) ) { $gc = ""; };
if ($gc != "") {
    parse_msg ("msg§" . _("Manueller Garbage-Collect ausgef&uuml;hrt..."));
  $sess->gc();  
}

if( !isset( $del ) ) { $del = false; };
if ($del) {
  $sum = 0;

  if (is_array($sid)) {
    reset($sid);
    while(list($k, $v) = each($sid)) {
      $query = sprintf("delete from %s where name = '%s' and sid = '%s'",
                 $sess->that->database_table,
                 $sess->name,
                 $v);
      $db->query($query);
      $sum += $db->affected_rows();
    }
  }
  
  parse_msg ("msg§" . sprintf(_("%s Sessions gel&ouml;scht..."), $sum) . "§");
  
}

##
## Generate form
##

?>
<tr><td class="blank" colspan=2 align=center>
<form method=post action="<?php $sess->pself_url() ?>">

<table width=60%>
 <tr >
  <td  class="blank" >
  <input type="submit" name="refresh" value="<?=_("Neu laden")?>">&nbsp; 
  <input type="submit" name="gc"      value="<?=_("Garbage Collect")?>">&nbsp; 
  <input type="submit" name="del"     value="<?=_("ausgew&auml;hlte Session l&ouml;schen")?>">
 </tr>
</table>
<?php
    
  $query = sprintf("select sid, name, changed from active_sessions where name = '%s' order by name asc, changed desc",
              $sess->name);
  $db->query($query);

  $t->show_result($db, "data");
?>
<table width=60%>
 <tr >
  <td  class="blank" >
  <input type="submit" name="refresh" value="<?=_("Neu laden")?>">&nbsp; 
  <input type="submit" name="gc"      value="<?=_("Garbage Collect")?>">&nbsp; 
  <input type="submit" name="del"     value="<?=_("ausgew&auml;hlte Session l&ouml;schen")?>">
 </tr>
</table>
</form>
</td></tr></table>

<?php 

include ('lib/include/html_end.inc.php');
page_close();
//<!-- $Id$ -->
?>
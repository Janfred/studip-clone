<?php
## Include this, if you do not use auto_prepend

  include($_PHPLIB["libdir"] . "table.inc");
  
  page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
  $perm->check("root");

  ## We need a database connection and a table object for later...
  $db = new DB_Seminar;
  $t  = new Table;
  $t->heading = "on";
  $t->check   = "sid";
?>
<html>
<head>
 <title>Stud.IP</title>
 	<link rel="stylesheet" href="style.css" type="text/css">
</head>

<body bgcolor="#ffffff">

<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert
?>

<!-- hier muessen Seiten-Initialisierungen passieren -->

<?php 
	include "header.php";   //hier wird der "Kopf" nachgeladen 
?>
<body>

<?php
include "links_admin.inc.php";  //Linkleiste fuer admins
?>

<table border=0 align="center" cellspacing=0 cellpadding=0 width=100%>
<tr><td class="blank" colspan=2>&nbsp;</td></tr>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;Im System gespeicherte Sessions</b></td>
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
    parse_msg ("msgºManueller Garbage-Collect ausgef&uuml;hrt...");
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
  
  parse_msg ("msgº$sum Sessions gel&ouml;sct...º");
  
}

##
## Generate form
##

?>
<tr><td class="blank" colspan=2 align=center>
<form method=post action="<?php $sess->pself_url() ?>">

<table width=50%>
 <tr >
  <td  class="blank" >
  <input type="submit" name="refresh" value="Neu laden">&nbsp; 
  <input type="submit" name="gc"      value="Garbage Collect">&nbsp; 
  <input type="submit" name="del"     value="ausgew&auml;hlte Session l&ouml;schen">
 </tr>
</table>
<?php
    
  $query = sprintf("select sid, name, changed from active_sessions where name = '%s' order by name asc, changed desc",
              $sess->name);
  $db->query($query);

  $t->show_result($db, "data");
?>
<table width=50%>
 <tr >
  <td  class="blank" >
  <input type="submit" name="refresh" value="Neu laden">&nbsp; 
  <input type="submit" name="gc"      value="Garbage Collect">&nbsp; 
  <input type="submit" name="del"     value="ausgew&auml;hlte Session l&ouml;schen">
 </tr>
</table>
</form>
<?php page_close() ?>
</td></tr></table>
</body>
</html>
<!-- $Id$ -->
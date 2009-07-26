<?
$infobox=array();
$infobox['picture']='infoboxbild_studygroup.jpg';
$infobox['content']=array(
        array(
        'kategorie'=>_("Information"),
        'eintrag'=>array(
            array("text"=>"Studiengruppen sind eine einfache Möglichkeit, mit Kommilitonen, Kollegen und anderen zusammenzuarbeiten. Jeder kann Studiengruppen gründen.","icon"=>"ausruf_small2.gif"))),
        array(
        'kategorie'=>_("Aktionen"),
        'eintrag'=>array(
            array("text"=>"Neue Studiengruppe gründen", "icon"=>"icon-cont.gif"),
	    array("text"=>"Studiengruppe löschen", "icon"=>"icon-wiki.gif"))),
     );

?>

<?= $this->render_partial("course/studygroup/_feedback") ?>
<h1><?= _("Arbeitsgruppe anlegen") ?></h1>

<form action="<?= $controller->url_for('course/studygroup/create') ?>" method=post>

<table class="blank" width="75%" cellspacing="5" cellpadding="0" border="0" style="margin-left:75px; margin-right:300px;">

<tr>
  <td style='text-align:right; font-size:150%;'>Name:</td>
  <td style='font-size:150%;'><input type='text' name='groupname' size='25' value='<?=$this->flash['request']['groupname']?>' style='font-size:100%'></td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'>Beschreibung:</td>
  <td><textarea name='groupdescription' rows=5 cols=50><?=$this->flash['request']['groupdescription']?><?=_("Hier aussagekräftige Beschreibung eingeben.")?></textarea></td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'>Module:</td>
  <td>
    <input name='groupmodule_forum' type=checkbox <?= ($this->flash['request']['groupmodule_forum']) ? 'checked' : '' ?>> Forum<br/>
    <input name='groupmodule_files' type=checkbox <?= ($this->flash['request']['groupmodule_files']) ? 'checked' : ''?>> Dateibereich<br/>
    <input name='groupmodule_wiki' type=checkbox <?= ($this->flash['request']['groupmodule_wiki']) ? 'checked' : ''?>> Wiki<br/>
    <input name='groupmodule_literature' type=checkbox <?= ($this->flash['request']['groupmodule_literature']) ? 'checked' : ''?>> Literatur
  </td>
</tr>

<tr>
  <td style='text-align:right;'></td>
</tr>

<tr>
  <td style='text-align:right;'>Zugang:</td>
  <td>
      <select size=0 name="groupaccess">
         <option value="all">Offen für alle
         <option value="invite">Auf Anfrage
      </select>
  </td>
</tr>

<tr>
  <td style='text-align:right;'></td>
  <td>&nbsp;</td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'>Nutzungsbedingungen:</td>
  <td>
  	<p>
		<em><?= formatReady( $terms ) ?></em>
	</p>
	<p>
		<input type=checkbox name="grouptermsofuse_ok"> Einverstanden
	</p>
  </td>
</tr>


<tr>
  <td></td>
  <td><input type='submit' value="Änderungen übernehmen"></td>
</tr>

</table>
</form>




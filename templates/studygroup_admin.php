<?if (sizeof($messages)):?>
<table>
<?=parse_msg_array($messages, '', 1, false)?>
</table>
<?endif;?>

<form action="<?=$GLOBALS['PHP_SELF']?>" method=post>
<input type='hidden' name='group_new' value='1'>

<table class="blank" width="75%" cellspacing="5" cellpadding="0" border="0" style="margin-left:75px; margin-right:300px;">
<tr><td colspan=2><h1><?=$current_page?></h1></td></tr>
<tr>
  <td style='text-align:right; font-size:150%;'>Name:</td>
  <td style='font-size:150%;'><input type='text' name='groupname' size='25' value='<?=$groupname?>' style='font-size:100%'></td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'>Beschreibung:</td>
  <td><textarea name='groupdescription' rows=5 cols=50><?=$groupdescription?></textarea></td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'>Module:</td>
  <td>
    <input name='groupmodule_forum' type=checkbox <?= ($groupmodule_forum) ? 'checked' : '' ?>> Forum<br/>
    <input name='groupmodule_files' type=checkbox <?= ($groupmodule_files) ? 'checked' : ''?>> Dateibereich<br/>
    <input name='groupmodule_wiki' type=checkbox <?= ($groupmodule_wiki) ? 'checked' : ''?>> Wiki<br/>
    <input name='groupmodule_literature' type=checkbox <?= ($groupmodule_literature) ? 'checked' : ''?>> Literatur
    <!--<input name='groupmodule_members' type=checkbox <?= ($groupmodule_members) ? 'checked' : ''?>> Mitgliederliste-->
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
         <option value="invite">Auf Einladung
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
    <p><em>
    Mir ist bekannt, dass ich die Gruppe nicht zu rechtswidrigen Zwecken nutzen darf. Dazu zählen u.a. Urheberrechtsverletzungen, Beleidigungen und andere Persönlichkeitsdelikte.
   </em></p>
   <p><em>
    Ich erkläre mich damit einverstanden, dass AdministratorInnen des virtUOS die Inhalte der Gruppe zu Kontrollzwecken einsehen dürfen.
   </em></p>
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

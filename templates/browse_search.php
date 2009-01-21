<script type="text/javascript">
	Event.observe(window, 'load', function() {
		new Ajax.Autocompleter('Vorname',
		                       'Vorname_choices',
		                       'dispatch.php/autocomplete/person/given',
		                       { minChars: 3, paramName: 'value', method: 'get' });
		new Ajax.Autocompleter('Nachname',
		                       'Nachname_choices',
		                       'dispatch.php/autocomplete/person/family',
		                       { minChars: 3, paramName: 'value', method: 'get',
		                         afterUpdateElement: function (input, item) {
		                           var username = encodeURI(item.down('span.username').firstChild.nodeValue);
		                           document.location = "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>about.php?username=" + username;
		                         }});
	});
</script>
<form action="browse.php" method="post">
<input type="hidden" name="send" value="TRUE">
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr>
	<td class="topic"><b><?=_("Suche nach Personen")?></b></td>
</tr>
<? if($sms_msg):?>
<tr>
	<td class="blank"><? parse_msg($sms_msg); ?></td>
</tr>
<? endif; ?>
</table>
<!-- form zur wahl der institute -->
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr class="steel1">
	<td width="10%">
		<b><?=_("in Einrichtungen:")?></b>
	</td>
	<td colspan="3">
	<select name="inst_id" size="1" style="min-width: 400px;">
		<option value="0">- - -</option>
	<? foreach ($institutes as $institut): ?>
		<option value="<?=$institut['id']?>"<? if($institut['id']==$browse_data['inst_id']):?> selected="selected"<? endif; ?>><?=$institut['name']?></option>
	<? endforeach;?>
	</select></td>
</tr>
<!-- form zur wahl der seminare -->
<tr class="steelgraulight">
	<td><b><?=_("in Veranstaltungen:")?></b>
	</td>
  	<td colspan="3">
  	<select name="sem_id" size="1" style="min-width: 400px;">
		<option value="0">- - -</option>
	<? foreach ($courses as $course): ?>
		<option value="<?=$course['id']?>"<? if($course['id']==$browse_data['sem_id']):?> selected="selected"<? endif; ?>><?=$course['name']?></option>
	<? endforeach;?>
	</select></td>
</tr>
<!-- form zur freien Suche -->
<tr class="steel1">
	<td><b><?=_("Vorname:")?></b></td>
  	<td>
		<input id="Vorname" type="text" style="width: 200px" size="10" length="255" name="Vorname" value="<? echo htmlReady(stripslashes($browse_data["Vorname"])) ?>">
		<div id="Vorname_choices" class="autocomplete"></div>
	</td>
	<td>
		<b><?=_("Nachname:")?></b>
	</td>
	<td>
		<input id="Nachname" type="text" style="width: 200px" size="10" maxlength="255" name="Nachname" value="<? echo htmlReady(stripslashes($browse_data["Nachname"])) ?>">
		<div id="Nachname_choices" class="autocomplete"></div>
	</td>
</tr>
<tr class="steelgroup4">
	<td colspan="4" align="center">
		<?=makeButton("suchen", "input", "Suchen", "Suchen")?>
		<?=makeButton("zuruecksetzen", "input", "zuruecksetzen", "zuruecksetzen")?>
	</td>
</tr>
</table>
</form>
<br/>

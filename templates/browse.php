<!-- SEARCHBOX -->
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
<div class="topic"><b><?=_("Suche nach Personen")?></b></div>

<? if($sms_msg):?>
<? parse_msg($sms_msg); ?>
<? endif; ?>

<!-- form zur wahl der institute -->
<div style="width: 100%;">
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<? if (count($institutes)): ?>
<tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
	<td style="white-space: nowrap;">
		<b><?=_("in Einrichtungen:")?></b>
	</td>
	<td width="90%">
	<select name="inst_id" size="1" style="min-width: 200px;">
		<option value="0">- - -</option>
	<? foreach ($institutes as $institut): ?>
		<option value="<?=$institut['id']?>"<? if($institut['id']==$browse_data['inst_id']):?> selected="selected"<? endif; ?>><?=$institut['name']?></option>
	<? endforeach;?>
	</select></td>
</tr>
<? endif ?>
<!-- form zur wahl der seminare -->
<? if (count($courses)): ?>
<tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
	<td style="white-space: nowrap;">
		<b><?=_("in Veranstaltungen:")?></b>
	</td>
	<td width="90%">
  	<select name="sem_id" size="1" style="min-width: 200px;">
		<option value="0">- - -</option>
	<? foreach ($courses as $course): ?>
		<option value="<?=$course['id']?>"<? if($course['id']==$browse_data['sem_id']):?> selected="selected"<? endif; ?>><?=$course['name']?></option>
	<? endforeach;?>
	</select></td>
</tr>
<? endif ?>
<!-- form zur freien Suche -->
<tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
	<td><b><?=_("Vorname:")?></b></td>
	<td width="90%">
		<input id="Vorname" type="text" style="width: 200px" size="10" length="255" name="Vorname" value="<? echo htmlReady(stripslashes($browse_data["Vorname"])) ?>">
		<div id="Vorname_choices" class="autocomplete"></div>
	</td>
</tr>
<tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
	<td><b><?=_("Nachname:")?></b></td>
	<td width="90%">
		<input id="Nachname" type="text" style="width: 200px" size="10" maxlength="255" name="Nachname" value="<? echo htmlReady(stripslashes($browse_data["Nachname"])) ?>">
		<div id="Nachname_choices" class="autocomplete"></div>
	</td>
</tr>
<tr class="steel2">
	<td colspan="2" align="center">
		<?=makeButton("suchen", "input", "Suchen", "Suchen")?>
		<?=makeButton("zuruecksetzen", "input", "zuruecksetzen", "zuruecksetzen")?>
	</td>
</tr>
</table>
</div>
</form>
<br/>

<!-- RESULTS -->
<? if($results):?>

<div class="topic"><b><?=_("Ergebnisse:")?></b></div>
<div style="width: 100%;">
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<? if(count($results) > 0):?>
<tr>
<? if($browse_data['group'] == 'Seminar'): ?>
	<th align="left"><a href="browse.php?sortby=Nachname"><?=_("Name")?></a></th>
	<th align="left"><a href="browse.php?sortby=status"><?=_("Status in der Veranstaltung")?></a></th>
<? elseif($browse_data['group'] == 'Institut'): ?>
	<th align="left"><a href="browse.php?sortby=Nachname"><b><?=_("Name")?></a></th>
	<th align="left"><?=_("Funktion an der Einrichtung")?></td>
<? else: ?>
	<th align="left"><a href="browse.php?sortby=Nachname"><b><?=_("Name")?></a></th>
	<th align="left"><a href="browse.php?sortby=perms"><?=_("globaler Status")?></a></th>
<? endif; ?>
	<th align="right"><?=_("Nachricht verschicken")?></td>
</tr>
<? foreach ($results as $user): ?>
<tr class="<?=TextHelper::cycle('cycle_odd', 'cycle_even')?>">
	<td><a href="about.php?username=<?=$user['username']?>"><?=$user['fullname']?></a></td>
	<td><?=$user['status']?> <?=$user['perms']?></td>
	<td align="right">
		<?=$user['chat']?>
		<a href="sms_send.php?sms_source_page=browse.php&rec_uname=<?=$user['username']?>"><img src="<?=Assets::url()?>images/nachricht1.gif" title="<?=_("Nachricht an User verschicken")?>" border="0"></a>
	</td>
</tr>
<? endforeach; ?>
<? else: ?>
<tr class="steel1">
	<td colspan="3"><p><b><?=_("Es wurde niemand gefunden!")?></b></p></td>
</tr>
<? endif; ?>
</table>
</div>
<? endif; ?>

<?php
$infobox = array(
	'picture' => 'board2.jpg',
	'content' => array(
		array("kategorie" => _("Information:"),
			"eintrag" => array(
				array(
					"icon" => 'ausruf_small.gif',
					"text" => _("Hier können Sie die Homepages aller NutzerInnen abrufen, die im System registriert sind.")
				),
				array(
					"icon" => 'ausruf_small.gif',
					"text" => _("Sie erhalten auf den Homepages von MitarbeiternInnen an Einrichtungen auch weiterf&uuml;hrende Informationen, wie Sprechstunden und Raumangaben.")
				),
				array(
					"icon" => 'ausruf_small.gif',
					"text" => _("Wählen Sie den gewünschten Bereich aus oder suchen Sie nach einem Namen!")
				)
			)
		),
		array("kategorie" => _("Ansichten:"),
			"eintrag" => array(
				array(
					"icon" => 'suche2.gif',
					"text" => '<a href="score.php">'._("Zur Stud.IP-Rangliste").'</a>'
				)
			)
		)
	)
);
?>

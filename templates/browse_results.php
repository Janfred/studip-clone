<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr>
	<td class="topic"><b><?=_("Ergebnisse:")?></b></td>
</tr>
</table>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<? if(count($results) > 0):?>
<tr>
<? if($browse_data['group'] == 'Seminar'): ?>
	<th><a href="browse.php?sortby=Nachname"><?=_("Name")?></a></th>
	<th><a href="browse.php?sortby=status"><?=_("Status in der Veranstaltung")?></a></th>
<? elseif($browse_data['group'] == 'Institut'): ?>
	<th><a href="browse.php?sortby=Nachname"><b><?=_("Name")?></a></th>
	<th><?=_("Funktion an der Einrichtung")?></td>
<? else: ?>
	<th><a href="browse.php?sortby=Nachname"><b><?=_("Name")?></a></th>
	<th><a href="browse.php?sortby=perms"><?=("globaler Status")?></a></th>
<? endif; ?>
	<th><?=_("Nachricht verschicken")?></td>
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

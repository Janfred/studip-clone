<div class="topic"><b><?=_("Stud.IP-Rangliste")?></b></div>
<? if(count($persons)>0): ?>
<div style="width: 100%;">
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr>
	<th width="3%" align="left"><?=_("Platz")?></th>
	<th width="1%"></th>
	<th align="left"><?=_("Name")?></th>
	<th align="left"></th>
	<th align="left"><?=_("Score")?></th>
	<th align="left"><?=_("Titel")?></th>
</tr>
<? foreach ($persons as $index=>$person): ?>
<tr class="<?=TextHelper::cycle('cycle_odd', 'cycle_even')?>">
	<td align="right"><?=$index+1?>. </td>
	<td> <?=$person['avatar']?></td>
	<td><a href="<?=URLHelper::getLink("about.php?username=". $person['username'])?>"><?=$person['name']?></a></td>
	<td><?=$person['content']?></td>
	<td><?=$person['score']?></td>
	<td><?=$person['title']?> <? if($person['userid']==$user->id): ?><a href="<?=URLHelper::getLink('score.php?cmd=kill')?>"><?=_("[löschen]")?></a><? endif; ?></td>
</tr>
<? endforeach;?>
</table>
</div>
<? endif; ?>

<?php
if ($score->ReturnPublik())
{
	$action = '<a href="'. URLHelper::getLink('score.php?cmd=kill') .'">'._("Ihren Wert von der Liste löschen").'</a>';
}
else
{
	$action = '<a href="'. URLHelper::getLink('score.php?cmd=write') .'">'._("Diesen Wert auf der Liste veröffentlichen").'</a>';
}
$infobox = array(
	'picture' => 'board2.jpg',
	'content' => array(
		array("kategorie" => _("Ihr Score: ").$score->ReturnMyScore()),
		array("kategorie" => _("Ihr Titel: ").$score->ReturnMyTitle()),
		array("kategorie" => _("Information:"),
			"eintrag" => array(
				array(
					"icon" => 'ausruf_small.gif',
					"text" => _("Auf dieser Seite können Sie abrufen, wie weit Sie im Stud.IP-Score aufgestiegen sind. Je aktiver Sie sich im System verhalten, desto höher klettern Sie!")
				),
				array(
					"icon" => 'ausruf_small.gif',
					"text" => _("Sie erhalten auf den Homepages von MitarbeiternInnen an Einrichtungen auch weiterführende Informationen, wie Sprechstunden und Raumangaben.")
				)
			)
		),
		array("kategorie" => _("Aktionen:"),
			"eintrag" => array(
				array(
					"icon" => 'suche2.gif',
					"text" => $action
				)
			)
		)
	)
);
?>

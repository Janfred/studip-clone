<table class="blank" cellpadding="5px">
<tr>
<td>
<?=parse_msg_array($msgs, '', 1, false);?>
<h1><?=$groupname?></h1>
<p><?=_("Moderiert von:")?>
<?$first=1; foreach($moderators as $m):?>
   <?=$first?'':', '?>
   <?=$m['fullname'];?>
   <?$first=0;?>
<?endforeach;?>
</p>
<p><em><?=$groupdescription?></em></p>
</td>
</tr>
<tr>
<td>
<h2><?=_("Mitglieder");?></h2>
<table cellpadding=4>
<?$count=0; foreach($members as $m):?>
   <?=($count%5==0) ? '<tr><td valign=middle>' : '<td valign=middle>'?>
	<font size="-1">
	<a href="<?= URLHelper::getLink('about.php?username='.$m['username']); ?>">
	<?= Avatar::getAvatar($m['user_id'])->getImageTag(Avatar::SMALL); ?>
	<?= htmlReady($m['fullname']) ?>
	</a>
	</font>
   <?=(($count+1)%5==0) ? '</td></tr>' : '</td>'?>
   <?$count++;?>
<?endforeach;?>
</table>
</td>
</tr>
<? if (count($accepted)>0): ?>
<tr>
<td>
<h2><?=_("Offene Mitgliedsanträge");?></h2>
<table>
<?foreach($accepted as $p):?>
	<tr><td><font size="-1">
	<a href="<?= URLHelper::getLink('about.php?username='.$p['username']); ?>">
	<?= Avatar::getAvatar($p['user_id'])->getImageTag(Avatar::SMALL); ?>
	<?= htmlReady($p['fullname']) ?>
	</a> </td> 
	<td style='padding-left:1em;'><a href="<?=URLHelper::getLink($GLOBALS['PHP_SELF'].'?accept='.$p['username'])?>"><img src="<?=$ASSETS_URL?>images/haken_transparent.gif"></a></td>
	<td style='padding-left:1em;'><a href="<?=URLHelper::getLink($GLOBALS['PHP_SELF'].'?deny='.$p['username'])?>"><img src="<?=$ASSETS_URL?>images/x_transparent.gif"></a></td>
        </tr>
<?endforeach;?>
</table>
</td>
</tr>
<? endif; ?>

</table>


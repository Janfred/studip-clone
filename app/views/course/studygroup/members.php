<script>
if (typeof STUDIP == "undefined" || !STUDIP) {
	var STUDIP = {};
}

STUDIP.Arbeitsgruppen = {


	toggleOption: function(user_id) {
		if($('user_opt_' + user_id).visible()) {
			$('user_opt_' + user_id).fade({duration: 0.4});
			$('user_' + user_id).morph('width:0px;', { queue: 'end', duration: 0.3 })

		} else{
			$('user_' + user_id).morph('width:110px;', {duration: 0.3})  
				$('user_opt_' + user_id).appear({ queue: 'end', duration: 0.4 });
		}
	},

		showToolTip: function(elemId) {
			$(elemId).show();

		/*
			$('bubble_text').innerHTML = '<?= _("klicken, f?r Optionen") ?>';

		var pos = String.split($(elemId).cumulativeOffset(), ',');

		$('bubble').style.top = (pos[1] - 140) + 'px'; 
		$('bubble').style.left  = (pos[0] -20) + 'px';
		$('bubble').show();
		 */

		},

			hideToolTip: function(elemId) {
				$(elemId).hide();

				// $('bubble').hide();
			}

}
</script>
<!--
<div id="bubble" style="position: absolute; display: none; z-index: 10000">
	<?= Assets::img('bubble.png', array('width' => '100')) ?>
	<div id="bubble_text" style="position: absolute; top: 15px; left: 20px;">
	</div>
</div>-->
<?
global $perm;
if($perm->have_studip_perm('tutor',$sem_id)) {
	$text  =  _('Hier können Sie je nach die Teilnehmer der Studiengruppen einsehen und verwalten. Teilnehmer können je nach Status zu einem Moderator hoch oder runtergestuft werden und aus der Studiengruppe entlassen werden.');
	$aktionen = array(
		'kategorie'=>_("Aktionen"), 
		'eintrag'=>array(
			array("text"=>_("Klicken Sie auf ein Gruppenmitglied um entsprechende Aktionen auszuführen"), "icon"=>"icon-cont.gif")));
} else {
	$text = _('Studiengruppen sind eine einfache Möglichkeit, mit Kommilitonen, Kollegen und anderen zusammenzuarbeiten. Jeder kann Studiengruppen');
	$aktionen = array();
}

$infobox=array();
$infobox['picture']='infoboxbild_studygroup.jpg';
$infobox['content']=array(
	array(
		'kategorie'=>_("Information"), 
		'eintrag'=>array(
			array("text"=>$text,"icon"=>"ausruf_small2.gif"))),
	$aktionen,
);



?>
<?= $this->render_partial("course/studygroup/_feedback") ?>

<h1><?=_("Mitglieder");?></h1>

<? $count=0; foreach($members as $m): ?>
	 <!-- <?=($count%5==0) ? '<tr><td valign=middle>' : '<td valign=middle>'?> -->

<div style="float:left;position:relative" align="left" valign="top" 
	onMouseOver="STUDIP.Arbeitsgruppen.showToolTip('pic_<?=$m['user_id']?>')"
	onMouseOut ="STUDIP.Arbeitsgruppen.hideToolTip('pic_<?=$m['user_id']?>')"
	onClick    ="STUDIP.Arbeitsgruppen.toggleOption('<?=$m['user_id']?>')" title="klicken f?r weitere Optionen">

	 <? if (!in_array($m,$moderators)) : ?>
	<div id='pic_<?=$m['user_id']?>' style="display:none;position:absolute;top:80px;left:60px;width:10px;height:10px">
		<?= Assets::img('einst2')?>
	</div>
	<? endif; ?>

	 <div style="float:left">
	 <? if (in_array($m,$moderators)) : ?>
	<?= Avatar::getAvatar($m['user_id'])->getImageTag(Avatar::MEDIUM); ?>
	 <? else : ?>
	<?= Avatar::getAvatar($m['user_id'])->getImageTag(Avatar::MEDIUM, array("title" => _("Klicken, f?r weitere Optionen"))); ?>
	<? endif; ?>
	<br>
	</div>

	<? if ($rechte && !in_array($m,$moderators) ) : ?>
	<!-- Opitonsbereich -->
	<noscript>
		<div id='user_<?=$m['user_id']?>' style="float:left; margin-right: 10px; width: 110px;" align="left" valign="top">
			<div id="user_opt_<?=$m['user_id']?>">
			<div class="blue_gradient" style="text-align: center"><?=_('Optionen')?></div>
			<br>
			<?  if ( in_array($m,$moderators)) :?>

			<? elseif ( in_array($m,$tutors)): ?>
				<!--<a href="<?=$controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['user_id'].'/promote/tutor')?>" alt="Nutzer bef?rdern">Nutzer bef?rdern</a> 
					<br> -->
					&nbsp;<a href="<?=$controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['user_id'].'/promote/autor')?>" alt="Nutzer runterstufen">
						<?= makebutton('runterstufen') ?>
					</a>
				<? else :?>
					&nbsp;<a href="<?=$controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['user_id'].'/promote/tutor')?>" alt="Nutzer bef?rdern">
						<?= makebutton('hochstufen') ?>
					</a><br>
					<br>
					&nbsp;<a href="<?=$controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['user_id'].'/remove')?>" alt="Nutzer runterstufen">
						<?= makebutton('rauswerfen') ?>
					</a>
				<? endif;?> 
		</div>
	</noscript>

	<div id='user_<?=$m['user_id']?>' style="float:left; margin-right: 10px; width: 0px;" align="left" valign="top">
		<div id="user_opt_<?=$m['user_id']?>" style="display: none">
			<div class="blue_gradient" style="text-align: center"><?=_('Optionen')?></div>
			<br>
			<?  if ( in_array($m,$moderators)) :?>

			<? elseif ( in_array($m,$tutors)): ?>
				<!--<a href="<?=$controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['user_id'].'/promote/tutor')?>" alt="Nutzer bef?rdern">Nutzer bef?rdern</a> 
				<br> -->
				&nbsp;<a href="<?=$controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['user_id'].'/promote/autor')?>" alt="Nutzer runterstufen">
					<?= makebutton('runterstufen') ?>
				</a>
			<? else :?>
				&nbsp;<a href="<?=$controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['user_id'].'/promote/tutor')?>" alt="Nutzer bef?rdern">
					<?= makebutton('hochstufen') ?>
				</a><br>
				<br>
				&nbsp;<a href="<?=$controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['user_id'].'/remove')?>" alt="Nutzer runterstufen">
					<?= makebutton('rauswerfen') ?>
				</a>
			<? endif;?> 
		</div>

	</div>
	<? endif; // Optionsbereich ?>

	<div style="clear: both; margin-right: 25px;">
	<a href="<?= URLHelper::getLink('about.php?username='.$m['username']); ?>">
		<?= htmlReady($m['fullname']) ?><br>
		<?  if ( in_array($m,$moderators)) :?>
		<em><?= _("Gruppengründer") ?></em>
		<? elseif ( in_array($m,$tutors)) :?>
		<em><?= _("Moderator") ?></em>
		<? endif;?>
		</a>
	</div>
</div>

	<?=(($count+1)%5==0) ? '</td></tr>' : '</td>'?>
	<?$count++;?>
<?endforeach;?>

<? if (count($accepted)>0): ?>

<? if ($rechte):   ?>    
<? $cssSw = new cssClassSwitcher() ?>
<p style="clear:both">
<h2><?=_("Offene Mitgliedsanträge");?></h2>
	<table cellspacing="0" cellpadding=2" border="0" style="max-width: 100%; min-width: 70%">
		<tr>
			<th colspan=2" width="70%">
				<?= _("Name") ?>
			</th>
			<th colspan="2" width="30%">
				<?= _("Aktionen") ?>
			</th>
		</tr>
<? foreach($accepted as $p): 
$cssSw->switchClass();
?>
		<tr class="<?= $cssSw->getClass() ?>">
			<td>
				<a href="<?= URLHelper::getLink('about.php?username='.$p['username']); ?>">
					<?= Avatar::getAvatar($p['user_id'])->getImageTag(Avatar::SMALL); ?>
				</a>
			</td> 
			<td>
				<a href="<?= URLHelper::getLink('about.php?username='.$p['username']); ?>">
					<?= htmlReady($p['fullname']) ?>
				</a>
			</td>
			<td style='padding-left:1em;'>
				<a href="<?=$controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$p['username'].'/accept')?>">
					<?= makebutton('eintragen') ?>
				</a>
			</td>
			<td style='padding-left:1em;'>
				<a href="<?=$controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$p['username'].'/deny')?>">
					<?= makebutton('ablehnen') ?>
				</a>
			</td>
		</tr>		
	<?endforeach;?>
	</table>
</p>
<? endif; ?>
<? endif; ?>

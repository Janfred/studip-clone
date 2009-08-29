<?php

/* * * * * * * * * * * * *
 * * * I N F O B O X * * *
  * * * * * * * * * * * * */
$infobox['picture'] = 'groups.jpg';
$infobox['content'] = array(
	array(
		'kategorie' => _("Information"), 
		'eintrag'   => array(
			array(
				'text' => 'Hier können Sie angeben, welche Module/Plugins in Studentische Arbeitsgruppen verwendet werden dürfen.',
				'icon' => 'ausruf_small.gif'
			)
		)
	)   
);

/* * * * * * * * * * * *
 * * * O U T P U T * * * 
 * * * * * * * * * * * */

$cssSw = new cssClassSwitcher();

?>
<?= $this->render_partial("course/studygroup/_feedback") ?>
<h3><?= _("Studentische Arbeitsgruppen")?></h3>
<? if (!Config::getInstance()->getValue('STUDYGROUPS_ENABLE')):?>
	<p><?= _("Die Studentischen Arbeitsgruppen sind derzeit <b>nicht</b> aktiviert.") ?></p>
	<p><?= _("Zum Aktivieren füllen Sie das Formular aus und klicken Sie auf 'Speichern'") ?></p>
<? else: ?>
	<p><?= _("Die Studentischen Arbeitsgruppen sind aktiviert.") ?></p>
	<div>
	<form action="<?= $controller->url_for('course/studygroup/deactivate') ?>" method="post">
	<?= makebutton('deaktivieren', 'input') ?>
	</form>
	<br />
	</div>
<?php endif;?>
<form action="<?= $controller->url_for('course/studygroup/savemodules') ?>" method="post">
	<!-- Title -->
	<div style="float: left; width: 50%; clear: left;" class="steelgraudunkel">
		<b><?= _("Aktivierbare Module / Plugins") ?></b>
	</div>
	<div style="float: left; width: 50%;" class="steelgraudunkel">
		&nbsp;
	</div>
	<?= $cssSw->switchClass(); ?>
	<div style="float: left; width: 50%; clear: left;" class="<?= $cssSw->getClass() ?>">
	    <?=_("TeilnehmerInnen")?>
	</div>
	<div style="float: left; width: 50%;" class="<?= $cssSw->getClass() ?>">
		<?=_("immer aktiv")?>
	</div>

	<!-- Modules / Plugins -->
<? if (is_array($modules)) foreach( $modules as $key => $name ) : 
	if (in_array($key, array('participants', 'schedule'))) continue; 
	$cssSw->switchClass(); ?>


	<div style="float: left; width: 50%; clear: left;" class="<?= $cssSw->getClass() ?>">
	    <?= $name ?>
	</div>

	<div style="float: left; width: 50%;" class="<?= $cssSw->getClass() ?>">
	<select name='modules[<?= $key ?>]'>
		<? if (!Config::getInstance()->getValue('STUDYGROUPS_ENABLE')):?>
		<option value='invalid'><?= _("-- bitte auswählen --")?></option>
		<? endif ?>
		<option value='on' <?= $enabled[$key] ? 'selected' : '' ?>><?= _("aktivierbar")?></option>
		<option value='off' <?= $enabled[$key] ? '' : 'selected' ?>><?= _("nicht aktivierbar")?></option>
	</select>
	</div>

<? endforeach; ?>
	<br />

	<!-- Title -->
	<div style="clear: left">
	<div>&nbsp;</div>
	<div style="float: left; width: 100%; clear: left;" class="steelgraudunkel">
		<b><?= _("Einrichtungszuordnung") ?></b>
	</div>
	<div style="float: left; width: 50%; clear: left;" class="<?= $cssSw->getClass() ?>">
		<?= _("Alle Studentischen Arbeitsgruppen werden folgender Einrichtung zugeordnet:") ?><br>
	</div>
	<div style="float: left; width: 50%;" class="<?= $cssSw->getClass() ?>">
		<select name="institute">
		<? if (!Config::getInstance()->getValue('STUDYGROUPS_ENABLE')):?>
			<option value='invalid' selected><?= _("-- bitte auswählen --")?></option>
		<? endif ?>
		<? foreach ($institutes as $fak_id => $faculty) : ?>
			<option value="<?= $fak_id ?>" style="font-weight: bold" 
				<?= ($fak_id == $default_inst) ? 'selected="selected"' : ''?>>
				<?= htmlReady(my_substr($faculty['name'], 0, 60)) ?>
			</option>
			<? foreach ($faculty['childs'] as $inst_id => $inst_name) : ?>
			<option value="<?= $inst_id ?>"
				<?= ($inst_id == $default_inst) ? 'selected="selected"' : ''?>>
				<?= htmlReady(my_substr($inst_name, 0, 60)) ?>
			</option>
			<? endforeach; ?>
		<? endforeach; ?>
		</select>
	</div>
	</div>
	<br />
	<div style="clear: left">
	<div>&nbsp;</div>
	<!-- Title -->
	<div style="float: left; width: 100%; clear: left;" class="steelgraudunkel">
		<b><?= _("Nutzungsbedingugen") ?></b>
	</div>
	<div style="float: left; width: 100%; clear: left;" class="<?= $cssSw->getClass() ?>">
		<?= _("Geben Sie hier Nutzungsbedingungen für die Studentischen Arbeitsgruppe ein. ".
				"Diese müssen akzeptiert werden, bevor eine Arbeitsgruppe angelegt werden kann.") ?><br>
	</div>
	<? $cssSw->switchClass(); ?>
	<div style="float: left; width: 100%; clear: left; text-align: center;" class="<?= $cssSw->getClass() ?>">
		<br />
		<textarea name="terms" style="width: 90%" rows="10" style='align:middle;'><?= $terms ?></textarea>
		<br />
	</div>
	
	<p style="clear: left; text-align: center">
		<br>
		<input type="image" <?= makebutton('speichern', 'src') ?>>
	</p>
</div>
</form>
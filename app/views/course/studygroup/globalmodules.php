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
<form action="<?= $controller->url_for('course/studygroup/savemodules') ?>" method="post">
<div>
	<!-- Title -->
	<div style="float: left; width: 50%; clear: left;" class="steelgraudunkel">
		<b><?= _("Modul / Plugin") ?></b>
	</div>
	<div style="float: left; width: 25%;" class="steelgraudunkel">
		&nbsp;
	</div>
	<div style="float: left; width: 25%;" class="steelgraudunkel">
		&nbsp;
	</div>

	<!-- Modules / Plugins -->
<? if (is_array($modules)) foreach( $modules as $key => $name ) : 
	$cssSw->switchClass(); ?>
	<div style="float: left; width: 50%; clear: left;" class="<?= $cssSw->getClass() ?>">
	    <?= $name ?>
	</div>

	<div style="float: left; width: 25%;" class="<?= $cssSw->getClass() ?>">
		<input type="radio" name="modules[<?= $key ?>]" value="1" <?= $enabled[$key] ? 'checked="checked"' : '' ?>> an
	</div>

	<div style="float: left; width: 25%" class="<?= $cssSw->getClass() ?>">
		<input type="radio" name="modules[<?= $key ?>]" value="0" <?= $enabled[$key] ? '' : 'checked="checked"' ?>> aus
	</div>

<? endforeach; ?>
	<div style="clear: left">
		<br>
		<?= _("Alle Studentischen Arbeitsgruppen werden folgender Einrichtung zugeordnet:") ?><br>
		<select name="institute">
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

	<div style="clear: left">
		<br>
		<?= _("Geben Sie hier Nutzungsbedingungen für die Studentischen Arbeitsgruppe ein. ".
				"Diese müssen akzeptiert werden, bevor eine Arbeitsgruppe angelegt werden kann.") ?><br>
		<textarea name="terms" style="width: 100%" rows="10"><?= $terms ?></textarea>
	</div>

	<p style="clear: left; text-align: center">
		<br>
		<input type="image" <?= makebutton('speichern', 'src') ?>>
	</p>
</div>
</form>

<form name="single_termin" class="default" action="<?= $controller->url_for("course/dates/singledate/" . htmlReady($date->getId())) ?>" method="POST" data-dialog="size=auto">
<?= CSRFProtection::tokenTag()?>
    <fieldset>
    	<legend><?= _("Termin findet am folgenden Tag zum folgenden Zeitpunkt statt") ?></legend>
    	<div style="display: inline;">
        	<label for="startDate" style="display: inline;"><?= _('Datum') ?></label>
        	<input id="startDate" class="has-date-picker" name="startDate" type="text" maxlength="10" size="10" value="<?= !$date->date ? _('Datum') : date("d.m.Y", $date->date ); ?>">

    		<label for="startStunde" style="display: inline; margin-left: 20px;"><?= _('Start') ?></label>
    		<input id="startStunde" name="start_stunde" type="text" maxlength="2" size="2" value="<?= !$date->date ? "" : date("H", $date->date ); ?>"> :
    		<input id="startMinute" name="start_minute" type="text" maxlength="2" size="2" value="<?= !$date->date ? "" : date("i", $date->date ); ?>">

    		<label for="endStunde" style="display: inline; margin-left: 20px;"><?= _('Ende') ?></label>
    		<input id="endStunde" name="end_stunde" type="text" maxlength="2" size="2" value="<?= !$date->end_time ? "" : date("H", $date->end_time ); ?>"> :
    		<input id="endMinute" name="end_minute" type="text" maxlength="2" size="2" value="<?= !$date->end_time ? "" : date("i", $date->end_time ); ?>">
    	</div>
    </fieldset>

    <fieldset>
    	<legend><?= _('Art des Termins') ?></legend>
    	<select name="dateType" >
       	<? foreach ($GLOBALS['TERMIN_TYP'] as $key => $val) : ?>
        	<option value="<?= $key ?>" <?= $date['date_typ'] == $key ? ' selected' : '' ?> > <?= htmlReady($val['name']) ?> </option>
		<? endforeach; ?>
        </select>
    </fieldset>

    <fieldset>
    	<legend><?= _('Freie Ortsangabe (keine Raumbuchung)') ?></legend>
    	<textarea name="freeRoomText_sd" style="width: 98%;" rows="3" ><?= htmlReady($date->raum) ?></textarea>
    </fieldset>

    <input type="hidden" name="singleDateID" value="<?= htmlReady($date->getId()) ?>">

	<div style="text-align: center;" data-dialog-button>
        <div class="button-group">
            <? if (!$dates_locked) : ?>
        	    <?= \Studip\Button::create(_('Termin Speichern'), "editSingleDate_button" ); ?>
            <? endif ?>
            <? if (!$cancelled_dates_locked && !$date->isNew()) : ?>
                <?= \Studip\LinkButton::create(_("Ausfallen lassen"), $controller->url_for("course/cancel_dates", array('termin_id' => $date->getId())), array('data-dialog' => '')) ?>
            <? endif ?>
        </div>
    </div>
</form>

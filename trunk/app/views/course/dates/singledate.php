<form name="single_termin" class="studip_form" action="<?= $controller->url_for("course/dates/singledate/" . htmlReady($date->getId())) ?>" method="POST" data-dialog="size=auto">
    
    <fieldset>
    	<legend><?= _("Termin findet am folgenden Tag zum folgenden Zeitpunkt statt") ?></legend>
    	<div style="display: inline;">
        	<label for="startDate" style="display: inline;"><?= _('Datum') ?></label> 
        	<input id="startDate" class="has-date-picker" name="startDate" type="text" maxlength="10" size="10" value="<?= $date->isNew() ? _('Datum') : date("d.m.Y", $date->date ); ?>">
    		    		
    		<label for="startStunde" style="display: inline; margin-left: 20px;"><?= _('Start') ?></label> 
    		<input id="startStunde" name="start_stunde" type="text" maxlength="2" size="2" value="<?= $date->isNew() ? "" : date("H", $date->date ); ?>"> : 
    		<input id="startMinute" name="start_minute" type="text" maxlength="2" size="2" value="<?= $date->isNew() ? "" : date("i", $date->date ); ?>">
    		
    		<label for="endStunde" style="display: inline; margin-left: 20px;"><?= _('Ende') ?></label> 
    		<input id="endStunde" name="end_stunde" type="text" maxlength="2" size="2" value="<?= $date->isNew() ? "" : date("H", $date->end_time ); ?>"> : 
    		<input id="endMinute" name="end_minute" type="text" maxlength="2" size="2" value="<?= $date->isNew() ? "" : date("i", $date->end_time ); ?>">          	
    	</div>
    </fieldset>
       
    <fieldset>
    	<legend><?= _('Art des Termins') ?></legend>
    	<select name="dateType" >
       	<? foreach ($GLOBALS['TERMIN_TYP'] as $key => $val) : ?>    
        	<option value="<?= $key ?>" <?= $date['date_typ'] == $key ? ' selected' : '' ?> > <?= $val['name'] ?> </option>
		<? endforeach; ?>                   
        </select>
    </fieldset>
    
    <fieldset>
    	<legend><?= _('Freie Ortsangabe (keine Raumbuchung)') ?></legend>
    	<textarea name="freeRoomText_sd" style="width: 98%;" rows="3" ><?= $date->raum ?></textarea>
    </fieldset>    
    
    <input type="hidden" name="singleDateID" value="<?= htmlReady($date->getId()) ?>">
    <? if (!empty($date->metadate_id)) : ?>
    <input type="hidden" name="cycle_id" value="<?= $date->metadate_id ?>">
    <? endif; ?> 
    <input type="hidden" name="cmd" value="editSingleDate">
    <input type="hidden" name="action" value="nochange">
    <input type="hidden" name="room_sd" value="<?= $room_sd ?>">
    <input type="hidden" name="related_teachers" value="<?= $related_teachers ?>">
    <input type="hidden" name="related_statusgruppen" value="<?= $related_groups ?>">
    
	<div style="text-align: center;" data-dialog-button>
        <div class="button-group">
        	<?= \Studip\Button::create(_('Termin Speichern'), "editSingleDate_button" ); ?>
            <? if (!$cancelled_dates_locked && $GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) : ?>
                <?= \Studip\LinkButton::create(_("Ausfallen lassen"), $controller->url_for("course/cancel_dates", array('termin_id' => $date->getId())), array('data-dialog' => '')) ?>
            <? endif ?>
        </div>
    </div>
</form>

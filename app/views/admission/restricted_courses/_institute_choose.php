<form action="?" method="post" name="institute_choose">
<?= CSRFProtection::tokenTag() ?>
    <div style="font-weight:bold">
        <?=_("Einrichtung:")?>
    </div>
    <div>
        <select name="choose_institut_id" class="nested-select">
<? while (list($institut_id,$institute) = each($my_inst)) : ?>
            <option value="<?= $institut_id?>" <?=($current_institut_id == $institut_id ? 'selected' : '')?> class="<?= $institute['is_fak'] ? 'nested-item-header' : 'nested-item' ?>">
                <?= htmlReady(my_substr($institute["name"] . ' (' . $institute["num_sem"] . ')',0,100));?>
            </option>
    <? if ($institute["is_fak"] == 'all') : ?>
        <? $num_inst = $institute["num_inst"]; for ($i = 0; $i < $num_inst; ++$i) : ?>
            <? list($institut_id,$institute) = each($my_inst);?>
            <option value="<?= $institut_id?>" <?=($current_institut_id == $institut_id ? 'selected' : '')?> class="nested-item">
                <?= htmlReady(my_substr($institute["name"] . ' (' . $institute["num_sem"] . ')',0,100));?>
            </option>
        <? endfor ?>
    <? endif ?>
<? endwhile ?>
        </select>
    </div>
    <div style="font-weight:bold">
        <?=_("Präfix des Veranstaltungsnamens / Nummer:")?>
    </div>
    <div>
        <input type="text" name="sem_name_prefix" value="<?=htmlReady($sem_name_prefix)?>" size="40">
    </div>
    <div style="font-weight:bold">
        <?=_("Veranstaltungen aus diesem Semester:")?> 
    </div>
    <div>
        <?=SemesterData::GetSemesterSelector(array('name'=>'select_semester_id'), $current_semester_id, 'semester_id', false)?>
    </div>
    <div>
        <?= Studip\Button::create(_('Auswählen'), 'choose_institut', array('title' => _("Einrichtung auswählen"))) ?>
    </div>
</form>

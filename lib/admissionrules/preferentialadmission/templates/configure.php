<h3><?= $rule->getName() ?></h3>
<label for="prefadmission_conditions" class="caption">
    <?= _('Folgende Personen bei der Platzverteilung bevorzugen:') ?>
</label>
<div id="prefadmission_conditions">
    <?php if (!$rule->getConditions()) { ?>
    <span class="nofilter">
        <i><?= _('Sie haben noch keine Auswahl festgelegt.'); ?></i>
    </span>
    <?php } else { ?>
    <div class="userfilter">
        <?php foreach ($rule->getConditions() as $condition) { ?>
            <div class="condition" id="condition_<?= $condition->getId() ?>">
                <?= $condition->toString() ?>
                <a href="#" onclick="return STUDIP.UserFilter.removeConditionField($(this).parent())"
                    class="conditionfield_delete">
                    <?= Assets::img('icons/16/blue/trash.png'); ?></a>
                <input type="hidden" name="conditions[]" value="<?= htmlReady(serialize($condition)) ?>"/>
            </div>
        <?php } ?>
    </div>
    <?php } ?>
    <br/><br/>
    <a href="<?= URLHelper::getURL('dispatch.php/userfilter/filter/configure/prefadmission_conditions') ?>" onclick="return STUDIP.UserFilter.configureCondition('condition', '<?= URLHelper::getURL('dispatch.php/userfilter/filter/configure/prefadmission_conditions') ?>')">
        <?= Assets::img('icons/16/blue/add.png', array(
            'alt' => _('Bedingung hinzufügen'),
            'title' => _('Bedingung hinzufügen'))) ?><?= _('Bedingung hinzufügen') ?></a>
</div>
<br/>
<label for="favor_semester" class="caption">
    <input type="checkbox" name="favor_semester"<?= $rule->getFavorSemester() ? ' checked="checked"' : '' ?>/>
    <?= _('Höhere Fachsemester bevorzugen') ?>
</label>
<br/>

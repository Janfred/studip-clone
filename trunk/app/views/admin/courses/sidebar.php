<form class="default" method="post" 
    action="<?= URLHelper::getLink('dispatch.php/admin/courses/sidebar'); ?>" >
    <input type="hidden" name="updateConfig" value="1">
    <label>
        <input name="searchActive" type="checkbox" value="1"
            <?= in_array('search', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
        <?= _('Freie Suche'); ?>
    </label>
    
    <label>
        <input name="instituteActive" type="checkbox" value="1"
            <?= in_array('institute', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
        <?= _('Einrichtung'); ?>
    </label>
    <label>
        <input name="semesterActive" type="checkbox" value="1"
            <?= in_array('semester', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
        <?= _('Semester'); ?>
    </label>
    <label>
        <input name="courseTypeActive" type="checkbox" value="1"
            <?= in_array('courseType', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
        <?= _('Veranstaltungstyp-Filter'); ?>
    </label>
    <label>
        <input name="teacherActive" type="checkbox" value="1"
            <?= in_array('teacher', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
        <?= _('Dozent'); ?>
    </label>
    <label>
        <input name="viewFilterActive" type="checkbox" value="1"
            <?= in_array('viewFilter', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
        <?= _('Darstellungs-Filter'); ?>
    </label>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Speichern')); ?>
    </div>
</form>
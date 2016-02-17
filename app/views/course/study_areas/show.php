<? if (!$locked) : ?>
    <form action="<?= $controller->url_for('course/study_areas/save/' . $course->id, $url_params) ?>" method="post">
<? endif?>
    <?= $tree ?>
<? if(!$locked) : ?>
        <div data-dialog-button class="hidden-no-js" style="clear: both; text-align: center">
            <?= Studip\Button::createAccept(_('Speichern')) ?>
        </div>
    </form>
<? endif ?>
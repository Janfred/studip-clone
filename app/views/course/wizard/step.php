<?php if ($content) : ?>
    <form class="default course-wizard-step-<?= $stepnumber ?>" data-secure action="<?= $controller->url_for('course/wizard/process', $stepnumber, $temp_id) ?>" method="post">
        <?= $content ?>
        <footer data-dialog-button>
            <input type="hidden" name="step" value="<?= $stepnumber ?>"/>
            <?php if (!$first_step) { ?>
                <?= Studip\Button::create(_('Zurück'), 'back',
                    $dialog ? array('data-dialog' => 'size=50%') : array()) ?>
            <?php } ?>
            <?= Studip\Button::create(_('Weiter'), 'next',
                $dialog ? array('data-dialog' => 'size=50%') : array()) ?>
        </footer>
    </form>
<?php else : ?>
    <?= Studip\LinkButton::createCancel(_('Zurück zu meiner Veranstaltungsübersicht'),
        $controller->url_for($GLOBALS['perm']->have_perm('admin') ? 'admin/courses' : 'my_courses'),
        array('data-dialog-button' => true)) ?>
<?php endif ?>

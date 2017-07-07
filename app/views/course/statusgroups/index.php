<form action="<?= $controller->url_for('course/statusgroups/batch_action') ?>" method="post">
<section class="contentbox course-statusgroups">
    <header>
        <h1><?= _('Teilnehmende nach Gruppen') ?></h1>
    </header>
    <?php foreach ($groups as $group) : ?>
        <?= $this->render_partial('course/statusgroups/_group',
            array('group' => $group['group'], 'members' => $group['members'])) ?>
    <?php endforeach ?>
    <?php if ($no_group) : ?>
        <?= $this->render_partial('course/statusgroups/_group',
            array('group' => $no_group['group'], 'members' => $no_group['members'])) ?>
    <?php endif ?>
    <?php if (count($groups) > 1 && $is_tutor && !$is_locked) : ?>
        <footer>
            <div class="groupselection">
                <label>
                    <input aria-label="<?= sprintf(_('Alle Gruppen auswählen')) ?>"
                           type="checkbox" name="allgroups" value="1"
                           data-proxyfor=":checkbox.groupselector"
                           data-activates="select#batch-groups-action">
                    <?= _('Alle Gruppen auswählen') ?>
                </label>
            </div>
            <div class="groupactions">
                <label>
                    <select name="groups_action" id="batch-groups-action" disabled>
                        <option value="edit"><?= _('Bearbeiten') ?></option>
                        <option value="delete"><?= _('Löschen') ?></option>
                    </select>
                </label>
                <?= Studip\Button::create(_('Ausführen'), 'batch_groups', array('data-dialog' => 'size=auto')) ?>
            </div>
        </footer>
    <?php endif ?>
</section>

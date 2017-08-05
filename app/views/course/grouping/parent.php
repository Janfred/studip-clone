<? if ($parent) : ?>
    <form class="default" action="<?= $controller->url_for('course/grouping/unassign_parent') ?>">
        <section>
            <p>
                <?= sprintf(
                    _('Diese Veranstaltung gehört zur Hauptveranstaltung %s%s%s.'),
                    sprintf(
                        '<a href="%s" title="%s">',
                        $controller->link_for('course/grouping/children', ['cid' => $parent->id]),
                        htmlReady($parent->getFullname())
                    ),
                    htmlReady($parent->getFullname()),
                    '</a>'
                ) ?>
            </p>
        </section>
        <footer>
            <?= Studip\Button::createCancel(_('Zuordnung aufheben'), 'unassign') ?>
        </footer>
    </form>
<? else : ?>
    <p>
        <?= _('Diese Veranstaltung ist noch keiner Hauptveranstaltung zugeordnet.') ?>
    </p>
    <form class="default" action="<?= $controller->url_for('course/grouping/assign_parent') ?>">
        <section>
            <?= $search->render() ?>
        </section>
        <footer>
            <?= Studip\Button::createAccept(_('Zuordnen'), 'assign') ?>
        </footer>
    </form>
<? endif ?>

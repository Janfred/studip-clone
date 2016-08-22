<? if ($courses) : ?>
    <? if(count($courses) == 1) : ?>
        <?= MessageBox::warning(_('Sie sind im Begriff, die folgende Veranstaltung zu archivieren. Dieser Schritt kann nicht rückgängig gemacht werden!')) ?>
    <? else : ?>
        <?= MessageBox::warning(_('Sie sind im Begriff, die aufgelisteten Veranstaltungen zu archivieren. Dieser Schritt kann nicht rückgängig gemacht werden!')) ?>
    <? endif ?>
<table class="default withdetails">
    <thead>
        <tr>
            <th><?= _('Name der Veranstaltung') ?></th>
            <th><?= _('Letzte Aktivität') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($courses as $course) : ?>
        <tr <? if (count($courses) == 1) : ?>class="open"<? endif ?> >
            <td>
                <a onclick="jQuery(this).closest('tr').toggleClass('open'); return false;" href="">
                    <?= htmlReady($course->name) ?>
                </a>
            </td>
            <td>
                <?= htmlReady($lastActivities[$course->id]) ?>
            </td>
        </tr>
        <tr class="details nohover">
            <td colspan="2">
                <div class="detailscontainer">
                    <dl class="default nohover">
                        <dt><?= _('Untertitel') . ':' ?></dt>
                        <dd>
                            <?= $course->untertitel ? htmlReady($course->untertitel) : ' ' ?>
                        </dd>
                        <dt><?= _('Lehrende') ?></dt>
                        <dd>
                            <ul>
                            <? foreach ($dozenten[$course->id] as $dozent) : ?>
                                <li>
                                    <a href="<?= $controller->url_for('profile?username=' . htmlReady($dozent->username)) ?>" >
                                    <?= htmlReady($dozent->vorname) . ' ' . htmlReady($dozent->nachname) ?>
                                    </a>
                                </li>
                            <? endforeach ?>
                            </ul>
                        </dd>
                        <dt><?= _('Veranstaltungsort') . ':' ?></dt>
                        <dd><?= $course->ort ? htmlReady($course->ort) : ' ' ?></dd>
                        <dt><?= _('Semester') . ':'; ?></dt>
                        <dd><?= $course->start_semester->name ? htmlReady($course->start_semester->name) : ' ' ?></dd>
                        <dt><?= _('Veranstaltungsnummer') . ':' ?></dt>
                        <dd><?= $course->veranstaltungsnummer ? htmlReady($course->veranstaltungsnummer) : ' ' ?></dd>
                    </dl>
                </div>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">
                <form class="default" action="<?= $controller->url_for('course/archive/archive') ?>" method="post" data-dialog>
                    <? foreach ($courses as $course) : ?>
                        <input type="hidden" name="courseIds[]" value="<?= $course->id ?>">
                    <? endforeach ?>
                    <div data-dialog-button>
                        <?= \Studip\Button::create(_('Archivieren')) ?>
                    </div>
                </form>
            </td>
        </tr>
    </tfoot>
</table>
<? else : ?>
<?= MessageBox::error(_('Es wurde keine Veranstaltung ausgewählt!')) ?>
<? endif ?>

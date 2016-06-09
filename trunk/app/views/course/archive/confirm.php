<?= MessageBox::info(_('Sie sind im Begriff, die aufgelistete(n) Veranstaltung(en) zu archivieren. Dieser Schritt kann nicht rückgängig gemacht werden!')); ?>
<table class="default withdetails">
    <caption><?= $_SESSION['SessSemName']["header_line"] ?></caption>
    <thead>
        <tr>
            <th><?= _('Veranstaltungs-ID'); ?></th>
            <th><?= _('Name der Veranstaltung'); ?></th>
            <th><?= _('Lehrende'); ?></th>
            <th><?= _('Letzte Aktivität'); ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($courses as $course) : ?>
        <tr class="open">
            <td>
                <a onclick="jQuery(this).closest('tr').toggleClass('open'); return false;" href="">
                    <?= htmlReady($course->id) ?>
                </a>
            </td>
            <td>
                <strong>
                    <a href="<?= URLHelper::getLink('seminar_main.php?auswahl=' . htmlReady($course->id)); ?>">
                        <?= htmlReady($course->name); ?>
                    </a>
                </strong>
            </td>
            <td>
                <?  $i = 0;
                    foreach ($dozenten[$course->id] as $dozent) : 
                        if($i > 0) : ?>
                            ,
                        <? endif;
                        $i++;?>
                    <a href="<?= $controller->url_for('profile?username=' . htmlReady($dozent->username)); ?>" >
                        <?= htmlReady($dozent->vorname) . ' ' . htmlReady($dozent->nachname) ?>
                    </a>
                <? endforeach ?>
            </td>
            <td>
                
            </td>
        </tr>
        <tr class="details nohover">
            <td style="display:none;">
                <div class="detailscontainer">
                    <table class="default nohover">
                        <tr>
                            <th><?= _('Untertitel') . ':'; ?></th>
                            <td><?= htmlReady($course->untertitel); ?></td>
                        </tr>
                        <tr>
                            <th><?= _('Veranstaltungsort') . ':'; ?></th>
                            <td><?= htmlReady($course->ort); ?></td>
                        </tr>
                        <tr>
                            <th><?= _('Semester') . ':'; ?></th>
                            <td><?= htmlReady($course->start_semester->name); ?></td>
                        </tr>
                        <tr>
                            <th><?= _('Veranstaltungsnummer') . ':'; ?></th>
                            <td><?= htmlReady($course->veranstaltungsnummer); ?></td>
                        </tr>
                        
                    </table>
                </div>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">
                <a href="<?= $controller->url_for('course/archive/archive', htmlReady($courses)); ?>">
                    <?= \Studip\Button::create(_('Archivieren')); ?>
                </a>
            </td>
        </tr>
    </tfoot>
</table>


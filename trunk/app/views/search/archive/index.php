<? if ($errorMessage) : ?>
    <?= MessageBox::error($errorMessage); ?>
<? endif ?>

<form class="default" method="get" action="<?= URLHelper::getLink(); ?>">
    <fieldset>
        <legend><?= _('Suche im Veranstaltungsarchiv'); ?></legend>
        <label>
            <?= _('Suche nach') . ':'; ?>
            <input type="text" minlength="4" name="search"
                value="<?= htmlReady($archivedCourseName) ?>">
        </label>
        <?= \Studip\Button::create(_('Suchen'), ''); ?>
    </fieldset>
</form>

<? if ($foundCourses) : ?>
    <? if (count($foundCourses) == 1) : ?>
        <?= MessageBox::info(_('Es wurde eine Veranstaltung gefunden!')); ?>
    <? else : ?>
        <?= MessageBox::info(sprintf(_('Es wurden %s Veranstaltungen gefunden!'), count($foundCourses))); ?>
    <? endif ?>
    
    <table class="default withdetails">
        <tr>
            <th><?= _('Name') ?></th>
            <th><?= _('Lehrende') ?></th>
            <th><?= _('Einrichtungen') ?></th>
            <th><?= _('Semester') ?></th>
            <th><?= _('Aktionen') ?></th>
        </tr>
    <? foreach ($foundCourses as $course) : ?>
        <tr <? if (count($foundCourses) == 1) : ?>class="open"<? endif ?> >
            <td onclick="jQuery(this).closest('tr').toggleClass('open'); return false;">
                <?= htmlReady($course->name); ?>
            </td>
            <td><?= htmlReady($course->dozenten); ?></td>
            <td><?= htmlReady($course->institute); ?></td>
            <td><?= htmlReady($course->semester); ?></td>
            <td>
                <a href="<?= $controller->url_for(
                                'archive/overview',
                                $course->id
                                ); ?>" data-dialog>
                    <?= Icon::create('info-circle', 'clickable')->asImg('16px') ?>
                </a>
                
                <? if ($course->archiv_file_id and archiv_check_perm($course->id)) : 
                    $filename = _('Dateisammlung') . '-' . substr($course->name, 0, 200) . '.zip';
                ?>
                <a href="<?= URLHelper::getLink(GetDownloadLink($course->archiv_file_id, $filename, 1)) ?>">
                    <?= Icon::create('file-archive', 'clickable')->asImg('16px') ?>
                </a>
                <? elseif ($course->archiv_protected_file_id and (archiv_check_perm($course->id) == 'admin')) :
                    $filename = _('Dateisammlung') . '-' . substr($course->name, 0, 200) . '.zip';
                ?>
                <a href="<?= URLHelper::getLink(GetDownloadLink($course->archiv_protected_file_id, $filename, 1)) ?>">
                    <?= Icon::create('file-archive', 'clickable')->asImg('16px') ?>
                </a>
                <? endif ?>
                <? if(archiv_check_perm($course->id)) : ?>
                <a href="<?= $controller->url_for(
                                'archive/forum',
                                $course->id
                                ); ?>" data-dialog>
                    <?= Icon::create('forum', 'clickable')->asImg('16px') ?>
                </a>
                <a href="<?= $controller->url_for(
                                'archive/wiki',
                                $course->id
                                ); ?>" data-dialog>
                    <?= Icon::create('wiki', 'clickable')->asImg('16px') ?>
                </a>
                <? endif ?>
            </td>
        </tr>
        <tr class="details nohover">
            <td colspan="4" class="detailscontainer">
                <ul class="default nohover">
                    <li>
                        <strong><?= _('Fakultät') . ':';?></strong>
                        <?= htmlReady($course->fakultaet); ?>
                    </li>
                    <li>
                        <strong><?= _('Bereich')  . ':'; ?></strong>
                        <?= htmlReady($course->studienbereiche); ?>
                    </li>
                </ul>
            </td>
        </tr>
    <? endforeach ?>
    </table>
<? else : ?>
    <? if (!$errorMessage) : ?>
        <? if ($searchRequested) : ?>
            <?= MessageBox::info(_('Es wurde keine Veranstaltung gefunden!')); ?>
        <? endif ?>
    <? endif ?>
<? endif ?>
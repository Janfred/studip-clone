<form class="default" method="post" action="<?= URLHelper::getLink(); ?>">
    <input type="hidden" name="searchRequested" value="1" >
    <fieldset>
        <legend><?= _('Suche im Veranstaltungsarchiv'); ?></legend>
        <label>
            <?= _('Name der Veranstaltung') . ':'; ?>
            <input type="text" minlength="4" name="criteria"
                value="<?= htmlReady($criteria) ?>">
        </label>
        <label>
            <?= _('Semester') . ':'; ?>
            <select name="selectedSemester">
                <? foreach($availableSemesters as $semester) : ?>
                <option value="<?= htmlReady($semester->name) ?>"
                    <?= ($selectedSemester == $semester->name) ? 'selected="selected"' : '' ?>>
                    <?= htmlReady($semester->name) ?>
                </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Einrichtung') . ':'; ?>
            <select name="selectedDepartment">
                <? foreach($availableDepartments as $department) : ?>
                <option value="<?= htmlReady($department->name) ?>"
                    <?= ($selectedDepartment == $department->name) ? 'selected="selected"' : '' ?>>
                    <?= htmlReady($department->name) ?>
                </option>
                <? endforeach ?>
            </select>
        </label>
        <?= \Studip\Button::create(_('Suchen'), ''); ?>
    </fieldset>
</form>
<? if ($tooManyCourses) : ?>
    <?= MessageBox::error(sprintf(_('Es wurden %s Veranstaltungen gefunden. Bitte grenzen sie die Suchkriterien weiter ein!'), $amountOfCourses)) ?>
<? endif ?>

<? if ($foundCourses and !$tooManyCourses) : ?>
    <? if (count($foundCourses) == 1) : ?>
        <?= MessageBox::info(_('Es wurde eine Veranstaltung gefunden!')); ?>
    <? else : ?>
        <?= MessageBox::info(sprintf(_('Es wurden %s Veranstaltungen gefunden!'), $amountOfCourses)) ?>
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
                <?= htmlReady($course->name) ?>
            </td>
            <td><?= htmlReady($course->dozenten) ?></td>
            <td><?= htmlReady($course->institute) ?></td>
            <td><?= htmlReady($course->semester) ?></td>
            <td>
                <? if(archiv_check_perm($course->id)) : ?>
                <a href="<?= $controller->url_for(
                                'archive/overview',
                                $course->id
                                ) ?>" data-dialog>
                    <?= Icon::create('info-circle', 'clickable')->asImg('16px') ?>
                </a>
                <? endif ?>
                
                <? if ($course->archiv_file_id and archiv_check_perm($course->id)) : 
                    $filename = _('Dateisammlung') . '-' . substr($course->name, 0, 200) . '.zip';
                ?>
                <a href="<?= URLHelper::getLink(GetDownloadLink($course->archiv_file_id, $filename, 1)) ?>">
                    <?= Icon::create('file-archive', 'clickable')->asImg('16px') ?>
                </a>
                <? elseif ($course->archiv_protected_file_id and in_array(archiv_check_perm($course->id), ['tutor', 'dozent', 'admin'])) :
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
                                ) ?>" data-dialog>
                    <?= Icon::create('forum', 'clickable')->asImg('16px') ?>
                </a>
                <a href="<?= $controller->url_for(
                                'archive/wiki',
                                $course->id
                                ) ?>" data-dialog>
                    <?= Icon::create('wiki', 'clickable')->asImg('16px') ?>
                </a>
                <? endif ?>
            </td>
        </tr>
        <tr class="details nohover">
            <td colspan="4" class="detailscontainer">
                <ul class="default nohover">
                    <li>
                        <strong><?= _('Fakultät') . ':' ?></strong>
                        <?= htmlReady($course->fakultaet) ?>
                    </li>
                    <li>
                        <strong><?= _('Bereich')  . ':' ?></strong>
                        <?= htmlReady($course->studienbereiche) ?>
                    </li>
                </ul>
            </td>
        </tr>
    <? endforeach ?>
    </table>
<? else : ?>
    <? if (!$errorOccured and !$tooManyCourses) : ?>
            <?= MessageBox::info(_('Es wurde keine Veranstaltung gefunden!')) ?>
    <? endif ?>
<? endif ?>

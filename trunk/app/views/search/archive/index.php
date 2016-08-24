<form class="default" method="post" action="<?= URLHelper::getLink(); ?>">
    <input type="hidden" name="myCoursesOnly" value="<?= Request::get('myCoursesOnly') ?>">
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
                <option value=""
                    <?= ($selectedSemester == '') ? 'selected="selected"' : '' ?>>
                    <?= _('alle') ?>
                </option>
                <? foreach($availableSemesterNames as $semesterName) : ?>
                <option value="<?= htmlReady($semesterName[0]) ?>"
                    <?= ($selectedSemester == $semesterName[0]) ? 'selected="selected"' : '' ?>>
                    <?= htmlReady($semesterName[0]) ?>
                </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <div style="margin-top: 1ex; margin-bottom: 1ex;"><?= _('Einrichtung') . ':'; ?></div>
            <select name="selectedDepartment" class="nested-select">
                <option value="" class="nested-item-header"
                    <?= ($selectedDepartment == '') ? 'selected="selected"' : '' ?>>
                    <?= _('alle') ?>
                </option>
                <? foreach($availableDepartments as $department) : ?>
                <option value="<?= htmlReady($department->name) ?>"
                    class="nested-item-header"
                    <?= ($selectedDepartment == $department->name) ? 'selected="selected"' : '' ?>>
                    <?= htmlReady($department->name) ?>
                </option>
                    <? foreach($department->sub_institutes as $subDepartment) : ?>
                    <option value="<?= htmlReady($subDepartment->name) ?>"
                        class="nested-item nested-item-level-1"
                        <?= ($selectedDepartment == $subDepartment->name) ? 'selected="selected"' : '' ?>>
                        <?= htmlReady($subDepartment->name) ?>
                    </option>
                    <? endforeach ?>
                <? endforeach ?>
            </select>
        </label>
        <?= \Studip\Button::create(_('Suchen'), '') ?>
    </fieldset>
</form>
<? if ($foundCourses) : ?>
    
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
            <td colspan="5" class="detailscontainer">
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
<? endif ?>

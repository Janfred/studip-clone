<? if ($errorMessage) : ?>
    <?= MessageBox::error($errorMessage); ?>
<? endif ?>

<section class="contentbox">
<header><?= _('Suche im Veranstaltungsarchiv'); ?></header>
<form class="default" method="get" action="<?= URLHelper::getLink(); ?>">
    <label>
        <?= _('Suche nach') . ':'; ?>
        <input type="text" minlength="4" name="search"
            value="<?= htmlReady($archivedCourseName) ?>">
    </label>
    <?= \Studip\Button::create(_('Suchen'), ''); ?>
</form>
</section>

<? if ($foundCourses) : ?>
    <? if (count($foundCourses) == 1) : ?>
        <?= MessageBox::info(_('Es wurde eine Veranstaltung gefunden!')); ?>
    <? else : ?>
        <?= MessageBox::info(sprintf(_('Es wurden %s Veranstaltungen gefunden!'), count($foundCourses))); ?>
    <? endif ?>
    
    <table class="default withdetails">
        <tr>
            <th><?= _('Name'); ?></th>
            <th><?= _('Lehrende'); ?></th>
            <th><?= _('Einrichtungen'); ?></th>
            <th><?= _('Semester'); ?></th>
        </tr>
    <? foreach ($foundCourses as $course) : ?>
        <tr <? if (count($foundCourses) == 1) : ?>class="open"<? endif ?> >
            <td onclick="jQuery(this).closest('tr').toggleClass('open'); return false;">
                <?= htmlReady($course->name); ?>
            </td>
            <td><?= htmlReady($course->dozenten); ?></td>
            <td><?= htmlReady($course->institute); ?></td>
            <td><?= htmlReady($course->semester); ?></td>
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
                    <li>
                        <a href="<?= $controller->url_for(
                                        'archive/overview',
                                        $course->id
                                        ); ?>">
                            <?= _('Übersicht der Veranstaltungsinhalte'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= $controller->url_for(
                                        'archive/forum',
                                        $course->id
                                        ); ?>">
                            <?= _('Beiträge des Forums'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= $controller->url_for(
                                        'archive/wiki',
                                        $course->id
                                        ); ?>">
                            <?= _('Wikiseiten'); ?>
                        </a>
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
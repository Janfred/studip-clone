<h1><?= PageLayout::getTitle() ?></h1>
<section class="contentbox">
    <header>
        <h1>
            <?= _('Informationen') ?>
        </h1>
    </header>
    <table class="default">

        <? foreach ($queries as $query): ?>
            <tr>
                <td style="font-weight: bold;"><?= $query['desc'] ?></td>
                <td class="actions">
                    <?= htmlReady($query['value']) ?>
                </td>
            </tr>
        <? endforeach; ?>
    </table>
</section>

<? if (count($sections)) : ?>
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Dateiübersicht') ?>
            </h1>
        </header>

        <? if (!empty($sections['course_files'])) : ?>
            <?= $this->render_partial('admin/user/_course_files.php', ['course_files' => $sections['course_files']]) ?>
        <? endif ?>

        <? if ($sections['institutes']) : ?>
            <?= $this->render_partial('admin/user/_institute_files.php', ['institutes' => $sections['institutes']]) ?>
        <? endif ?>

        <? if (!empty($sections['courses'])) : ?>
            <?= $this->render_partial('admin/user/_course_list.php',
                    ['memberships' => $sections['courses'],
                     'headline'    => _('Übersicht Veranstaltungen'),
                     'class'       => 'courses']) ?>
        <? endif ?>

        <? if (!empty($sections['closed_courses'])) : ?>
            <?= $this->render_partial('admin/user/_course_list.php',
                    ['memberships' => $sections['closed_courses'],
                     'headline'    => _('Übersicht geschlossene Veranstaltungen'),
                     'class'       => 'closed_courses']) ?>
        <? endif ?>
        
        <? if (!empty($sections['seminar_wait'])) : ?>
            <?= $this->render_partial('admin/user/_waiting_list.php', ['memberships' => $sections['seminar_wait']]) ?>
        <? endif ?>
        
        <? if (!empty($sections['priorities'])) : ?>
            <?= $this->render_partial('admin/user/_priority_list.php', ['priorities' => $sections['priorities']]) ?>
        <? endif ?>
    </section>
<? endif ?>

    

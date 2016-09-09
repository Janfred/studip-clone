<? if (count($m->user->studycourses)) : ?>
    <? $course_res = '' ?>
    <? $counter = 0 ?>
    <? foreach ($m->user->studycourses as $studycourses) : ?>
        <? if ($counter < 1) : ?>
            <?= htmlReady($studycourses->studycourse->name) ?>
            <?= htmlReady($studycourses->degree->name) ?>
            (<?= htmlReady($studycourses->semester) ?>)
        <? endif ?>
        <? if (count($m->user->studycourses) > 1) : ?>
            <? $course_res .= sprintf('- %s (%s)<br>',
                    htmlReady(trim($studycourses->studycourse->name . ' ' . $studycourses->degree->name)),
                    htmlReady($studycourses->semester)) ?>
        <? endif ?>
        <? $counter++ ?>
    <? endforeach ?>
    <? if ($course_res != '') : ?>
        [...]<?= tooltipHtmlIcon('<strong>' . _('Weitere Studiengänge') . '</strong><br>' . $course_res) ?>
    <? endif ?>
<? endif ?>

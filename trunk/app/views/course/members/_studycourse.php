
<? if(count($m->user->studycourses)) : ?>
    
    <? if (count($m->user->studycourses) < 2) : ?>
        <? for ($i = 0; $i < 1; $i++) : ?>
            <?= htmlReady($m->user->studycourses[$i]->studycourse->name) ?>
            <?= htmlReady($m->user->studycourses[$i]->degree->name) ?>
            (<?= htmlReady($m->user->studycourses[$i]->semester) ?>)
        <? endfor ?>
    <? else : ?>
        <?= htmlReady($m->user->studycourses[0]->studycourse->name) ?>
        <?= htmlReady($m->user->studycourses[0]->degree->name) ?>
        (<?= htmlReady($m->user->studycourses[0]->semester) ?>)
        [...]
        <? foreach($m->user->studycourses as $studycourses) : ?>
            <? $course_res .= sprintf('- %s (%s)<br>',
                    htmlReady(trim($studycourses->studycourse->name . ' ' . $studycourses->degree->name)),
                    htmlReady($studycourses->semester)) ?>
        <? endforeach ?>
        <?= tooltipHtmlIcon('<strong>' . _('Weitere Studiengänge') . '</strong><br>' . $course_res) ?>
        <? unset($course_res); ?>
    <? endif ?>
<? endif ?>

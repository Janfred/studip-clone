<?php
if ($count = count($studycourses)) {
    $studycourse = $studycourses->first();
    echo sprintf(
        '%s (%s)',
        htmlReady(trim($studycourse->studycourse->name . ' ' . $studycourse->degree->name)),
        htmlReady($studycourse->semester)
    );;
    if ($count > 1) {
        echo '[...]';
        $course_res = implode('<br>', $studycourses->limit(1, PHP_INT_MAX)->map(function ($item) {
            return sprintf(
                '- %s (%s)<br>',
                htmlReady(trim($item->studycourse->name . ' ' . $item->degree->name)),
                htmlReady($item->semester)
            );
        }));
        echo tooltipHtmlIcon('<strong>' . _('Weitere Studiengänge') . '</strong><br>' . $course_res);
    }
}
?>

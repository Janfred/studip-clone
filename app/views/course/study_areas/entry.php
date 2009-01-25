<?
$id = htmlReady($area->getID());
$name = isset($show_path)
        ? htmlReady($area->getPath(' · '))
        : htmlReady($area->getName());
if($course_id){
	$course_start_time = Seminar::GetInstance($course_id)->getSemesterStartTime();
} else {
	$course_start_time = $_SESSION['sem_create_data']['sem_start_time'];
}
list(,$semester_id) = array_values(SemesterData::GetInstance()->getSemesterDataByDate($course_start_time));

?>
<input class="study_area_selection_add_<?= $id ?>"
        onclick="STUDIP.study_area_selection.add('<?= $id ?>','<?= htmlReady($course_id) ?>');return false;"
        type="image"
        name="study_area_selection[add][<?= $id ?>]"
        src="<?= Assets::image_path('move_left.gif') ?>"
        title="<?= _("Diesen Studienbereich zuordnen") ?>"
        alt="<?= _("Diesen Studienbereich zuordnen") ?>"
        <?= !$area->isAssignable() || $selection->includes($area)
            ? 'style="visibility:hidden;"' : '' ?> />
<? if (isset($show_link) && $show_link) : ?>
  <a onClick="STUDIP.study_area_selection.expandSelection('<?= $id ?>','<?= htmlReady($course_id) ?>');return false;"
     href="<?= URLHelper::getLink(isset($url) ? $url : '',
                   array('study_area_selection[selected]' => $area->getID())) ?>">
    <?= $name ?>
  </a>
<? else : ?>
  <?= $name ?>
<? endif ?>
<? if($area->isModule()) echo $area->getModuleInfoIcon($semester_id); ?>

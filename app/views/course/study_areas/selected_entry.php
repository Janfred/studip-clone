<?
$_id = htmlReady($area->getID());
if($course_id){
	$course_start_time = Seminar::GetInstance($course_id)->getSemesterStartTime();
} else {
	$course_start_time = $_SESSION['sem_create_data']['sem_start_time'];
}
list(,$semester_id) = array_values(SemesterData::GetInstance()->getSemesterDataByDate($course_start_time));
?>
<li id="study_area_selection_<?= $_id ?>" class="<?= TextHelper::cycle('odd', 'even') ?>">
  <input title="Zuordnung entfernen" alt="Zuordnung entfernen"
         onclick="STUDIP.study_area_selection.remove('<?= $_id ?>','<?= htmlReady($course_id) ?>');return false;"
         style="vertical-align: middle;"
         type="image"
         name="study_area_selection[remove][<?= $_id ?>]"
         src="<?= Assets::image_path('trash.gif') ?>" />
  <a onClick="STUDIP.study_area_selection.expandSelection('<?= htmlReady($area->getParentId()) ?>','<?= htmlReady($course_id) ?>');return false;"
     href="<?= URLHelper::getLink(isset($url) ? $url : '',
                                  array('study_area_selection[selected]' => $area->getParentId())) ?>">
    <?= htmlReady($area->getPath(' · ')) ?>
  </a>
  <? if($area->isModule()) echo $area->getModuleInfoIcon($semester_id); ?>
  <input type="hidden" name="study_area_selection[areas][]" class="study_area_selection_area" value="<?= $_id ?>" />
</li>


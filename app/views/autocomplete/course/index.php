<ul>
  <? foreach ($courses as $course) : ?>
    <li><span class="informal">
    <? if (strlen($course['VeranstaltungsNummer'])) : ?>
      <span class="number"><?= $course['VeranstaltungsNummer'] ?>: </span>
    <? endif ?>
    </span><?= $course['Name'] ?><span class="informal">

    <? if (isset($semesters[$course['start_time']])) : ?>
      <span class="semester">(<?= $semesters[$course['start_time']] ?>)</span>
    <? endif ?>
    <br/>

    <span class="lecturer"><?= $course['lecturer'] ?></span>
    <br/>

    <span class="comment"><?= text_excerpt($course['Beschreibung'], $search_term, 10) ?></span>
    </span></li>
  <? endforeach ?>
</ul>

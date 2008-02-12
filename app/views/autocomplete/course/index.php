<ul>
  <? foreach ($courses as $course) : ?>
    <li><span class="informal">
    <? if (strlen($course['VeranstaltungsNummer'])) : ?>
      <span class="number"><?= htmlready($course['VeranstaltungsNummer']) ?>: </span>
    <? endif ?>
    </span><?= htmlready(text_excerpt($course['Name'], $search_term, 20, 50)) ?><span class="informal">

    <? if (isset($semesters[$course['start_time']])) : ?>
      <span class="semester">(<?= htmlready($semesters[$course['start_time']]) ?>)</span>
    <? endif ?>
    <br/>

    <span class="lecturer"><?= htmlready(text_excerpt($course['lecturer'], $search_term, 20, 70)) ?></span>
    <br/>

    <span class="comment"><?= htmlready(text_excerpt($course['Beschreibung'], $search_term, 20, 70)) ?></span>
    </span></li>
  <? endforeach ?>
</ul>

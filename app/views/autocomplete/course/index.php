<ul>
  <? foreach ($courses as $course) : ?>
    <li><span class="informal">
    <? if (strlen($course['VeranstaltungsNummer'])) : ?>
      <span class="number"><?= htmlready($course['VeranstaltungsNummer']) ?>: </span>
    <? endif ?>
    </span><?= htmlready($course['Name']) ?><span class="informal">

    <? if (isset($semesters[$course['start_time']])) : ?>
      <span class="semester">(<?= htmlready($semesters[$course['start_time']]) ?>)</span>
    <? endif ?>
    <br/>

    <span class="lecturer"><?= htmlready(text_excerpt($course['lecturer'], 15)) ?></span>
    <br/>

    <span class="comment"><?= htmlready(text_excerpt($course['Beschreibung'], $search_term, 15)) ?></span>
    </span></li>
  <? endforeach ?>
</ul>

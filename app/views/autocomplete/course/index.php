<ul>
  <? foreach ($courses as $course) : ?>
    <li><span class="informal">
    <? if (strlen($course['VeranstaltungsNummer'])) : ?>
      <span class="number"><?= htmlspecialchars(studip_utf8encode($course['VeranstaltungsNummer'])) ?>: </span>
    <? endif ?>
    </span><?= htmlspecialchars(studip_utf8encode($course['Name'])) ?><span class="informal">

    <? if (isset($semesters[$course['start_time']])) : ?>
      <span class="semester">(<?= htmlspecialchars(studip_utf8encode($semesters[$course['start_time']])) ?>)</span>
    <? endif ?>
    <br>

    <span class="lecturer"><?= htmlspecialchars(studip_utf8encode(text_excerpt($course['lecturer'], $search_term, 20, 60))) ?></span>
    <br>

    <span class="comment"><?= htmlspecialchars(studip_utf8encode(text_excerpt($course['Beschreibung'], $search_term, 20, 60))) ?></span>

    <span class="seminar_id"><?= $course['seminar_id'] ?></span>

    </span></li>
  <? endforeach ?>
</ul>

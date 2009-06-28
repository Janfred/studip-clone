<ul>
  <? foreach ($courses as $course) : ?>
    <li><span class="informal">
    <?= htmlspecialchars(studip_utf8encode($course['Name'])) ?>
    <span class="seminar_id"><?= $course['seminar_id'] ?></span>
    </span></li>
  <? endforeach ?>
</ul>
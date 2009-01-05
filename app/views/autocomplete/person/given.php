<ul>
  <? foreach ($persons as $person) : ?>
    <li><?= htmlspecialchars(studip_utf8encode(($person))) ?></li>
  <? endforeach ?>
</ul>

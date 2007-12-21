<ul>
  <? foreach ($persons as $person) : ?>
    <li><?= htmlready($person['fullname']) ?></li>
  <? endforeach ?>
</ul>

<ul>
  <? foreach ($persons as $person) : ?>
    <li><?= htmlready($person) ?></li>
  <? endforeach ?>
</ul>

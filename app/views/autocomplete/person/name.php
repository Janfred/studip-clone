<? require_once 'lib/classes/Avatar.class.php' ?>
<ul>
  <? foreach ($persons as $person) : ?>
    <li><span class="informal">

        <?= Avatar::getAvatar($person['user_id'])->getImageTag(Avatar::SMALL) ?>
        <?= htmlready($person['title_front']) ?>
        <?= htmlready($person['Vorname']) ?>

      </span><?= htmlready($person['Nachname']) ?><span class="informal">

        <?= htmlready($person['title_rear']) ?>

        <span class="username"><?= htmlready($person['username']) ?></span>
        <span class="permission"><?= htmlready($person['perms']) ?></span>

      </span></li>
  <? endforeach ?>
</ul>

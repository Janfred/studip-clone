<ul>
  <? foreach ($persons as $person) : ?>
    <li><span class="informal">

        <?= Avatar::getAvatar($person['user_id'])->getImageTag(Avatar::SMALL) ?>
        <?= htmlspecialchars(studip_utf8encode(($person['title_front']))) ?>
        <?= htmlspecialchars(studip_utf8encode(($person['Vorname']))) ?>

      </span><?= htmlspecialchars(studip_utf8encode(($person['Nachname']))) ?><span class="informal">

        <?= htmlspecialchars(studip_utf8encode(($person['title_rear']))) ?>

        <span class="username"><?= htmlspecialchars(studip_utf8encode(($person['username']))) ?></span>
        <span class="permission"><?= htmlspecialchars(studip_utf8encode(($person['perms']))) ?></span>

      </span></li>
  <? endforeach ?>
</ul>

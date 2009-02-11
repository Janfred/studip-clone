<? if ($error) : ?>
    <em><?= $error ?></em>
<? else : ?>
    <ul>
        <? foreach($users as $user) : ?>
            <li>
                <a href="<?= URLHelper::getLink('about.php', array('username' => $user['username'])) ?>">
                    <?= htmlReady($user['fullname']) ?>
                </a>
                , E-Mail:
                <?= FixLinks(htmlReady($user['Email'])) ?>
            </li>
        <? endforeach ?>
    </ul>
<? endif ?>

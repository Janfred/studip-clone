<? if ($error) : ?>
    <em><?= $error ?></em>
<? else : ?>
    <a href="<?= URLHelper::getLink('about.php', array('username' => $username)) ?>">
        <?= $fullname?>
    </a>
    , E-Mail: <?= $email?>
<? endif ?>

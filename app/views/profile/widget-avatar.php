<div class="text-center">
    <?= $avatar->getImageTag(Avatar::NORMAL, [
        'class' => 'profile-avatar',
        'style' => ''
    ]) ?>
</div>
<div class="profile-sidebar-details">
    <? if ($kings): ?>
        <div><?= $kings ?></div>
    <? endif; ?>
        <div class="minor">
            <?= _('Profilbesuche:') ?>
            <?= number_format($views, 0, ',', '.') ?>
        </div>
    <? if ($score && $score_title): ?>
        <div class="minor">
            <a href="<?= URLHelper::getLink('dispatch.php/score') ?>" title="<?= _('Zur Rangliste') ?>">
                <div><?= _('Stud.IP-Punkte') ?>: <?= number_format($score, 0, ',', '.') ?></div>
                <div><?= _('Rang') ?>: <?= htmlReady($score_title) ?></div>
            </a>
        </div>
    <? endif; ?>
</div>

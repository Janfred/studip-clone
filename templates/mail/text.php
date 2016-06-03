<?
# Lifter010: TODO
?>
<?= $message ?>

<? if (is_array($attachments) && count($attachments)) : ?>

    <?= _("Dateianhänge:") ?>

    <? foreach ($attachments as $one) : ?>
        <?= $one['filename'] . ' (' . relsize($one['filesize'], false) . ')' ?>

        <?= GetDownloadLink($one['dokument_id'], $one['filename'], 7, 'force') ?>


    <? endforeach; ?>
<? endif; ?>


-- 
<?= sprintf(_("Diese E-Mail ist eine Kopie einer systeminternen Nachricht, die in Stud.IP an %s versendet wurde."), $rec_fullname) ?>

<?= sprintf(_("Sie erreichen Stud.IP unter %s"), $GLOBALS['ABSOLUTE_URI_STUDIP']) ?>

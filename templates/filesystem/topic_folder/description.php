<div>
    <?= _('Dieser Ordner ist ein themenbezogener Dateiordner.')?>
</div>
<?$dates = isset($topic) ? $topic->dates->getFullname() : [];?>
<? if (count($dates)) :?>
<?=_('Folgende Termine sind diesem Thema zugeordnet:') ?>
    <div>
        <strong>
            <?=join('; ', $dates)?>
        </strong>
    </div>
<? endif ?>
<?php if ($folder->start_time && !$folder->end_time) : ?>
    <strong>
        <?= sprintf(_('Sichtbar ab %s'),
            strftime('%x %X', $folder->start_time)) ?>
    </strong>
    <br>
<?php elseif (!$folder->start_time && $folder->end_time) : ?>
    <strong>
        <?= sprintf(_('Sichtbar bis %s'),
            strftime('%x %X', $folder->end_time)) ?>
    </strong>
    <br>
<?php elseif ($folder->start_time && $folder->end_time) : ?>
    <strong>
        <?= sprintf(_('Sichtbar von %s bis %s'),
            strftime('%X %x', $folder->start_time),
            dstrftime('%x %X', $folder->end_time)) ?>
    </strong>
    <br>
<?php endif ?>
<?php if (!$folder->isReadable() && $folder->isWritable()) : ?>
    <div>
        <?= _("Dieser Ordner ist ein Hausaufgabenordner. Es können nur Dateien eingestellt werden.") ?>
    </div>
<?php endif ?>
<?php if ($folder->isReadable() && !$folder->isWritable()) : ?>
    <div>
        <?= _('Ein Ordner für Materialien, welche nur zum Download zu Verfügung gestellt werden sollen.') ?>
    </div>
    <div>
        <?= _('Den Inhalt des Ordners können nur Lehrende und TutorInnen verändern.') ?>
        <?= _('Die normalen Teilnehmenden der Veranstaltung können diese Materialien nur herunterladen.') ?>
    </div>
<?php endif ?>
<label>
    <? if (count($own_files) > 0) : ?>
        <div>
            <?= _('Sie selbst haben folgende Dateien in diesen Ordner eingestellt:') ?>
            <ul>
                <? foreach ($own_files as $own_file) : ?>
                    <li><?= htmlReady($own_file->name) ?> - <?= strftime('%x %X', $own_file->chdate) ?></li>
                <? endforeach ?>
            </ul>
        </div>
    <? endif ?>

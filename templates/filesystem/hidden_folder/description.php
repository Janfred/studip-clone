<div><?= htmlReady($type) ?>:</div>
<br>
<label>
    <div><?= _('Ein unsichtbarer Ordner, welcher nur von Lehrenden und TutorInnen gesehen werden kann.') ?></div>
    <div><?= _('Der Ordner lässt sich auch für Studierende sichtbar schalten.') ?></div>
</label>
<label>
    <input disabled type="checkbox" <? if ($folder->isReadable()) echo 'checked'; ?>>
    <strong>r</strong> - <?= _('Lesen (Dateien können heruntergeladen werden)') ?>
</label>
<br>
<label>
    <input disabled type="checkbox" <? if ($folder->isWritable()) echo 'checked'; ?>>
    <strong>w</strong> - <?= _('Schreiben (Dateien können hochgeladen werden)') ?>
</label>
<br>
<label>
    <input disabled type="checkbox" <? if ($folder->isVisible()) echo 'checked'; ?>>
    <strong>x</strong> - <?= _('Sichtbarkeit (Ordner wird angezeigt)') ?>
</label>

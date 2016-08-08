<? $perm_dokumente = isset($perm_dokumente) ? $perm_dokumente : true; ?>
<fieldset>
    <legend><?= _('Referenzierte Dokumente und Materialien') ?></legend>
    <? if ($perm_dokumente) : ?>
    <div class="mvv-item-list-properties" style="display: none;">
        <div class="mvv-property-de"></div>
        <div class="mvv-property-en"></div>
    </div>
    <div>
        <?= $search_dokumente['html']->render(); ?>
        <? if (Request::submitted('search_dokumente')) : ?>
            <?= Icon::create('refresh', 'clickable', ['name' => 'reset_dokumente', 'data-qs_id' => $search_dokumente['id']])->asInput(); ?>
        <? else : ?>
            <?= Icon::create('search', 'clickable', ['name' => 'search_dokumente', 'data-qs_id' => $search_dokumente['id'], 'data-qs_name' => $search_dokumente['html']->getId(), 'class' => 'mvv-qs-button'])->asInput(); ?>
        <? endif; ?>
    </div>
    <? endif; ?>
    <ul id="dokumente_target" class="mvv-assigned-items mvv-with-properties mvv-dokumente sortable">
        <? if ($perm_dokumente) : ?>
        <li class="mvv-item-list-placeholder"<?= (empty($dokumente) ? '' : ' style="display: none;"') ?>><?= _('Fügen Sie Dokumente und Materialien hinzu.') ?></li>
        <? elseif (empty($dokumente)) : ?>
        <li class="mvv-item-list-placeholder"><?= _('Es wurden keine Dokumente zugeordnet.') ?></li>
        <? endif; ?>
        <? foreach ($dokumente as $dokument) : ?>
        <? if ($perm_dokumente) : ?>
        <li id="dokumente_<?= $dokument->dokument_id ?>" class="sort_items">
            <div style="flex: 9;">
                <?= htmlReady($dokument->document->name) ?>
            </div>
            <div style="flex: 1; text-align: right;" class="mvv-item-list-buttons">
                <a href="#" class="mvv-item-remove"><?= Icon::create('trash', 'clickable', array('title' => _('Dokument entfernen')))->asImg(); ?></a>
                <? /*
                <a data-dialog="buttons=false" href="<?= $controller->url_for('materialien/dokumente/ref_properties', $dokument->dokument_id, $dokument->range_id, $dokument->object_type) ?>" class="mvv-item-edit-properties"><?= Icon::create('edit', 'clickable', array('title' => _('Kommentar bearbeiten')))->asImg(); ?></a>
                <a data-dialog="size=auto;buttons=false" href="<?= $url . $dokument->dokument_id ?>" class="mvv-item-edit-properties"><?= Icon::create('edit', 'clickable', array('title' => _('Kommentar bearbeiten')))->asImg(); ?></a>
                 */?>                 
                 <a href="#" class="mvv-item-edit-properties"><?= Icon::create('edit', 'clickable', array('title' => _('Kommentar bearbeiten')))->asImg(); ?></a>
            </div>
            <? /*
            <div class="mvv-item-list-properties">
                <div class="mvv-property-de" <?= $dokument->kommentar? '' : 'style="display: none;"' ?>>
                        <?= htmlReady(mila($dokument->kommentar)) ?>
                </div>
                <div class="mvv-property-en" <?= $dokument->kommentar_en ? '' : 'style="display: none;"' ?>>
                    <?= htmlReady(mila($dokument->kommentar_en)) ?>
                </div>
            </div>
             */?>
            <fieldset class="mvv-item-document-comments" style="display: none;">
                <legend><?= _('Anmerkungen/Kommentare') ?></legend>
                <label>
                    <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>" style="vertical-align: top;">
                	<textarea <?//= $perm->disable('kommentar') ?>cols="60" rows="5" id="dokument_kommentar_<?= $dokument->dokument_id ?>" name="dokumente_properties[<?= $dokument->dokument_id ?>][kommentar]" class="add_toolbar resizable ui-resizable mvv-ref-properties"><?= htmlReady($dokument->kommentar) ?></textarea>
                </label>
                <label>
                    <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>" style="vertical-align: top;">
                    <textarea <?//= $perm->disable('kommentar_en') ?>cols="60" rows="5" id="dokument_kommentar_en_<?= $dokument->dokument_id ?>" name="dokumente_properties[<?= $dokument->dokument_id ?>][kommentar_en]" class="add_toolbar resizable ui-resizable mvv-ref-properties"><?= htmlReady($dokument->kommentar_en) ?></textarea>
                </label>
                <?= _('Die Änderungen werden erst gespeichert, wenn das Hauptformular gespeichert wurde!') ?>
            </fieldset>
            
            
            <input type="hidden" name="dokumente_items[]" value="<?= $dokument->dokument_id ?>">
        </li>
        <? else : ?>
        <li id="dokumente_<?= $dokument->dokument_id ?>">
            <div style="flex: 1;">
                <?= htmlReady($dokument->name) ?>
            </div>
            <input type="hidden" name="dokumente_items[]" value="<?= $dokument->dokument_id ?>">
        </li>
        <? endif; ?>
        <? endforeach; ?>
    </ul>
    <? if ($perm_dokumente) : ?>
    <div id="dokumente_edit-form-new">
        <?= _('Neues Dokument anlegen und hinzufügen') ?>
        <a data-dialog href="<?= URLHelper::getLink($controller->url_for('materialien/dokumente/dokument/')) ?>"><?= Icon::create('add', 'clickable', array('title' => _('Neues Dokument anlegen')))->asImg(); ?></a>
    </div>
    <div style="padding-top: 15px; width: 100%; max-width: 48em;">
    <?= _('Die Reihenfolge der zugeordneten Dokumente kann durch Anklicken und Ziehen geändert werden. Für die Zuordnung eines Dokumentes kann ein zusätzlicher Text angegeben werden.') ?>
    </div>
    <? endif; ?>
</fieldset>
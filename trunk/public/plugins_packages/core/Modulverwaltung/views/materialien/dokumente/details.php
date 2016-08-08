<td colspan="5">
    <table class="default nohover">
        <colgroup>
            <col style="width: 20%">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <td><strong><?= _('Name:') ?></strong></td>
                <td>
                    <div>
                        <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>"> <?= htmlReady($dokument->name) ?>
                    </div>
                    <? if ($dokument->name_en) : ?>
                    <div style="margin-top:10px;">
                        <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>"> <?= htmlReady($dokument->name_en) ?>
                    </div>
                    <? endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong><?= _('URL:') ?></strong></td>
                <td>
                    <?= formatLinks($dokument->url) ?>
                </td>
            </tr>
            <tr>
                <td><strong><?= _('Beschreibung:') ?></strong></td>
                <td>
                    <? if (!strlen($dokument->beschreibung) && !strlen($dokument->beschreibung)) : ?>
                        <?= _('Keine Beschreibung vorhanden.') ?>
                    <? else : ?>
                        <? if (strlen($dokument->beschreibung)) : ?>
                        <div>
                            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
                            <?= htmlReady($dokument->beschreibung) ?>
                        </div>
                        <? endif; ?>
                        <? if ($dokument->beschreibung_en) : ?>
                        <div style="margin-top:10px;">
                            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
                            <?= htmlReady($dokument->beschreibung_en) ?>
                        </div>
                        <? endif; ?>
                    <? endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2"><strong><?= _('Referenzierungen:') ?></strong><br>
                    <? if (!sizeof($relations)) : ?>
                        <?= _('Das Dokument wurde noch nicht referenziert.') ?>
                    <? else : ?>
                        <dl>
                            <? foreach ($relations as $object_type => $relation) : ?>
                            <dt>
                                <strong><?= htmlReady($object_type::getClassDisplayName()) ?></strong>
                            </dt>
                            <dd>
                                <ul>
                                <? foreach ($relation as $rel) : ?>
                                    <? $related_object = $object_type::getEnriched($rel['range_id']) ?>
                                    <li>
                                        <a href="<?= $this->controller->url_for('dispatch/index', strtolower($object_type), $rel['range_id']) ?>">
                                            <?= $related_object->getDisplayName() ?>
                                        </a>
                                    </li>
                                <? endforeach; ?>
                                </ul>
                            </dd>
                            <? endforeach; ?>
                        </dl>
                    <? endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong><?= _('Erstellt am:') ?></strong></td>
                <td>
                    <?= strftime('%x, %X', $dokument->mkdate) . ', ' ?>
                    <?= get_fullname($dokument->author_id) ?>
                    <?= ' (' . get_username($dokument->author_id) . ')' ?>
                </td>
            </tr>
            <? if ($dokument->mkdate != $dokument->chdate) : ?>
            <tr>
                <td><strong><?= _('Letzte Änderung am:') ?></strong></td>
                <td>
                    <?= strftime('%x, %X', $dokument->chdate) . ', ' ?>
                    <?= get_fullname($dokument->editor_id) ?>
                    <?= ' (' . get_username($dokument->editor_id) . ')' ?>
                </td>
            </tr>
            <? endif; ?>
        </tbody>
    </table>
</td>

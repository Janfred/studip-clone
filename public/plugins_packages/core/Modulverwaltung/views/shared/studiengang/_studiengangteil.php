<h3><?= htmlReady($stgteil->getDisplayName()) ?></h3>
<table class="default mvv-modul-details" id="<?= $stgteil->getId() ?>" data-mvv-id="<?= $stgteil->getId(); ?>" data-mvv-type="stgteil">
    <colgroup>
        <col style="width: 20%;">
        <col style="width: 10%;">
        <col style="width: 10%;">
        <col style="width: 30%;">
        <col style="width: 10%;">
    </colgroup>
    <tr>
        <th><?= _('Fach') ?></th>
        <th><?= _('Kredit-Punkte') ?></th>
        <th><?= _('Semesterzahl') ?></th>
        <th><?= _('Titelzusatz') ?></th>
        <th><?= _('Studienfachberater') ?></th>   
    </tr>
    <tr>
        <td data-mvv-field="mvv_stgteil.fach">
            <? if ($stgteil->fach) : ?>
                <?= htmlReady($stgteil->fach->name) ?>
            <? endif; ?>
        </td>
        <td data-mvv-field="mvv_stgteil.kp">
            <?= htmlReady($stgteil->kp) ?>
        </td>
        <td data-mvv-field="mvv_stgteil.semester">
            <?= htmlReady($stgteil->semester) ?>
        </td>
        <td data-mvv-field="mvv_stgteil.zusatz mvv_stgteil.zusatz_en">
            <img<?= $image_style ? ' style="' . $image_style . '"' : '' ?> src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <?= htmlReady($stgteil->zusatz) ?>
            <br>
            <img<?= $image_style ? ' style="' . $image_style . '"' : '' ?> src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <?= htmlReady($stgteil->zusatz_en) ?>   
        </td>
        <td data-mvv-field="mvv_stgteil.fachberater">
            <? if (empty($stgteil->fachberater)): ?>
                -
            <? else: ?>
                <? foreach ($stgteil->fachberater as $fachberater) : ?>
                    <?= htmlReady($fachberater->getFullname()) ?><br>
                <? endforeach; ?>
            <? endif; ?>
        </td>
    </tr>
</table>
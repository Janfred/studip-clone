<td colspan="4">
    <table class="default nohover">
        <colgroup>
            <col style="width: 20%;">
            <col style="width: 40%;">
            <col style="width: 40%;">
        </colgroup>
        <tbody>
            <tr>
                <td><strong><?= _('Name:') ?></strong></td>
                <td>
                    <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
                    <?= htmlReady($stgteilbezeichnung->name) ?>
                </td>
                <td>
                    <? if ($stgteilbezeichnung->name_en) : ?>
                    <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
                    <?= htmlReady($stgteilbezeichnung->name_en) ?>
                    <? endif; ?>
                </td>
            </tr>
            <? if ($stgteilbezeichnung->name_kurz_en || $stgteilbezeichnung->name_kurz) : ?>
            <tr>
                <td><strong><?= _('Kurzname:') ?></strong></td>
                <td>
                    <? if ($stgteilbezeichnung->name_kurz) : ?>
                    <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
                    <?= htmlReady($stgteilbezeichnung->name_kurz) ?>
                    <? endif; ?>
                </td>
                <td>
                    <? if ($stgteilbezeichnung->name_kurz_en) : ?>
                    <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
                    <?= htmlReady($stgteilbezeichnung->name_kurz_en) ?>
                    <? endif; ?>
                </td>
            </tr>
            <? endif; ?>
        </tbody>
    </table>
</td>
<table class="mvv-modul-details default nohover" data-mvv-id="<?= $modul->getId(); ?>" data-mvv-type="modul">
    <tbody>
        <? $modulSumme =  $modul->wl_selbst + $modul->wl_pruef ?>
        <tr>
            <td style="width: 20%;"><strong><?= _('Workload selbstgestaltete Arbeit') ?></strong></td>
            <td data-mvv-field="mvv_modul.wl_selbst mvv_modul_deskriptor.kommentar_wl_selbst"><?= htmlReady($modul->wl_selbst) ?> <?= MVVController::trim($modulDeskriptor->kommentar_wl_selbst) ? sprintf(" (%s)", formatReady($modulDeskriptor->kommentar_wl_selbst)) : '' ?></td>

        </tr>
        <tr>
            <td style="width: 20%;"><strong><?= _('Workload Prüfung incl. Vorbereitung') ?></strong></td>
            <td data-mvv-field="mvv_modul.wl_pruef mvv_modul_deskriptor.kommentar_wl_pruef"><?= htmlReady($modul->wl_pruef) ?> <?= MVVController::trim($modulDeskriptor->kommentar_wl_pruef) ? sprintf(" (%s)", formatReady($modulDeskriptor->kommentar_wl_pruef)) : '' ?></td>

        </tr>
        <tr>
            <td style="width: 20%;"><strong><?= _('Workload Insgesamt') ?></strong></td>
            <td><?= $modulSumme ?></td>
        </tr>
    </tbody>
</table>
<table class="mvv-modul-details" data-mvv-id="<?= $modulDeskriptor?$modulDeskriptor->getId():''; ?>" data-mvv-type="moduldeskriptor">
    <tbody>
        <? if (trim($modulDeskriptor->pruef_vorleistung)) : ?>
        <tr>
            <td style="width: 20%;"><strong><?= _('Prüfungsvorleistung') ?></strong></td>
            <td data-mvv-field="mvv_modul_deskriptor.pruef_vorleistung" ><?= formatReady($modulDeskriptor->pruef_vorleistung) ?></td>
        </tr>
        <? endif; ?>
        <tr>
            <td style="width: 20%;"><strong><?= _('Prüfungsform') ?></strong></td>
            <td data-mvv-field="mvv_modul_deskriptor.pruef_leistung"><?= formatReady($modulDeskriptor->pruef_leistung) ?></td>
        </tr>
        <tr>
            <td style="width: 20%;"><strong><?= _('Wiederholungsprüfung') ?></strong></td>
            <td data-mvv-field="mvv_modul_deskriptor.pruef_wiederholung"><?= formatReady($modulDeskriptor->pruef_wiederholung) ?></td>
        </tr>
    </tbody>
</table>

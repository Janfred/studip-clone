<table class="default mvv-modul-details nohover">
    <tr>
        <th class="mvv-modul-details-head" style="width: 30%"><?= $modul->code ?></th>
        <th class="mvv-modul-details-head" style="width: 30%"><?= $institut->name ?></th>
        <th class="mvv-modul-details-head" style="width: 40%"><?= sprintf("%d CP", $modul->kp) ?> </th>
    </tr>
    <tr>
        <td colspan="2">
            <?= $modul->getDisplayName() ?><br>
            <?= _('Lehrveranstaltungen') ?> <?= $semester['name'] ?>
        </td>
        <td>
            <dl>
            <? foreach ($modulVerantwortung as $gruppe): ?>
                <dt><?= $gruppe['name'] ?></dt>
                <? foreach ($gruppe['users'] as $user): ?>
                    <dd><?= ($user['name']) ?></dd>
                <? endforeach; ?>
                </dl>
            <? endforeach; ?>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 0;">
            <table class="default nohover" style="margin: -1px; padding: 0; border-collapse: collapse;">
                <? if (strlen($teilnahmeVoraussetzung) > 0): ?>
                    <tr>
                        <td style="width: 20%; font-weight: bold;"><?= _('Teilnahmevoraussetzungen') ?></td>
                        <td ><?= $teilnahmeVoraussetzung ?></td>
                    </tr>
                <? endif; ?>
                <? if (strlen($deskriptor->kommentar)) : ?>
                    <tr>
                        <td style="width: 20%; font-weight: bold;"><?= _('Hinweise') ?></td>
                        <td><?= formatReady($deskriptor->kommentar) ?></td>
                    </tr>
                <? endif; ?>
                <? if (strlen($deskriptor->ersatztext) > 0): ?>
                    <tr>
                        <td style="width: 20%; font-weight: bold;"> </td>
                        <td><?= formatReady($deskriptor->ersatztext) ?></td>
                    </tr>
                <? else: ?>

                    <? if ($modul->kapazitaet > 0): ?>
                        <tr>
                            <td style="width: 20%; font-weight: bold;"><?= _('Kapazität Modul') ?></td>
                            <td>
                                <?= $modul->kapazitaet ?>
                                <? if (strlen($deskriptor->kommentar_kapazitaet) > 0): ?>
                                    (<?= formatReady($deskriptor->kommentar_kapazitaet) ?>)
                                <? endif; ?>

                            </td>
                        </tr>
                    <? endif; ?>
                    <? if (strlen($pruef_ebene) > 0): ?>
                        <tr>
                            <td style="width: 20%; font-weight: bold;"><?= _('Prüfungsebene') ?></td>
                            <td><?= $pruef_ebene ?></td>
                        </tr>
                    <? endif; ?>
                    <? if (strlen($deskriptor->pruef_vorleistung)) : ?>
                        <tr>
                            <td style="width: 20%; font-weight: bold;"><?= _('Prüfungsvorleistung Modul') ?></td>
                            <td><?= formatReady($deskriptor->pruef_vorleistung) ?></td>
                        </tr>
                    <? endif; ?>
                    <? if (strlen($deskriptor->pruef_leistung)) : ?>
                        <tr>
                            <td style="width: 20%; font-weight: bold;"><?= _('Prüfungsleistung Modul') ?></td>
                            <td><?= formatReady($deskriptor->pruef_leistung) ?></td>
                        </tr>
                    <? endif; ?>
                    <? if (strlen($deskriptor->kompetenzziele)): ?>
                        <tr>
                            <td style="width: 20%; font-weight: bold;"><?= _('Kompetenzziele') ?></td>
                            <td><?= formatReady($deskriptor->kompetenzziele) ?></td>
                        </tr>
                    <? endif; ?>

                <? endif; ?>
            </table>
        </td>
    </tr>
    <? if ($type !== 3): ?>
        <tr>
            <? if ($type === 1): ?>
                <th><?= _('Modulteile') ?></th>
            <? endif; ?>
            <th <? if ($type === 2): ?> colspan="3" <? endif; ?> ><?= _('Semesterveranstaltungen') ?></th>
            <? if ($type === 1): ?>
                <th><?= _('Prüfungsleistung') ?></th>    
            <? endif; ?>
        </tr>
        <? foreach ($modulTeile as $lvGruppe): ?>
            <tr>
                <? if ($type === 1): ?>
                    <td>  
                        <b> <?= $lvGruppe['name'] ?> </b> 
                        <? if (strlen($lvGruppe['kommentar']) > 0): ?>
                            (<?= $lvGruppe['kommentar'] ?>)
                        <? endif; ?>
                        <? /* if ($lvGruppe['kapazitaet'] > 0): ?>
                            <br/>
                            <b><?= _('Kapazität') ?>: </b> <?= $lvGruppe['kapazitaet'] ?>
                        <? endif; */ ?>
                        <? if (strlen($lvGruppe['voraussetzung']) > 0): ?>
                            <br/>
                            <b><?= _('Teilnahmevoraussetzungen') ?>:</b> <?= $lvGruppe['voraussetzung'] ?>
                        <? endif; ?>
                    </td>
                <? endif; ?>
                <td  <? if ($type === 2): ?> colspan="3" <? endif; ?>>
                    <? foreach ($lvGruppe['lvGruppen'] as $gruppe): ?>


                        <? if (strlen($gruppe['alt_texte']) > 0): ?>
                            <b><?= formatReady($gruppe['alt_texte']) ?></b>
                        <? endif; ?>
                        <ul>  
                            <? foreach ($gruppe['courses'] as $seminar_id => $course): ?>
                            	<li>
                                    <a href="<?= URLHelper::getLink('details.php', array('sem_id' => $seminar_id)) ?>">
                                    <?= $course['VeranstaltungsNummer'] . ' - ' . $course['Name'] ?>
                                    </a>
                                </li>
                            <? endforeach; ?>
                        </ul>
                    <? endforeach; ?>
                </td>
                <? if ($type === 1): ?>
                    <td width="40%">
                        <? if (strlen($lvGruppe['pruef_vorleistung']) > 0) : ?>
                            <b><?= _('Prüfungsvorleistung') ?>:</b> <?= $lvGruppe['pruef_vorleistung'] ?>
                        <? endif; ?>
                        <? if (strlen($lvGruppe['pruef_leistung']) > 0) : ?>
                            <b><?= _('Prüfungsform') ?>:</b> <br/><?= $lvGruppe['pruef_leistung'] ?> (<?= $lvGruppe['anteil_note'] ?> %)
                        <? endif; ?>
                    </td>
                <? endif; ?>
            </tr>
        <? endforeach; ?>
    <? endif; ?>
    <? /* 
    <tr>
        <td colspan="3">
            <?=
            sprintf(_('In der Fassung des <b>%d</b>. Beschlusses vom <b>%s</b> (<b>%s</b>) / Version <b>%s</b>.')
                    , $modul->fassung_nr, date('d.m.Y', $modul->beschlussdatum), $GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'][$modul->fassung_typ]['name'], $modul->version)
            ?>
        </td>
    </tr>
    */ ?>
</table>

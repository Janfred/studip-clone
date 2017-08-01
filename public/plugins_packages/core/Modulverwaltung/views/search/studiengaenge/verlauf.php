<div style="padding-bottom: 20px;">
<?= $this->render_partial('search/breadcrumb') ?>
</div>
<? if ($studiengangTeilName) : ?>
    <h1><?= htmlReady($studiengangTeilName) ?></h1>
    <? if ($studiengang) : ?>
    <h3><?= sprintf(_('%s im Studiengang %s'), htmlReady($stgTeilBez->getDisplayName()), htmlReady($studiengang->getDisplayName())) ?></h3>
    <? endif; ?>
    <? $current_version = $versionen->findOneBy('id', $cur_version_id); ?>
    <? if ($current_version) : ?>
        <h4><?= $current_version->getDisplayName(); ?></h4>
    <? else : ?>
        <h4><?= htmlReady($versionen->first()->getDisplayName()) ?></h4>
    <? endif; ?>           
    <? $max_fachsemester = count($fachsemesterData) ? max($fachsemesterData) : 0; ?>
    <table class="mvv-modul-details default nohover">
        <thead>
            <tr >
                <th rowspan="2"><?= _('Name / CP') ?></th>
                <th rowspan="2"><?= _('Modul') ?></th>
                <th rowspan="2"><?= _('Modulteil') ?></th>
                <? if ($max_fachsemester) : ?>
                <th colspan="<?= $max_fachsemester ?>" align="center"><?= _('Semester') ?></th>
                <? endif; ?>
            </tr>
            <tr>
            <? for ($i = 1; $i <= $max_fachsemester; $i++) : ?>
                <th><?= $i ?></th>
            <? endfor; ?>
    		</tr>
        </thead>
        <tbody>
            <? foreach ($abschnitteData as $abschnitt_id => $abschnitt): ?>
                <? $displayedAbschnittName = false; ?>
                <? $ueberschrift = (mb_strlen($abschnitt['zwischenUeberschrift'])) ?>
                <?// if (!$ueberschrift): ?>
                <? if ($ueberschrift): ?>
                	<tr class="table_header">
                        <td colspan="<?= $max_fachsemester + 3 ?>"><?= htmlReady($abschnitt['zwischenUeberschrift']) ?></td>
                    </tr>
                <? endif; ?>
                           
                    <? foreach ($abschnitt['module'] as $modul_id => $modul): ?>
                        <? $displayedModulName = false; ?>
    
                        <? foreach ($modul['modulTeile'] as $modulTeil_id => $modulTeil): ?>
                            <? $displayedModulTeilName = false; ?>
                            <tr>
                                <? if (!$displayedAbschnittName) : ?>
                                    <? $displayedAbschnittName = true; ?>
                                    <td rowspan="<?= $abschnitt['rowspan'] ?: 1 ?>">
                                        <?= htmlReady($abschnitt['name']) ?><br/><?= $abschnitt['creditPoints'] ? $abschnitt['creditPoints'] . ' ' . _('CP') : '' ?>
                                        <? if (trim($abschnitt['kommentar'])) : ?>
                                            <a data-dialog title="<?= sprintf(_('%s (Kommentar)'), htmlReady($abschnitt['name'])) ?>" href="<?= $controller->url_for('search/studiengaenge/kommentar', $abschnitt_id) ?>">
                                                <?= Icon::create('log', 'clickable', array('title' => _('Vollständige Modulbeschreibung')))->asImg(); ?>
                                            </a>
                                        <? endif; ?>
                                    </td>
                                <? endif; ?>
                                <? if (!$displayedModulName) : ?>
                                    <? $displayedModulName = true; ?>
                                    <td rowspan="<?= count($modul['modulTeile']) ?>">
                                       <? // Anzeige der alternativen Bezeichnung aus mvv_stgteilabschnitt_modul ?>
                                        <? $abschnitt_modul = new StgteilabschnittModul(array($abschnitt_id, $modul_id)) ?>
                                        <a data-dialog title="<?= htmlReady($modul['name']) . ' (' . _('Vollständige Modulbeschreibung') . ')' ?>" href="<?= $controller->url_for('shared/modul/description/' . $modul_id, ['display_language' => ModuleManagementModel::getLanguage()]) ?>">
                                            <?= Icon::create('log', 'clickable', array('title' => _('Vollständige Modulbeschreibung')))->asImg(); ?>
                                        </a>
                                        <? if($modul['veranstaltungen']):?>                          
                                        <a data-dialog href="<?= $controller->url_for('shared/modul/overview', $modul_id, $active_sem->getId(), ['display_language' => ModuleManagementModel::getLanguage()])  ?>">
                                            <?= htmlReady($abschnitt_modul->getDisplayName()) ?>
                                        </a>
                                        <? else: ?>
                                            <?= htmlReady($abschnitt_modul->getDisplayName()) ?>
                                        <? endif; ?>
                                    </td>
                                <? endif; ?>
                                <td><?= htmlReady($modulTeil['name']) ?> </td>
                                <? for ($i = 1; $i <= $max_fachsemester; $i++) : ?>
                                    <? if ($modulTeil['fachsemester'][$fachsemesterData[$i]] == 'kann') : ?>
                                <td class="mvv-type-kann"><span title="<? printf(_('%s Semester (kann)'), $i . ModuleManagementModel::getLocaleOrdinalNumberSuffix($i)) ?>">o</span></td>
                                    <? elseif ($modulTeil['fachsemester'][$fachsemesterData[$i]] == 'soll') : ?>
                                <td class="mvv-type-soll"><span title="<? printf(_('%s Semester (soll)'), $i . ModuleManagementModel::getLocaleOrdinalNumberSuffix($i)) ?>">+</td>
                                    <? else : ?>
                                    <td class="mvv-type">&nbsp;</td>
                                    <? endif; ?>
                                <? endfor; ?>
                            </tr>
                        <? endforeach; ?>
                    <? endforeach; ?>
                <?/* else: ?>
                    <tr class="table_header">
                        <td colspan="<?= count($fachsemesterData) + 4 ?>"><?= htmlReady($abschnitt['zwischenUeberschrift']) ?></td>
                    </tr>
                <? endif; */?>
            <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>

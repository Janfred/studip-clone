<td colspan="6">
    <table class="default collapsable sortable" id="<?= $modul->id ?>">
        <colgroup>
            <col>
            <col span="2" style="width: 150px;">
        </colgroup>
        <? foreach ($modul->modulteile as $modulteil) : ?>
        <? $perm = MvvPerm::get($modulteil) ?>
        <tbody class="<?= ($modulteil_id == $modulteil->getId() ? 'not-collapsed' : 'collapsed') ?><?= $perm->haveFieldPerm('position') ? ' sort_items' : '' ?>" id="<?= $modulteil->getId() ?>">
            <tr class="header-row">
                <td class="toggle-indicator">
                <? if (count($modulteil->lvgruppen) || $perm->haveFieldPermLvgruppen(MvvPerm::PERM_CREATE)) : ?>
                        <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/modulteil_lvg', $modulteil->id) ?>"><?= htmlReady($modulteil->getDisplayName()) ?></a>
                <? else : ?>
                        <?= htmlReady($modulteil->getDisplayName()) ?>
                <? endif; ?>
                </td>
                <td class="dont-hide actions" style="white-space: nowrap;">
                <? if ($perm->havePermWrite()) : ?>
                    <? foreach ($modulteil->deskriptoren->pluck('sprache') as $language) : ?>
                    <? $lang = $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$language]; ?>
                    <a href="<?= $controller->url_for('/modulteil/' . join('/', array($modulteil->id, $institut_id)), array('display_language' => $language)) ?>">
                        <img src="<?= Assets::image_path('languages/lang_' . strtolower($language) . '.gif') ?>" alt="<?= $lang['name'] ?>" title="<?= $lang['name'] ?>">
                    </a>
                    <? endforeach; ?>
                <? endif; ?>
                </td>
                <td class="dont-hide actions" style="white-space: nowrap;">                
                <? if(MvvPerm::havePermCreate('Lvgruppe')
                            && $perm->haveFieldPermLvgruppen(MvvPerm::PERM_CREATE)) : ?>
                    <a data-dialog="title='<?= _('Neue LV-Gruppe anlegen') ?>'" href="<?= $controller->url_for('/new_lvgruppe', $modulteil->id) ?>">
                        <?= Icon::create('file+add', 'clickable', array('title' => _('Neue LV-Gruppe anlegen')))->asImg(); ?>
                    </a>
                <? endif; ?>
                <? if ($perm->havePermWrite()) : ?>
                    <a href="<?= $controller->url_for('/modulteil', $modulteil->id) ?>">
                        <?= Icon::create('edit', 'clickable', array('title' => _('Modulteil bearbeiten')))->asImg(); ?>
                    </a>
                <? endif; ?>
                <? if ($perm->havePermCreate()) : ?>
                    <a href="<?= $controller->url_for('/copy_modulteil', $modulteil->id) ?>">
                        <?= Icon::create('files', 'clickable', array('title' => _('Modulteil kopieren')))->asImg(); ?>
                    </a>
                <? endif; ?>
                <? if ($perm->havePermCreate()) : ?>
                    <a href="<?= $controller->url_for('/delete_modulteil', $modulteil->id) ?>">
                        <?= Icon::create('trash', 'clickable', array('title' => _('Modulteil löschen')))->asImg(); ?>
                    </a>
                <? endif; ?>
                </td>
            </tr>
            <? if (count($modulteil->lvgruppen) && $modulteil_id == $modulteil->id) : ?>
            <tr class="loaded-details nohover">
                <?= $this->render_partial('module/module/modulteil_lvg', compact('modulteil')) ?>
            </tr>
            <? endif; ?>
        </tbody>
        <? endforeach; ?>
    </table>
</td>

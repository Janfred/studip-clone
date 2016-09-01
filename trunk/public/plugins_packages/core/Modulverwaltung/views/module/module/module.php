<? foreach ($module as $modul) : ?>
    <? $perm = MvvPerm::get($modul) ?>
<tbody class="<?= ($modul->count_modulteile ? '' : 'empty ') ?><?= ($modul_id == $modul->getId()? 'not-collapsed' : 'collapsed') ?>">
<tr class="header-row" id="modul_<?= $modul->getId() ?>">
    <td class="toggle-indicator">    	
        <? $ampel_icon = $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$modul->stat]['icon'] ?>
        <? $ampelstatus = $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$modul->stat]['name'] ?>
    	<? if ($modul->count_modulteile) : ?>
        <? $details_url = $details_url ? $details_url : '/details'; ?>
        <a class="mvv-load-in-new-row" href="<?= $controller->url_for($details_url, $modul->getId()) ?>">
        <? if ($ampel_icon) : ?>
            <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
        <? endif; ?>
        <?= htmlReady($modul->getDisplayName()) ?> </a>
        <? else : ?>
        <? if ($ampel_icon) : ?>
            <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
        <? endif; ?>
        <?= htmlReady($modul->getDisplayName()) ?>
        <? endif; ?>
    </td>
    <td style="white-space:nowrap;" class="dont-hide"><?= htmlReady($modul->code) ?></td>
    <td style="text-align:center;" class="dont-hide"><?= htmlReady($modul->fassung_nr) ?></td>
    <td style="text-align: center;" class="dont-hide"><?= $modul->count_modulteile ?></td>    
    <td class="dont-hide actions">
    <? if ($perm->havePermRead()) : ?>
        <? foreach ($modul->deskriptoren->pluck('sprache') as $language) : ?>
        <? $lang = $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$language]; ?>
        <a href="<?= $controller->url_for('/modul/' . $modul->id . '/', array('display_language' => $language)) ?>">
            <img src="<?= Assets::image_path('languages/lang_' . strtolower($language) . '.gif') ?>" alt="<?= $lang['name'] ?>" title="<?= $lang['name'] ?>">
        </a>
        <? endforeach; ?>
    <? endif; ?>
    </td>
    <td class="dont-hide actions" style="white-space: nowrap;">
    <? if ($modul->stat == 'planung' && $perm->haveFieldPerm('stat')) : ?>
        <a data-dialog="title='<?= htmlReady($modul->getDisplayName()) ?>'" href="<?= $controller->url_for('/approve', $modul->id) ?>">
            <?= Icon::create('accept', 'clickable', array('title' => _('Modul genehmigen')))->asImg(); ?>
        </a>
    <? endif; ?>
    <? if ($perm->havePermRead()) : ?>
        <a data-dialog="size=auto;title='<?= htmlReady($modul->getDisplayName()) ?>'" href="<?= $controller->url_for('/description', $modul->id) ?>">
            <?= Icon::create('log', 'clickable', array('title' => _('Modulbeschreibung ansehen')))->asImg(); ?>
        </a>
    <? endif; ?>
    <? if ($perm->haveFieldPerm('modulteile', MvvPerm::PERM_CREATE)) : ?>
        <a href="<?= $controller->url_for('/modulteil', array('modul_id' => $modul->id)) ?>">
            <?= Icon::create('file+add', 'clickable', array('title' => _('Modulteil anlegen')))->asImg(); ?>
        </a>
    <? endif; ?>
    <? if ($perm->havePermWrite()) : ?>
        <a href="<?= $controller->url_for('/modul', $modul->id) ?>">
            <?= Icon::create('edit', 'clickable', array('title' => _('Modul bearbeiten')))->asImg(); ?>
        </a>
    <? endif; ?>
    <? if ($perm->havePermCreate()) : ?>
        <a href="<?= $controller->url_for('/copy', $modul->id) ?>">
            <?= Icon::create('files', 'clickable', array('title' => _('Modul kopieren')))->asImg(); ?>
        </a>
    <? endif; ?>
    <? if ($perm->havePermCreate()) : ?>
        <a href="<?= $controller->url_for('/delete', $modul->id) ?>">
            <?= Icon::create('trash', 'clickable', array('title' => _('Modul löschen')))->asImg(); ?>
        </a>
    <? endif; ?>
    </td>
</tr>
<? if ($modul->count_modulteile && $modul_id == $modul->id) : ?>
<tr class="loaded-details nohover">
    <?= $this->render_partial('module/module/details', compact('modul')) ?>
</tr>
<? endif; ?>
</tbody>
<? endforeach; ?>
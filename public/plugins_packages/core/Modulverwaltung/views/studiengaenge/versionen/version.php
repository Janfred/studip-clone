<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<? $perm = MvvPerm::get($version) ?>
<h3>
    <? if ($version->isNew()) : ?>
    <?= sprintf(_('Neue Version f�r Studiengangteil: %s'), htmlReady($stgteil->getDisplayName())) ?>
    <? else : ?>
    <?= sprintf(_('Version: %s'), htmlReady($version->getDisplayName())) ?>
    <? endif; ?>
</h3>
<!-- <form class="catch-change" action="<?= $controller->url_for('studiengaenge/versionen/version/' . $version->getId()) ?>" method="post"> -->
<form class="default" action="<?= $controller->url_for('/version', $stgteil->id, $version->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('G�ltigkeit') ?></legend>
        <label>
            <?= _('von Semester:') ?>
            <? if ($perm->haveFieldPerm('start_sem')) : ?> 
            <select name="start_sem" size="1">
                <option value=""><?= _('-- Semester w�hlen --') ?></option>
            <? foreach ($semester as $sem) : ?>
                <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id == $version->start_sem ? ' selected' : '') ?>>
                    <?= htmlReady($sem->name) ?>
                </option>
            <? endforeach; ?>
            </select>
            <? else : ?>
                <? $sem = Semester::find($version->start_sem) ?>
                <?= htmlReady($sem->name) ?>
                <input type="hidden" name="start_sem" value="<?= $version->start_sem ?>">
            <? endif; ?>
        </label>
        <label>
            <?= _('bis Semester:') ?>
            <? if ($perm->haveFieldPerm('end_sem')) : ?> 
            <select name="end_sem" size="1">
                <option value=""><?= _('unbegrenzt g�ltig') ?></option>
            <? foreach ($semester as $sem) : ?>
                <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id == $version->end_sem ? ' selected' : '') ?>>
                    <?= htmlReady($sem->name) ?>
                </option>
            <? endforeach; ?>
            </select>
            <? else : ?>
                <? if ($version->end_sem != "") : ?>
                    <? $sem = Semester::find($version->end_sem) ?>
                    <?= htmlReady($sem->name) ?>
                <? else : ?>
                    <?= _('unbegrenzt g�ltig') ?>
                <? endif; ?>
                <input type="hidden" name="end_sem" value="<?= $version->end_sem ?>">
            <? endif; ?>
        </label>
        <div><?= _('Das Endsemester wird nur angegeben, wenn die Version abgeschlossen ist.') ?></div>
        <label>
            <?= _('Beschlussdatum:') ?>
            <? if ($perm->haveFieldPerm('beschlussdatum')) : ?>
            <input type="text" name="beschlussdatum" value="<?= ($version->beschlussdatum ? strftime('%d.%m.%Y', $version->beschlussdatum) : '') ?>" placeholder="<?= _('TT.MM.JJJJ') ?>" class="with-datepicker">
            <? else : ?>
            <?= ($version->beschlussdatum ? strftime('%d.%m.%Y', $version->beschlussdatum) : '') ?>
            <input type="hidden" name="beschlussdatum" value="<?= ($version->beschlussdatum ? strftime('%d.%m.%Y', $version->beschlussdatum) : '') ?>">
            <? endif; ?>
        </label>
        <label><?= _('Fassung:') ?></label>
        <select<?= $perm->disable('fassung_nr') ?> name="fassung_nr" style="display: inline-block; width: 5em;">
            <option value="">--</option>
        <? foreach (range(1, 30) as $nr) : ?>
            <option<?= $nr == $version->fassung_nr ? ' selected' : '' ?> value="<?= $nr ?>"><?= $nr ?>.</option>
        <? endforeach; ?>
        </select>
        <? if ($perm->haveFieldPerm('fassung_typ')):?>
        <select style="display: inline-block; max-width: 40em;" name="fassung_typ">
            <option value="0">--</option>
        <? foreach ($GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'] as $key => $entry) : ?>
            <option value="<?= $key ?>"<?= $key == $version->fassung_typ ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
        <? endforeach; ?>
        </select>
        <? else: ?>            
        <?= ($version->fassung_typ == '0' ? '--' : $GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'][$version->fassung_typ]['name']) ?>
        <input type="hidden" name="fassung_typ" value="<?= $version->fassung_typ ?>">
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Code') ?></legend>
            <input <?= $perm->disable('code') ?> type="text" name="code" id="code" value="<?= htmlReady($version->code) ?>" maxlength="100">
    </fieldset>
    <fieldset>
        <legend><?= _('Beschreibung') ?></legend>
        <label for="beschreibung">
            <?= Assets::img('languages/lang_de.gif'); ?>
            <? if($perm->haveFieldPerm('beschreibung', MvvPerm::PERM_WRITE)) : ?>
            <textarea cols="60" rows="5" name="beschreibung" id="beschreibung" class="add_toolbar ui-resizable"><?= htmlReady($version->beschreibung) ?></textarea>
            <? else : ?>
            <textarea readonly cols="60" rows="5" name="beschreibung" id="beschreibung" class="ui-resizable"><?= htmlReady($version->beschreibung) ?></textarea>
            <? endif; ?>
        </label>
        <label for="beschreibung_en">
            <?= Assets::img('languages/lang_en.gif'); ?>
            <? if($perm->haveFieldPerm('beschreibung_en', MvvPerm::PERM_WRITE)) : ?>
            <textarea cols="60" rows="5" name="beschreibung_en" id="beschreibung_en" class="add_toolbar ui-resizable"><?= htmlReady($version->beschreibung_en) ?></textarea>
            <? else : ?>
            <textarea readonly cols="60" rows="5" name="beschreibung_en" id="beschreibung_en" class="ui-resizable"><?= htmlReady($version->beschreibung_en) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <? $url = $controller->url_for('/dokumente_properties'); ?>
    <? $perm_dokumente = $perm->haveFieldPerm('document_assignments', MvvPerm::PERM_CREATE) ?>
    <?= $this->render_partial('shared/form_dokumente', compact('search_dokumente', 'dokumente', 'url', 'perm_dokumente')) ?>
    <fieldset>
        <legend><?= _('Status der Bearbeitung') ?></legend>
        <input type="hidden" name="status" value="<?= $version->stat ?>">
        <? foreach ($GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'] as $key => $status_bearbeitung) : ?>
        <? // The MVVAdmin have always PERM_CREATE for all fields ?>
        <? if ($perm->haveFieldPerm('stat', MvvPerm::PERM_CREATE) && $version->stat != 'planung') : ?>
        <label>
            <input type="radio" name="status" value="<?= $key ?>"<?= ($version->stat == $key ? ' checked' : '') ?>>
            <?= $status_bearbeitung['name'] ?>
        </label>
        <? elseif ($perm->haveFieldPerm('stat', MvvPerm::PERM_WRITE) && $version->stat != 'planung') : ?>
        <label>
            <input <?= ($version->stat == 'ausgelaufen' && $key == 'genehmigt')  ? 'disabled' :'' ?> type="radio" name="status" value="<?= $key ?>"<?= ($version->stat == $key ? ' checked' : '') ?>>
            <?= $status_bearbeitung['name'] ?>
        </label>
        <? elseif($version->stat == $key) : ?>
            <?= $status_bearbeitung['name'] ?>
        <? endif; ?>
        <? endforeach; ?>
        <label for="kommentar_status" style="vertical-align: top;"><?= _('Kommentar:') ?></label>
        <? if($perm->haveFieldPerm('kommentar_status', MvvPerm::PERM_WRITE)) : ?>
        <textarea cols="60" rows="5" name="kommentar_status" id="kommentar_status" class="add_toolbar ui-resizable"><?= htmlReady($version->kommentar_status) ?></textarea>
        <? else : ?>
        <textarea disabled cols="60" rows="5" name="kommentar_status" id="kommentar_status" class="ui-resizable"><?= htmlReady($version->kommentar_status) ?></textarea>
        <? endif; ?>
    </fieldset>
    <footer data-dialog-button>
        <? if ($version->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
            <?= Button::createAccept(_('anlegen'), 'store', array('title' => _('Version anlegen'))) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($perm->havePermWrite()) : ?>
            <?= Button::createAccept(_('�bernehmen'), 'store', array('title' => _('�nderungen �bernehmen'))) ?>
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('abbrechen'), $cancel_url, array('title' => _('zur�ck zur �bersicht'))) ?>
    </footer>
</form>
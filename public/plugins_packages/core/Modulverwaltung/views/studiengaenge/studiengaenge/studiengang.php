<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<?= $controller->renderMessages() ?>
<? $perm = MvvPerm::get($studiengang) ?>
<h3>
    <? if ($studiengang->isNew()) : ?>
    <?= _('Neuer Studiengang') ?>
    <? else : ?>
    <?= sprintf(_('Studiengang: %s'), htmlReady($studiengang->getDisplayName())) ?>
    <? endif; ?>
</h3>
<form class="default" action="<?= $controller->url_for('/studiengang', $studiengang->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Name des Studiengangs') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <? if ($perm->haveFieldPerm('name', MvvPerm::PERM_WRITE)) : ?>
            <?= $search ?>
            <? else : ?>
            <input readonly type="text" name="fach_id_parameter"  size="60" maxlength="255" value="<?= htmlReady($studiengang->name) ?>">
            <? endif; ?>
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <input<?= $perm->disable('name_en') ?> type="text" name="name_en" id="name_en" value="<?= htmlReady($studiengang->name_en) ?>" size="60" maxlength="255">
        </label>
        <div style="width: 100%; max-width: 48em;">
        <?= _('Es kann ein beliebiger Name eingegeben werden. Soll der Name mit dem eines Fachs übereinstimmen, geben Sie den Namen des Fachs ein, und wählen Sie das Fach aus der Liste. Es werden dann automatisch die weiteren Bezeichnungen aus den Daten des Fachs übernommen.') ?>
        </div>
    </fieldset>
    <fieldset>
        <legend><?= _('Kurzbezeichnung') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <input<?= $perm->disable('name_kurz') ?> type="text" id="name_kurz" name="name_kurz" size="20" maxlength="20" value="<?= htmlReady($studiengang->name_kurz) ?>">
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <input<?= $perm->disable('name_kurz_en') ?> type="text" id="name_kurz_en" name="name_kurz_en" size="20" maxlength="20" value="<?= htmlReady($studiengang->name_kurz_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Gültigkeit') ?></legend>
        <label>
            <?= _('von Semester:') ?>
            <? if ($perm->haveFieldPerm('start')) : ?> 
            <select name="start" size="1">
                <option value=""><?= _('-- Semester wählen --') ?></option>
            <? foreach ($semester as $sem) : ?>
                <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id == $studiengang->start ? ' selected' : '') ?>>
                    <?= htmlReady($sem->name) ?>
                </option>
            <? endforeach; ?>
            </select>
            <? else : ?>
                <? $sem = Semester::find($studiengang->start) ?>
                <?= htmlReady($sem->name) ?>
                <input type="hidden" name="start" value="<?= $studiengang->start ?>">
            <? endif; ?>
        </label>
        <label>
            <?= _('bis Semester:') ?>
            <? if ($perm->haveFieldPerm('end')) : ?> 
            <select name="end" size="1">
                <option value=""><?= _('unbegrenzt gültig') ?></option>
            <? foreach ($semester as $sem) : ?>
                <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id == $studiengang->end ? ' selected' : '') ?>>
                    <?= htmlReady($sem->name) ?>
                </option>
            <? endforeach; ?>
            </select>
            <? else : ?>
                <? if ($studiengang->end != '') : ?>
                    <? $sem = Semester::find($studiengang->end) ?>
                    <?= htmlReady($sem->name) ?>
                <? else : ?>
                    <?= _('unbegrenzt gültig') ?>
                <? endif; ?>                    
                <input type="hidden" name="end" value="<?= $studiengang->end ?>">
            <? endif; ?>
        </label>
        <div><?= _('Das Endsemester wird nur angegeben, wenn der Studiengang abgeschlossen ist.') ?></div>
        <label>
            <?= _('Beschlussdatum:') ?>
            <? if ($perm->haveFieldPerm('beschlussdatum')) : ?>
            <input type="text" name="beschlussdatum" value="<?= ($studiengang->beschlussdatum ? strftime('%d.%m.%Y', $studiengang->beschlussdatum) : '') ?>" placeholder="<?= _('TT.MM.JJJJ') ?>" size="15" class="with-datepicker">
            <? else : ?>
            <?= ($studiengang->beschlussdatum ? strftime('%d.%m.%Y', $studiengang->beschlussdatum) : '') ?>
            <input type="hidden" name="beschlussdatum" value="<?= ($studiengang->beschlussdatum ? strftime('%d.%m.%Y', $studiengang->beschlussdatum) : '') ?>">
            <? endif; ?>
        </label>
        <label><?= _('Fassung:') ?></label>
        <select<?= $perm->disable('fassung_nr') ?> name="fassung_nr" style="display: inline-block; width: 5em;">
            <option value="">--</option>
        <? foreach (range(1, 30) as $nr) : ?>
            <option<?= $nr == $studiengang->fassung_nr ? ' selected' : '' ?> value="<?= $nr ?>"><?= $nr ?>.</option>
        <? endforeach; ?>
        </select>
        <? if ($perm->haveFieldPerm('fassung_typ')):?>
        <select style="display: inline-block; max-width: 40em;" name="fassung_typ">
            <option value="0">--</option>
        <? foreach ($GLOBALS['MVV_STUDIENGANG']['FASSUNG_TYP'] as $key => $entry) : ?>
            <option value="<?= $key ?>"<?= $key == $studiengang->fassung_typ ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
        <? endforeach; ?>
        </select>
        <? else: ?>            
        <?= ($studiengang->fassung_typ == '0' ? '--' : $GLOBALS['MVV_STUDIENGANG']['FASSUNG_TYP'][$studiengang->fassung_typ]['name']) ?>
        <input type="hidden" name="fassung_typ" value="<?= $studiengang->fassung_typ ?>">
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Beschreibung') ?></legend>
        <label for="beschreibung">
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPerm('beschreibung', MvvPerm::PERM_WRITE)) : ?>
            <textarea cols="60" rows="5" name="beschreibung" id="beschreibung" class="add_toolbar ui-resizable"><?= htmlReady($studiengang->beschreibung) ?></textarea>
            <? else : ?>
            <textarea readonly cols="60" rows="5" name="beschreibung" id="beschreibung" class="ui-resizable"><?= htmlReady($studiengang->beschreibung) ?></textarea>
            <? endif; ?>
        </label>
        <br>
        <label for="beschreibung_en">
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPerm('beschreibung_en', MvvPerm::PERM_WRITE)) : ?>
            <textarea cols="60" rows="5" name="beschreibung_en" id="beschreibung_en" class="add_toolbar ui-resizable"><?= htmlReady($studiengang->beschreibung_en) ?></textarea>
            <? else : ?>
            <textarea readonly cols="60" rows="5" name="beschreibung_en" id="beschreibung_en" class="ui-resizable"><?= htmlReady($studiengang->beschreibung_en) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Studiengangteile') ?></legend>
            <? if ($perm->haveFieldPerm('typ', MvvPerm::PERM_WRITE)) : ?>
            <label><input id="stg_typ" class="mvv_toggle_hide" type="radio" name="stg_typ"<?=
            ($studiengang->typ != 'mehrfach' ? ' checked' : '') ?> value="einfach"> <?= _('Einfach-Studiengang (Diesem Studiengang wird ein oder mehrere Studiengangteil(e) direkt zugewiesen)') ?></label>
            <? else : ?>
            <input type="hidden" name="stg_typ" value="<?= htmlReady($studiengang->typ) ?>">
            <? endif; ?>
            <? if ($perm->haveFieldPerm('typ', MvvPerm::PERM_WRITE)) : ?>
            <label><input class="mvv_toggle_hide" type="radio" name="stg_typ"<?=
            ($studiengang->typ == 'mehrfach' ? ' checked' : '') ?> value="mehrfach"> <?= _('Mehrfach-Studiengang (Diesem Studiengang können mehrere Studiengangteile in Abschnitten zugewiesen werden)') ?></label>
            <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Abschluss') ?></legend>
        <? if ($perm->haveFieldPerm('abschluss_id')) : ?>
        <select id="abschluss_id" name="abschluss_id" size="1">
            <option value=""><?= _('-- bitte wählen --') ?></option>
            <? foreach ($abschluesse as $abschluss) : ?>
            <option <?= ($abschluss['abschluss_id'] == $studiengang->abschluss_id ? 'selected ' : '') ?>value="<?= $abschluss['abschluss_id'] ?>"><?= htmlReady($abschluss['name']) ?></option>
            <? endforeach; ?>
        </select>
        <? else: ?>
            <? $abschluss = Abschluss::find($studiengang->abschluss_id)?>
            <?= htmlReady($abschluss['name']) ?>
            <input type="hidden" name="abschluss_id" value="<?= $studiengang->abschluss_id ?>">
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Verantwortliche Einrichtung') ?></legend>
        <? if($perm->haveFieldPerm('institut_id', MvvPerm::PERM_WRITE)): ?>
        <?= $search_institutes->render(); ?>
        <? if (Request::submitted('search_institutes')) : ?>
            <?= Icon::create('refresh', 'clickable', ['name' => 'reset_institutes', 'data-qs_id' => $search_institutes_id])->asInput(); ?>
        <? else : ?>
            <?= Icon::create('search', 'clickable', ['name' => 'search_institutes', 'data-qs_id' => $search_institutes_id, 'data-qs_name' => $search_institutes->getId(), 'class' => 'mvv-qs-button'])->asInput(); ?>
        <? endif; ?>
        <? endif;?>
        <ul id="institut_target" class="mvv-assigned-items mvv-assign-single mvv-institute">
            <li class="mvv-item-list-placeholder"<?= ($studiengang->institut_id ? ' style="display: none;"' : '') ?>><?= _('Bitte eine Einrichtung suchen und zuordnen.') ?></li>
            <? if ($studiengang->institut_id) : ?>
            <li id="institut_<?= $studiengang->institut_id ?>">
                <div class="mvv-item-list-text">
                    <? if ($institut) : ?>
                    <?= htmlReady($institut->getDisplayName()) ?>
                    <? else: ?>
                    <?= _('unbekannte Einrichtung') ?>
                    <? endif; ?>
                </div>
                <? if($perm->haveFieldPerm('institut_id', MvvPerm::PERM_WRITE)): ?>
                <div class="mvv-item-list-buttons">
                    <a href="#" class="mvv-item-remove"><?= Icon::create('trash', 'clickable', array('title' => _('Einrichtung entfernen')))->asImg(); ?></a>
                </div>
                <? endif;?>
                <input type="hidden" name="institut_item" value="<?= $studiengang->institut_id ?>">
            </li>
            <? endif; ?>
        </ul>
    </fieldset>
    <?= $this->render_partial('shared/form_dokumente', array('perm_dokumente' => $perm->haveFieldPerm('document_assignments', MvvPerm::PERM_CREATE))) ?>
    <fieldset>
        <legend><?= _('Status der Bearbeitung') ?></legend>
        <? if($perm->haveFieldPerm('stat', MvvPerm::PERM_WRITE) && $studiengang->stat != 'planung'): ?>
        <? foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status_bearbeitung) : ?>
        <label>
            <input <?= ($studiengang->stat == 'ausgelaufen' && $key == 'genehmigt')  ? 'disabled' :'' ?> type="radio" name="status" value="<?= $key ?>"<?= $studiengang->stat == $key ? ' checked' : '' ?>>
            <?= $status_bearbeitung['name'] ?>
        </label>
        <? endforeach; ?>
        <? else : ?>
        <div>
            <?= $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$studiengang->stat]['name'] ?>
            <input type="hidden" name="status" value="<?= $studiengang->stat ?>">
        </div>
        <? endif; ?>
        <br>
        <label for="kommentar_status" style="vertical-align: top;"><?= _('Kommentar:') ?></label>
        <? if($perm->haveFieldPerm('kommentar_status', MvvPerm::PERM_WRITE)): ?>
        <textarea cols="60" rows="5" name="kommentar_status" id="kommentar_status" class="add_toolbar ui-resizable"><?= htmlReady($studiengang->kommentar_status) ?></textarea>
            <? else: ?>
            <textarea readonly cols="60" rows="5" name="kommentar_status" id="kommentar_status" class="ui-resizable"><?= htmlReady($studiengang->kommentar_status) ?></textarea>
            <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Schlagworte') ?></legend>
        <textarea <?= $perm->disable('schlagworte') ?> cols="60" rows="5" name="schlagworte" id="schlagworte" class="ui-resizable"><?= htmlReady($studiengang->schlagworte) ?></textarea>
        <div><?= _('Hier können zusätzlich Schlagworte angegeben werden, die in der Suche berücksichtigt werden.') ?></div>
    </fieldset>
    <footer>
    <? if ($studiengang->isNew()) : ?>
        <? if ($perm->havePermCreate()) : ?>
            <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Studiengang anlegen'))) ?>
        <? endif; ?>
    <? else : ?>
        <? if ($perm->havePermWrite()) : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
    <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>
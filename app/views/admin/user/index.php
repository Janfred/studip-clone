<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;
?>


<form action="<?= $controller->url_for('admin/user') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Benutzerverwaltung') ?></legend>

        <label>
            <?= _('Benutzername') ?>
            <input name="username" type="text" value="<?= htmlReady($request['username']) ?>">
        </label>

        <label>
            <?= _('E-Mail') ?>
            <input name="email" type="text" value="<?= htmlReady($request['email']) ?>">
        </label>

        <label>
            <?= _('Status')?>

            <select name="perm">
            <? foreach(words('alle user autor tutor dozent admin root') as $one): ?>
                <option value="<?= $one ?>" <?= ($request['perm'] === $one) ? 'selected' : '' ?>>
                    <?= ($one === 'alle') ? _('alle') : $one ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
            <input type="checkbox" name="locked" value="1" <?=  ($request['locked']) ?  'checked' : '' ?>>
            <?= _('nur gesperrt') ?>
        </label>

        <label>
            <?= _('Vorname') ?>
            <input name="vorname" type="text" value="<?= htmlReady($request['vorname']) ?>">
        </label>

        <label>
            <?= _('Nachname') ?>
            <input name="nachname" type="text" value="<?= htmlReady($request['nachname']) ?>">
        </label>

        <label for="inactive">
            <?= _('inaktiv') ?>
        </label>
        <section class="hgroup size-m">
            <select name="inaktiv" class="size-s">
            <? foreach(array('<=' => '>=', '=' => '=', '>' => '<', 'nie' =>_('nie')) as $i => $one): ?>
                <option value="<?= htmlready($i) ?>" <?= ($user['inaktiv'] === $i) ? 'selected' : '' ?>>
                    <?= htmlReady($one) ?>
                </option>
            <? endforeach; ?>
            </select>

            <label>
                <input name="inaktiv_tage" type="number" id="inactive"
                       value="<?= htmlReady($request['inaktiv_tage']) ?>">
                <?= _('Tage') ?>
            </label>
        </section>

    </fieldset>

    <fieldset class="collapsable <?= (!$advanced) ?  'collapsed' : '' ?>">
        <legend><?= _('Erweiterte Suche') ?></legend>
        <label for="institute">
            <?=_('Einrichtung')?>
            <select name="institute">
                <option value=""><?= _('Alle')?></option>
                <? foreach($institutes as $institute) : ?>
                    <option value="<?= $institute['Institut_id']?>" <?= $request['institute'] == $institute['Institut_id'] ? 'selected' : ''?>>
                        <?= htmlReady($institute['Name'])?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Nutzerdomäne') ?>

            <select name="userdomains">
                <option value=""><?= _('Alle') ?></option>
                <option value="null-domain" <?= ($request['userdomains'] === 'null-domain') ? 'selected' : '' ?>>
                    <?= _('Ohne Domäne') ?>
                </option>
            <? foreach ($userdomains as $one): ?>
                <option value="<?= htmlReady($one->getId()) ?>" <?= ($request['userdomains'] === $one->getId()) ? 'selected' : ''?>>
                    <?= htmlReady($one->getName() ?: $one->getId()) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
        <label>
            <?=_('Abschluss')?>
            <select name="degree">
                <option value=""><?=_('Alle')?></option>
                <? foreach($degrees as $degree) : ?>
                    <option value="<?= $degree->id ?>" <?= $request['degree'] == $degree->id ? 'selected' : ''?>><?=htmlReady($degree->name)?></option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?=_('Fach')?>
            <select name="studycourse">
                <option value=""><?=_('Alle')?></option>
                <? foreach($studycourses as $studycourse) : ?>
                    <option value="<?= $studycourse->id ?>" <?= $request['studycourse'] == $studycourse->id ? 'selected' : ''?>><?=htmlReady($studycourse->name)?></option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Authentifizierung') ?>

            <select name="auth_plugins">
               <option value=""><?= _('Alle') ?></option>
               <option value="preliminary"><?= _('vorläufig')?></option>
           <? foreach ($available_auth_plugins as $key => $val): ?>
                <option value="<?= $key ?>" <?= $request['auth_plugins'] === $key ? 'selected' : '' ?>>
                    <?= htmlReady($val) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

    <? foreach ($datafields as $datafield): ?>
        <label>
            <?= htmlReady($datafield->name) ?>

        <? if ($datafield->type === 'bool'): ?>
            <section class="hgroup size-m">
                <label>
                    <input type="radio" name="<?= $datafield->id ?>" value="" <?= (mb_strlen($request[$datafield->id]) === 0) ? 'checked' : '' ?>>
                    <?= _('egal') ?>
                </label>
                <label>
                    <input type="radio" name="<?= $datafield->id ?>" value="1" <?= ($request[$datafield->id] === '1') ? 'checked' : '' ?>>
                    <?= _('ja') ?>
                </label>
                <label>
                    <input type="radio" name="<?= $datafield->id ?>" value="0" <?= ($request[$datafield->id] === '0') ? 'checked' : '' ?>>
                    <?= _('nein') ?>
                </label>
            </section>
        <? elseif ($datafield->type === 'selectbox' || $datafield->type === 'radio') : ?>
            <? $datafield_entry = DataFieldEntry::createDataFieldEntry($datafield);?>
            <select name="<?= $datafield->id ?>">
                <option value="---ignore---"><?= _('alle') ?></option>
            <? foreach ($datafield_entry->type_param as $pkey => $pval) :?>
                <? $value = $datafield_entry->is_assoc_param ? (string) $pkey : $pval; ?>
                <option value="<?= $value ?>" <?= ($request[$datafield->id] === $value) ? 'selected' : '' ?>>
                    <?= htmlReady($pval) ?>
                </option>
            <? endforeach ?>
            </select>
        <? else : ?>
            <input type="text" name="<?= $datafield->id ?>" value="<?= htmlReady($request[$datafield->id]) ?>">
        <? endif ?>
        </label>
    <? endforeach; ?>

    </fieldset>

    <footer>
        <?= Button::create(_('Suchen'), 'search')?>
        <?= Button::create(_('Zurücksetzen'), 'reset')?>
    </footer>
</form>

<? if (count($users) > 0 && $users != 0): ?>
    <?= $this->render_partial('admin/user/_results', compact('users')) ?>
<? endif; ?>

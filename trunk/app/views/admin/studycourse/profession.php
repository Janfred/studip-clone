<?
# Lifter010: TODO
?>
<? if (isset($flash['error'])): ?>
    <?= MessageBox::error($flash['error'], $flash['error_detail']) ?>
<? elseif (isset($flash['message'])): ?>
    <?= MessageBox::info($flash['message']) ?>
<? elseif (isset($flash['success'])): ?>
    <?= MessageBox::success($flash['success'], $flash['success_detail']) ?>
<? elseif (isset($flash['delete'])): ?>
    <?= createQuestion2(sprintf(_('Wollen Sie das Fach "%s" wirklich löschen?'), $flash['delete']['name']), array(), array(), $controller->url_for('/delete_profession', $flash['delete']['fach_id'])) ?>
<? endif; ?>
<table class="default collapsable">
    <thead>
        <tr>
        <th><a href="<?= $controller->url_for('/profession', array('sortby' => 'name')) ?>"><b> <?=_('Name des Studienganges')?></b> <?= (Request::get('sortby', 'name') == 'name') ? Assets::img('dreieck_down.png'): ''?></a></th>
        <th><a href="<?= $controller->url_for('/profession', array('sortby' => 'users')) ?>"><b> <?= _('Nutzer') ?></b> <?= (Request::get('sortby') == 'users') ? Assets::img('dreieck_down.png'): ''?></a></th>
        <th colspan="3"><b> <?=_("Aktion")?></b></th>
        </tr>
    </thead>
    <? foreach ($studycourses as $index_s => $studycourse): ?>
    <tbody class="<?= count($studycourse->degrees) ? 'collapsed' : 'empty' ?>">
    <tr class="table_header header-row" valign="bottom">
        <th class="toggle-indicator"><? if (count($studycourse->degrees) < 1): ?><?= $index_s + 1 ?>. <?= htmlReady($studycourse->name) ?> <? else: ?> <a class="toggler" href="#"><?= $index_s + 1 ?>. <?= htmlReady($studycourse->name) ?></a><? endif; ?></th>
        <? $count_user = $studycourse->count_user; ?>
        <th class="dont-hide"><?= $count_user ?> </th>
        <th style="width: 20px;" class="dont-hide">
            <? if ($count_user > 0): ?><a href="<?=URLHelper::getLink('dispatch.php/messages/write', array('sp_id' => $studycourse->id, 'emailrequest' => '1', 'default_subject' => _('Informationen zum Studiengang:') . " " . $studycourse->name)) ?>">
                <?= Icon::create('mail', 'clickable', ['title' => _('Nachricht an alle Benutzer schicken'), 'class' => 'text-top'])->asImg() ?>
            </a><? endif;?>
        </th>
        <th style="width: 20px;" class="dont-hide">
            <a href="<?=$controller->url_for('/edit_profession', $studycourse->id)?>">
                <?= Icon::create('edit', 'clickable', ['title' => _('Studiengang bearbeiten')])->asImg() ?>
            </a>
        </th>
        <th style="width: 20px;" class="dont-hide">
            <? if ($count_user == 0): ?> <a href="<?=$controller->url_for('/delete_profession', $studycourse->id) ?>">
                <?= Icon::create('trash', 'clickable', ['title' => _('Studiengang löschen')])->asImg() ?>
            </a><? endif;?>
        </th>
    </tr>
    <? foreach ($studycourse->degrees as $index_d => $degree): ?>
    <tr>
        <td class="label-cell">
           <?= $index_s + 1 ?>.<?= $index_d + 1 ?>
           <?= htmlReady($degree['name']) ?>
        </td>
        <td><?= $degree->countUserByStudycourse($studycourse->id) ?></td>
        <td><a href="<?= URLHelper::getLink('sms_send.php', array('sms_source_page' => 'sms_box.php', 'prof_id' => $studycourse->fach_id, 'deg_id' => $degree->abschluss_id, 'emailrequest' => '1', 'subject' => _('Informationen zum Studiengang:') . " " . $studycourse->name . " (" . $degree->name . ")"))?>"><?= Icon::create('mail', 'clickable', ['title' => _('Nachricht an alle Nutzer schicken')])->asImg() ?></a></td>
        <td></td>
        <td></td>
    </tr>
    <? endforeach; ?>
    </tbody>
    <? endforeach ?>
</table>

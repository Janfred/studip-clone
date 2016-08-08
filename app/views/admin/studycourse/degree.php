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
    <?= createQuestion2(sprintf(_('Wollen Sie den Abschluss "%s" wirklich löschen?'), $flash['delete']['name']), array(), array(), $controller->url_for('/delete_degree', $flash['delete']['abschluss_id'])); ?>
<? endif; ?>
<table class="default collapsable">
    <tr>
        <th><a href="<?= $controller->url_for('/degree', array('sortby' => 'name')) ?>"><b> <?= _('Name der Abschlüsse') ?></b> <?= (Request::get('sortby', 'name') == 'name') ? Assets::img('dreieck_down.png'): ''?></a></th>
        <th><a href="<?= $controller->url_for('/degree', array('sortby' => 'users')) ?>"><b> <?= _('Nutzer') ?></b> <?= (Request::get('sortby') == 'users') ? Assets::img('dreieck_down.png'): ''?></a></th>
        <th colspan="3"><b> <?= _('Aktion') ?></b></th>
    </tr>
    <? foreach ($studydegrees as $index_a => $studydegree) : ?>
    <tbody class="<?= count($studydegree->professions) ? '' : 'empty' ?> collapsed ">
    <tr class="table_header header-row">
        <td class="toggle-indicator"><? if (count($studydegree->professions) < 1): ?><?= $index_a + 1 ?>. <?= htmlReady($studydegree->name) ?> <? else: ?> <a class="toggler" href="#"><?= $index_a + 1 ?>. <?= htmlReady($studydegree->name) ?> </a><? endif; ?></td>
        <td> <?= $studydegree->count_user ?> </td>
        <td style="width: 20px;" class="dont-hide">
            <? if ($studydegree->count_user > 0): ?><a href="<?=URLHelper::getLink('dispatch.php/messages/write', array('sd_id' => $studydegree->id, 'emailrequest' => '1', 'default_subject' => _('Informationen zum Studienabschluss:') . ' ' . $studydegree->name)) ?>">
                <?= Icon::create('mail', 'clickable', ['title' => _('Nachricht an alle Nutzer schicken')])->asImg() ?>
            </a><? endif;?>
        </td>
        <td style="width: 20px;" class="dont-hide">
            <a href="<?=$controller->url_for('/edit_degree', $studydegree->id)?>">
                <?= Icon::create('edit', 'clickable', ['title' => _('Abschluss bearbeiten')])->asImg() ?>
            </a>
        </td>
        <td style="width: 20px;" class="dont-hide">
            <? if ($studydegree->count_user == 0): ?><a href="<?=$controller->url_for('/delete_degree', $studydegree->id) ?>">
                <?= Icon::create('trash', 'clickable', ['title' => _('Abschluss löschen')])->asImg() ?>
            </a><? endif; ?>
        </td>
    </tr>
    <?php foreach ($studydegree->professions as $index_s => $studycourse): ?>
    <tr>
        <td class="label-cell">
           <?= $index_a + 1 ?>.<?= $index_s + 1 ?>
           <?= htmlReady($studycourse->name) ?>
        </td>
        <td><?= $studycourse->countUserByDegree($studydegree->id) ?></td>
        <td><a href="<?= URLHelper::getLink('sms_send.php', ['sms_source_page' => 'sms_box.php', 'prof_id' => $studycourse->id, 'deg_id' => $studydegree->id, 'emailrequest' => '1', 'subject' => _('Informationen zum Studiengang:') . ' ' . htmlReady($studycourse->name) . ' (' . htmlReady($studydegree->name) . ')']) ?>"><?= Icon::create('mail', 'clickable', ['title' => _('Eine Nachricht an alle Nutzer schicken')])->asImg() ?></a> </td>
        <td></td>
        <td></td>
    </tr>
    <? endforeach; ?>
    </tbody>
    <? endforeach; ?>
 </table>

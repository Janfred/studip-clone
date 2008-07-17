<div class="white" style="padding: 1ex;">

  <? if (isset($error_msg)): ?>
    <table style="width: 100%;">
      <? my_error($error_msg, '', 1, false, true) ?>
    </table>
  <? endif ?>

  <h3><?= _('Liste der Nutzerdomänen') ?></h3>

  <form action="<?= $controller->url_for('domain_admin/new') ?>" method="POST">

    <table style="border-collapse: collapse; margin-bottom: 1em; width: 80%;">
      <tr>
        <th style="text-align: left; width: 50%;">
          <?= _('Name') ?>
        </th>
        <th style="text-align: left; width: 40%;">
          <?= _('ID') ?>
        </th>
        <th style="text-align: left; width: 10%;">
          <?= _('Aktionen') ?>
        </th>
      </tr>
  
      <? foreach ($domains as $domain): ?>
        <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
          <td>
            <?= htmlReady($domain->getName()) ?>
          </td>
          <td>
            <?= $domain->getID() ?>
          </td>
          <td>
            <a href="<?= $controller->url_for('domain_admin/edit/'.$domain->getID()) ?>">
              <?= Assets::img('edit_transparent.gif') ?>
            </a>
            <a href="<?= $controller->url_for('domain_admin/delete/'.$domain->getID()) ?>">
              <?= Assets::img('trash.gif') ?>
            </a>
          </td>
        </tr>
      <? endforeach ?>
    </table>

    <?= makebutton('anlegen', 'input') ?>
  </form>

</div>

<div class="white" style="padding: 1ex;">

  <h3><?= _('Liste der Nutzerdomänen') ?></h3>

  <form action="<?= $controller->url_for('domain_admin/save') ?>" method="POST">

    <table style="border-collapse: collapse; margin-bottom: 1em; width: 99%;">
      <tr>
        <th style="text-align: left; width: 40%;">
          <?= _('Name') ?>
        </th>
        <th style="text-align: left; width: 35%;">
          <?= _('ID') ?>
        </th>
        <th style="text-align: left; width: 15%;">
          <?= _('NutzerInnen') ?>
        </th>
        <th style="text-align: left; width: 10%;">
          <?= _('Aktionen') ?>
        </th>
      </tr>
  
      <? foreach ($domains as $domain): ?>
        <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
          <td>
            <? if (isset($edit_id) && $edit_id === $domain->getID()): ?>
              <input type="hidden" name="id" value="<?= $edit_id ?>">
              <input type="text" style="width: 80%;" name="name" value="<?= htmlReady($domain->getName()) ?>">
            <? else: ?>
              <?= htmlReady($domain->getName()) ?>
            <? endif ?>
          </td>
          <td>
            <?= $domain->getID() ?>
          </td>
          <td>
            <?= count($domain->getUsers()) ?>
          </td>
          <td>
            <a href="<?= $controller->url_for('domain_admin/edit/'.$domain->getID()) ?>">
              <?= Assets::img('edit_transparent.gif', array('alt' => _('bearbeiten'))) ?>
            </a>
            <? if (count($domain->getUsers())==0): ?>
            <a href="<?= $controller->url_for('domain_admin/delete/'.$domain->getID()) ?>">
              <?= Assets::img('trash.gif', array('alt' => _('löschen'))) ?>
            </a>
            <? endif; ?>
          </td>
        </tr>
      <? endforeach ?>

      <? if (!isset($edit_id)): ?>
        <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
          <td>
            <input type="hidden" name="new_domain" value="1">
            <input type="text" style="width: 80%;" name="name" value="">
          </td>
          <td>
            <input type="text" style="width: 80%;" name="id" value="">
          </td>
          <td></td>
        </tr>
      <? endif ?>
    </table>

    <?= makebutton('uebernehmen', 'input') ?>
  </form>

</div>

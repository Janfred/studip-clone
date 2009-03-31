<div class="white" style="padding: 1ex;">

  <? if (isset($error_msg)): ?>
    <table style="width: 100%;">
      <? my_error($error_msg, '', 1, false, true) ?>
    </table>
  <? endif ?>

  <h3><?= _('Liste der Nutzerdomänen') ?></h3>

  <form action="<?= $controller->url_for('domain_admin/new') ?>" method="POST">

    <table style="border-collapse: collapse; margin-bottom: 1em; width: 100%;">
      <?= $this->render_partial('domain_admin/domains') ?>
    </table>

    <?= makebutton('anlegen', 'input') ?>
  </form>

</div>

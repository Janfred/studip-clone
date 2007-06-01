<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0" height="25">
  <tr>
    <?= MakeToolbar($assets.'images/logo2.gif', '', '', '', 40, ''); ?>
  </tr>
</table>
<div align="center">
  <table style="width: 80%;">
    <tr>
      <td class="topic">
        <img style="float: left; margin-right: 1ex;" src="<?= $assets ?>images/icon-posting.gif">
        <b>
          <?= _('Stud.IP Web-Migrator') ?>
        </b>
      </td>
    </tr>
    <tr>
      <td class="blank" style="padding: 1ex;">
        <p>
          Aktueller Versionsstand: <?= $current ?>
        </p>
        <? if (empty($migrations)): ?>
          <p>
            <?= _('Ihr System befindet sich auf dem aktuellen Stand.') ?>
          </p>
        <? else: ?>
          <p>
            <?= _('Die hier aufgeführten Anpassungen werden beim Klick auf "starten" ausgeführt:') ?>
          </p>
          <table class="steel1" width="100%">
            <tr>
              <th>
                <?= _('Nr.') ?>
              </th>
              <th>
                <?= _('Name') ?>
              </th>
              <th>
                <?= _('Beschreibung') ?>
              </th>
            </tr>
            <? foreach ($migrations as $migration): ?>
              <tr>
                <td style="text-align: center;">
                  <?= $migration['number'] ?>
                </td>
                <td>
                  <?= $migration['name'] ?>
                </td>
                <td>
                  <? if (empty($migration['description'])): ?>
                    <i>
                      <?= _('keine Beschreibung vorhanden') ?>
                    </i>
                  <? else: ?>
                    <?= htmlspecialchars($migration['description']) ?>
                  <? endif ?>
                </td>
              </tr>
            <? endforeach ?>
          </table>
          <p></p>
          <form method="POST">
            <div align="center">
              <?= makeButton('starten', 'input', false, 'start') ?>
            </div>
          </form>
        <? endif ?>
      </td>
    </tr>
  </table>
</div>

<div class="white" style="padding: 0.5em;">


  <h1>Studienbereiche</h1>

  <p class="effect_highlight">
    <?= Assets::img('x.gif', array('align' => 'middle')) ?>
    <?= _("Sie haben bisher keine Veranstaltung gewählt.") ?>
    <a href="<?= URLHelper::getLink('admin_seminare1.php', array('list' => 'TRUE')) ?>">
      <?= makeButton("auswaehlen") ?>
    </a>
  </p>

</div>

<?php
require_once 'lib/classes/Messagebox.class.php';
include 'lib/include/html_head.inc.php';

$current_page = _("Fehler");
$home = array(
  'text'  => _("Start"),
  'link'  => URLHelper::getLink('index.php'),
  'info'  => _("Zur Startseite"),
  'image' => "home",
  'accesskey' => false);
?>

<?= $this->render_partial('header', compact('current_page', 'home')) ?>

<div style="background-color: white; width: 70%; padding: 1em; margin: auto;">
	<?=Messagebox::error('Bitte wenden Sie sich an Ihren Systemadministrator.', array(htmlentities($exception->getMessage())), true)?>
    <p>
      <?= _("Zurück zur") ?> <a href="<?= URLHelper::getLink('index.php') ?>"><?= _("Startseite") ?></a>
    </p>
</div>

<? include 'lib/include/html_end.inc.php'; ?>

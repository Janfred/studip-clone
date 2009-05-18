<?php

require_once 'lib/visual.inc.php';

//warum wird hier nicht das layout benutzt? so fehlen doch die menü-punkte...

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
	<?=Messagebox::get('ERROR')->show(htmlentities($exception->getMessage()))?>
    <p>
      <?= _("Zurück zur") ?> <a href="<?= URLHelper::getLink('index.php') ?>"><?= _("Startseite") ?></a>
    </p>
</div>

<? include 'lib/include/html_end.inc.php'; ?>

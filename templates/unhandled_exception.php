<? require_once 'lib/visual.inc.php'; ?>
<? include 'lib/include/html_head.inc.php'; ?>

<?
$current_page = _("Fehler");
$home = array(
  'text'  => _("Start"),
  'link'  => 'index.php',
  'info'  => _("Zur Startseite"),
  'image' => "home",
  'accesskey' => false);
?>

<?= $this->render_partial('header', compact('current_page', 'home')) ?>

<div style="background-color: white; width: 70%; padding: 1em; margin: auto;">
    <h1>
      <?= Assets::img('x.gif') ?>
      <?= _("Fehler:") ?> <?= htmlentities($exception->getMessage()) ?>
    </h1>
    <p>
      <?= _("Zurück zur") ?> <a href="index.php"><?= _("Startseite") ?></a>
    </p>
</div>


<? include 'lib/include/html_end.inc.php'; ?>

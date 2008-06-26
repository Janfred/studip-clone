<? # Lifter005: TODO - studipim ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <? if (in_array(basename($_SERVER['SCRIPT_NAME']), array('dispatch.php', 'plugins.php'))) : ?>
      <base href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>">
    <? endif ?>
    <? if (basename($_SERVER['SCRIPT_NAME']) !== 'logout.php' &&
           $GLOBALS['AUTH_LIFETIME'] > 0) : ?>
      <meta http-equiv="REFRESH" CONTENT="<?= $GLOBALS['AUTH_LIFETIME'] * 60 ?>; URL=<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>logout.php">
    <? endif ?>
    <link rel="SHORTCUT ICON" href="<?= $GLOBALS['FAVICON'] ?>">
    <title><?= isset($GLOBALS['_html_head_title']) ? $GLOBALS['_html_head_title'] : 'Stud.IP' ?></title>

    <?
      if (!isset($GLOBALS['_include_stylesheet'])) {
        $GLOBALS['_include_stylesheet'] = 'style.css';
      }
    ?>
    <? if ($GLOBALS['_include_stylesheet'] != '') : ?>
      <?= Assets::stylesheet($GLOBALS['_include_stylesheet']) ?>
    <? endif ?>

    <? if (isset($GLOBALS['_include_extra_stylesheet'])) : ?>
      <?= Assets::stylesheet($GLOBALS['_include_extra_stylesheet']) ?>
    <? endif ?>

    <? if (isset($GLOBALS['_include_additional_header'])) : ?>
      <?= $GLOBALS['_include_additional_header'] ?>
    <? endif ?>

    <?= Assets::stylesheet('header') ?>

    <?= Assets::script('prototype', 'scriptaculous', 'application') ?>

    <? if ($GLOBALS['my_messaging_settings']['start_messenger_at_startup'] &&
           $GLOBALS['auth']->auth['jscript'] &&
           !$GLOBALS['messenger_started'] &&
           !$GLOBALS['seminar_open_redirected']) : ?>
      <script language="Javascript">
        fenster = window.open("studipim.php", "im_<?= $GLOBALS['user']->id ?>",
                              "scrollbars=yes,width=400,height=300",
                              "resizable=no");
      </script>
      <? $GLOBALS['messenger_started'] = TRUE; ?>
    <? endif ?>
  </head>

  <body>

    <div id="ajax_notification" style="display: none;">
      <?= Assets::img('ajax_indicator.gif', array('align' => 'absmiddle')) ?>&nbsp;Working...
    </div>

    <? include 'lib/include/header.php'; ?>

    <? if (isset($tabs)) : ?>
      <? include 'lib/include/' . $tabs . '.inc.php'; ?>
    <? endif ?>

    <div id="layout_container">
        <?= $content_for_layout ?>
    </div>
  </body>
</html>


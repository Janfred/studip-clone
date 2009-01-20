<? # Lifter005: TODO - studipim ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252">
    <? if (basename($_SERVER['SCRIPT_NAME']) !== 'logout.php' &&
           $GLOBALS['AUTH_LIFETIME'] > 0) : ?>
      <meta http-equiv="REFRESH" CONTENT="<?= $GLOBALS['AUTH_LIFETIME'] * 60 ?>; URL=<?= $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] ?>logout.php">
    <? endif ?>
    <link rel="SHORTCUT ICON" href="<?= $GLOBALS['FAVICON'] ?>">
    <title>
      <? if (isset($GLOBALS['_html_head_title'])): ?>
	<?= $GLOBALS['_html_head_title'] ?>
      <? elseif (isset($GLOBALS['CURRENT_PAGE'])): ?>
	<?= $GLOBALS['HTML_HEAD_TITLE'] ?> - <?= $GLOBALS['CURRENT_PAGE'] ?>
      <? else: ?>
	<?= $GLOBALS['HTML_HEAD_TITLE'] ?>
      <? endif ?>
    </title>

    <?
      if (!isset($GLOBALS['_include_stylesheet'])) {
        $GLOBALS['_include_stylesheet'] = 'style.css';
      }
    ?>
    <? if ($GLOBALS['_include_stylesheet'] != '') : ?>
      <?= Assets::stylesheet($GLOBALS['_include_stylesheet'], array('media' => 'screen, print')) ?>
    <? endif ?>

    <? if (isset($GLOBALS['_include_extra_stylesheet'])) : ?>
      <?= Assets::stylesheet($GLOBALS['_include_extra_stylesheet']) ?>
    <? endif ?>

    <? if (isset($GLOBALS['_include_additional_header'])) : ?>
      <?= $GLOBALS['_include_additional_header'] ?>
    <? endif ?>

    <?= Assets::stylesheet('header', array('media' => 'screen, print')) ?>

    <?= Assets::script('prototype', 'scriptaculous.js?load=effects,dragdrop,controls', 'application') ?>

    <script type="text/javascript" language="javascript">
    // <![CDATA[
      STUDIP.ABSOLUTE_URI_STUDIP = "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>";
    // ]]>
    </script>

    <? if ($GLOBALS['my_messaging_settings']['start_messenger_at_startup'] &&
           $GLOBALS['auth']->auth['jscript'] &&
           !$_SESSION['messenger_started'] &&
           !$GLOBALS['seminar_open_redirected']) : ?>
      <script language="Javascript">
        fenster = window.open("studipim.php", "im_<?= $GLOBALS['user']->id ?>",
                              "scrollbars=yes,width=400,height=300",
                              "resizable=no");
      </script>
      <? $_SESSION['messenger_started'] = TRUE; ?>
    <? endif ?>
  </head>

  <body>
    <div id="overdiv_container"></div>
    <div id="ajax_notification" style="display: none;">
      <?= Assets::img('ajax_indicator.gif', array('align' => 'absmiddle')) ?>&nbsp;Working...
    </div>

    <? include 'lib/include/header.php'; ?>

    <? if (isset($tabs)) : ?>
      <? include 'lib/include/' . $tabs . '.inc.php'; ?>
    <? endif ?>

    <div id="layout_container">
      <div id="layout_infobox">
        <? $infobox = isset($infobox)
                      ? $infobox
                      : array('picture' => 'warning.jpg',
                              'content' => array(
                                             array('kategorie' => _("Infobox fehlt."))
                                           )) ?>
        <?= $this->render_partial('infobox/infobox_generic_content', $infobox) ?>
      </div>
      <div id="layout_content">
        <?= $content_for_layout ?>
      </div>
      <div id="layout_clear"></div>
    </div>
  </body>
</html>


<?php
# Lifter010: TODO
NotificationCenter::postNotification('PageWillRender', $body_id ? : PageLayout::getBodyElementId());
$navigation = PageLayout::getTabNavigation();
$tab_root_path = PageLayout::getTabNavigationPath();
if ($navigation) {
    $subnavigation = $navigation->activeSubNavigation();
    if ($subnavigation !== null) {
        $nav_links = new NavigationWidget();
        $nav_links->id = 'sidebar-navigation';
        foreach ($subnavigation as $path => $nav) {
            if (!$nav->isVisible()) {
                continue;
            }
            $nav_id = "nav_".implode("_", preg_split("/\//", $tab_root_path, -1, PREG_SPLIT_NO_EMPTY))."_".$path;
            $link = $nav_links->addLink(
                $nav->getTitle(),
                URLHelper::getLink($nav->getURL()),
                null,
                array('id' => $nav_id)
            );
            $link->setActive($nav->isActive());
            if (!$nav->isEnabled()) {
                $link['disabled'] = true;
                $link->addClass('quiet');
            }
        }
        if ($nav_links->hasElements()) {
            Sidebar::get()->insertWidget($nav_links, ':first');
        }
    }
}
?>
<!DOCTYPE html>
<html class="no-js">
<head>
    <meta charset="WINDOWS-1252">
    <title data-original="<?= htmlReady(PageLayout::getTitle()) ?>">
      <?= htmlReady(PageLayout::getTitle() . ' - ' . Config::get()->UNI_NAME_CLEAN) ?>
    </title>
    <?php
        // needs to be included in lib/include/html_head.inc.php as well
        include 'app/views/WysiwygHtmlHeadBeforeJS.php';
    ?>
    <?= PageLayout::getHeadElements() ?>

    <script src="<?= URLHelper::getScriptLink('dispatch.php/localizations/' . $_SESSION['_language']) ?>"></script>

    <script>
        STUDIP.ABSOLUTE_URI_STUDIP = "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>";
        STUDIP.ASSETS_URL = "<?= $GLOBALS['ASSETS_URL'] ?>";
        STUDIP.STUDIP_SHORT_NAME = "<?= Config::get()->STUDIP_SHORT_NAME ?>";
        String.locale = "<?= htmlReady(strtr($_SESSION['_language'], '_', '-')) ?>";
        <? if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('autor') && PersonalNotifications::isActivated()) : ?>
        STUDIP.jsupdate_enable = true;
        <? endif ?>
        STUDIP.URLHelper.parameters = <?= json_encode(studip_utf8encode(URLHelper::getLinkParams())) ?>;
    </script>
    <?php
        // needs to be included in lib/include/html_head.inc.php as well
        include 'app/views/WysiwygHtmlHead.php';
    ?>
</head>

<body id="<?= $body_id ? $body_id : PageLayout::getBodyElementId() ?>">
<div id="layout_wrapper">
    <? SkipLinks::insertContainer() ?>
    <? SkipLinks::addIndex(_("Hauptinhalt"), 'layout_content', 100, true) ?>
    <?= PageLayout::getBodyElements() ?>

    <? include 'lib/include/header.php' ?>

    <div id="layout_page">
        <? if (PageLayout::isHeaderEnabled() && is_object($GLOBALS['user']) && $GLOBALS['user']->id != 'nobody' && Navigation::hasItem('/course') && Navigation::getItem('/course')->isActive() && $_SESSION['seminar_change_view_'.$GLOBALS['SessionSeminar']]) : ?>
            <?= $this->render_partial('change_view', array('changed_status' => $_SESSION['seminar_change_view_'.$GLOBALS['SessionSeminar']])) ?>
        <? endif ?>

        <? if (PageLayout::isHeaderEnabled() /*&& isset($navigation)*/) : ?>
            <?= $this->render_partial('tabs', compact('navigation')) ?>
        <? endif; ?>

        <?
        if (is_object($GLOBALS['user']) && $GLOBALS['user']->id != 'nobody') {
            // only mark course if user is logged in and free access enabled
            if (Config::get()->ENABLE_FREE_ACCESS
                && Navigation::hasItem('/course')
                && Navigation::getItem('/course')->isActive())
            {
                // indicate to the template that this course is publicly visible
                // need to handle institutes separately (always visible)
                if ($GLOBALS['SessSemName']['class'] == 'inst') {
                    $header_template->public_hint = _('Öffentliche Einrichtung');
                } else if (Course::findCurrent()->lesezugriff == 0) {
                    $header_template->public_hint = _('Öffentliche Veranstaltung');
                }
            }
        }
        ?>
        <div id="page_title_container">
            <div id="current_page_title">
                <?= htmlReady(PageLayout::getTitle()) ?>
                <?= $public_hint ? '(' . htmlReady($public_hint) . ')' : '' ?>
            </div>
            <? if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('autor')) : ?>
            	<?= Helpbar::get()->render() ?>
            <? endif; ?>
         </div>

        <div id="layout_container">
            <?= Sidebar::get()->render() ?>
            <div id="layout_content">
                <?= implode(PageLayout::getMessages()) ?>
                <?= $content_for_layout ?>
            </div>
        </div>
    </div> <? // Closes #layout_page opened in included templates/header.php ?>

    <?= $this->render_partial('footer'); ?>
    <!-- Ende Page -->
    <? /* <div id="layout_push"></div> */ ?>
</div>


    <?= SkipLinks::getHTML() ?>
</body>
</html>
<?php NotificationCenter::postNotification('PageDidRender', $body_id ? : PageLayout::getBodyElementId());

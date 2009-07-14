<?
$GLOBALS['CURRENT_PAGE'] = getHeaderLine($course_id) . ' - ' .
                           _('Bild ändern');
$tabs = "links_admin";
$this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox'))
?>
<h1><?= _("Veranstaltungsbild hochladen") ?></h1>

<?= MessageBox::success(_("Die Bilddatei wurde erfolgreich hochgeladen.")) ?>

<p class="quiet">
    <?= _("Eventuell sehen Sie das neue Bild erst, nachdem Sie diese Seite neu geladen haben (in den meisten Browsern F5 drücken).") ?>
</p>

<p>
    <a href="<?= URLHelper::getLink('admin_seminare1.php') ?>">
      <?= Assets::img('forumgruen.gif', array('style' => 'vertical-align: baseline;')) ?>
      <?= _("zurück zur Veranstaltungsadministration") ?>
    </a>
</p>

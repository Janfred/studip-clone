<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<?= (isset($flash['error']))?MessageBox::error($flash['error'], $flash['error_detail']):'' ?>
<form action="<?= $controller->url_for('/newdegree') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <tr class="table_row_odd">
            <td><?= _("Name des Studienabschlusses:") ?> </td>
            <td><input type="text" name="degreename" size="60" maxlength="254" value="<?= htmlReady($degree_name) ?>"></td>
        </tr>
        <tr class="table_row_even">
            <td><?= _("Beschreibung:") ?> </td>
            <td><textarea cols="57" rows="5" name="description" value="<?= htmlReady($degree_desc) ?>"></textarea></td>
        </tr>
        <tr class="table_footer">
            <td></td>
            <td>
                <?= Button::create(_('Anlegen'),'anlegen', array('title' => _('Abschluss anlegen'))) ?>
                <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('/degree')) ?>
            </td>
        </tr>
    </table>
</form>
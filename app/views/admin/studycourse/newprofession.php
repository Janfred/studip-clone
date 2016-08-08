<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<?= (isset($flash['error'])) ? MessageBox::error($flash['error'], $flash['error_detail']) : '' ?>
<form action="<?= $controller->url_for('/newprofession') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <tr class="table_row_odd">
            <td><?= _('Name des Faches:') ?> </td>
            <td><input type="text" name="professionname" size="60" maxlength="254" value="<?= htmlReady($prof_name) ?>"></td>
        </tr>
        <tr class="table_row_even">
            <td><?= _('Beschreibung:') ?> </td>
            <td><textarea cols="57" rows="5" name="description"><?= htmlReady($prof_desc) ?></textarea></td>
        </tr>
        <tr class="table_footer">
            <td> </td>
            <td>
                <?= Button::create(_('Anlegen'),'anlegen', array('title' => _('Neues Fach anlegen'))) ?>
                <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('/profession')) ?>
            </td>
        </tr>
    </table>
</form>
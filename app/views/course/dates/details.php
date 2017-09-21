<table class="default nohover">
    <colgroup>
        <col width="25%">
        <col width="75%">
    </colgroup>
    <tbody>
    <? if (count($date->topics) > 0): ?>
        <tr>
            <td><strong><?= _('Thema') ?></strong></td>
            <td>
                <ul class="themen_list">
                <? foreach ($date->topics as $topic) : ?>
                    <?= $this->render_partial('course/dates/_topic_li', compact('topic')) ?>
                <? endforeach ?>
                </ul>
            </td>
        </tr>
    <? endif; ?>
        <tr>
            <td><strong><?= _("Art des Termins") ?></strong></td>
            <td>
                <?= htmlReady($GLOBALS['TERMIN_TYP'][$date['date_typ']]['name']) ?>
            </td>
        </tr>
    <? if (count($date->dozenten) > 0): ?>
        <tr>
            <td><strong><?= _('Durchführende Dozenten') ?></strong></td>
            <td>
                <ul class="dozenten_list clean">
                <? foreach ($date->dozenten as $teacher): ?>
                    <li>
                        <a href="<?= $controller->link_for('profile?username=' . $teacher->username) ?>">
                            <?= Avatar::getAvatar($teacher->user_id)->getImageTag(Avatar::SMALL) ?>
                            <?= htmlReady($teacher->getFullname()) ?>
                        </a>
                    </li>
                <? endforeach ?>
                </ul>
            </td>
        </tr>
    <? endif; ?>
    <? if (count($date->statusgruppen) > 0): ?>
        <tr>
            <td><strong><?= _('Beteiligte Gruppen') ?></strong></td>
            <td>
                <ul>
                <? foreach ($date->statusgruppen as $group): ?>
                    <li><?= htmlReady($group->name) ?></li>
                <? endforeach ;?>
                </ul>
            </td>
        </tr>
    <? endif; ?>
    </tbody>
</table>

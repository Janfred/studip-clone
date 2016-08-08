<td colspan="2">
    <table class="default collapsable">
        <colgroup>
            <col>
            <col style="width: 20%;">
            <col span="3" style="width: 5%;">
            <col style="width: 5%;">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Modul') ?></th>
                <th><?= _('Modulcode') ?></th>
                <th><?= _('Fassung') ?></th>
                <th><?= _('Modulteile') ?></th>
                <th style="text-align: center;">
                    <?= _('Ausgabesprachen') ?>
                </th>
                <th> </th>
            </tr>
        </thead>
        <?= $this->render_partial('module/module/module') ?>
    </table>
</td>
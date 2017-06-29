    <colgroup>
        <col width="30px">
        <col width="20px">
        <col>
        <col width="100px" class="responsive-hidden">
        <col width="150px" class="responsive-hidden">
        <col width="120px" class="responsive-hidden">
        <col width="80px">
    </colgroup>
    <thead>
        <tr class="sortable">
            <th data-sort="false" style="padding-left: 1px;">
                <input type="checkbox"
                       data-proxyfor=":checkbox[name='ids[]']"
                       class="document-checkbox"
                       id="all_files_checkbox">
                <label for="all_files_checkbox"><span></span></label>
            </th>
            <th data-sort="htmldata"><?= _('Typ') ?></th>
            <th data-sort="text"><?= _('Name') ?></th>
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Größe') ?></th>
            <th data-sort="text" class="responsive-hidden"><?= _('Autor/-in') ?></th>
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Datum') ?></th>
            <th data-sort="false"><?= _('Aktionen') ?></th>
        </tr>
    </thead>

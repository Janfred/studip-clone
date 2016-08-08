<? foreach (ModuleManagementModelTreeItem::getPathes($area->getTrails(array('Modulteil', 'StgteilabschnittModul',  'StgteilAbschnitt', 'StgteilVersion', 'Studiengang'))) as $path) : ?>
    <li style="background-color:inherit;padding-left:20px;color:#666666">
        <?= Request::isAjax() ? studip_utf8encode(htmlReady($path)) : htmlReady($path) ?>
    </li>
<? endforeach; ?>
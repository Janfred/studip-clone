<? foreach (ModuleManagementModelTreeItem::getPathes($area->getTrails(array('Modulteil', 'StgteilabschnittModul',  'StgteilAbschnitt', 'StgteilVersion', 'Studiengang'))) as $path) : ?>
    <li style="background-color:inherit;padding-left:20px;color:#666666">
        <?= htmlReady($path) ?>
    </li>
<? endforeach; ?>

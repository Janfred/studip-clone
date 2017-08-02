<?
if (!$pathes) {
    /**
     * TODO simplify filter for complete pathes
     */
    $complete_trails = array();
    $trails = $area->getTrails(array('Modulteil', 'StgteilabschnittModul',  'StgteilAbschnitt', 'StgteilVersion', 'Studiengang'));
    foreach ($trails as $trail) {
        if (count($trail) == 5) {
            $complete_trails[] = $trail;
        }
    }
    $pathes = ModuleManagementModelTreeItem::getPathes($complete_trails);
}
?>
<? if (count($pathes)) : ?>
<? foreach ($pathes as $path) : ?>
    <li style="background-color:inherit;padding-left:20px;color:#666666">
        <?= htmlReady($path) ?>
    </li>
<? endforeach; ?>
<? else : ?>
<li style="background-color:inherit;padding-left:20px;color:#666666">
    <?= _('Keine Module in den Semestern der Veranstaltung verfügbar.'); ?>
</li>
<? endif; ?>

<div style="padding-bottom: 20px;">
<? $breadCrumbPoints = $this->breadCrumb->getTrail(); ?>
<? $sumPoints = count($breadCrumbPoints)-1;?>
<? $index = 0; ?>
<? foreach($breadCrumbPoints as $id => $point):?>
    <? if (is_array($point['addition'])) : ?>
        <? $mvv_object = $point['type']::find($id); ?>
        <? if ($point['type'] == 'Fach') : ?> 
            <? $additional_object = Abschluss::find($point['addition']['Abschluss']); ?>
            <a href="<?= $point['uri']?>"><?= htmlReady($mvv_object->getDisplayName() . ' (' . $additional_object->getDisplayName() . ')') ?></a> <?= $index++ < $sumPoints?'>':null ?>
        <? endif; ?>
        <? if ($point['type'] == 'StgteilBezeichnung') : ?>
            <? $additional_object = StudiengangTeil::find($point['addition']['StudiengangTeil']); ?>
            <a href="<?= $point['uri']?>"><?= htmlReady($mvv_object->getDisplayName() . ': ' . $additional_object->getDisplayName(ModuleManagementModel::DISPLAY_FACH)) ?></a> <?= $index++ < $sumPoints?'>':null ?>
        <? endif; ?>
    <? else : ?>
        <? if ($point['type']) : ?>
            <? $mvv_object = $point['type']::find($id); ?>
            <? if ($point['type'] == 'StudiengangTeil') : ?>
                <a href="<?= $point['uri']?>"><?= htmlReady($mvv_object->getDisplayName(ModuleManagementModel::DISPLAY_FACH)) ?></a> <?= $index++ < $sumPoints?'>':null ?>
            <? else : ?>
                <a href="<?= $point['uri']?>"><?= htmlReady($mvv_object->getDisplayName()) ?></a> <?= $index++ < $sumPoints?'>':null ?>
            <? endif; ?>
        <? else : ?>
            <a href="<?= $point['uri']?>"><?= htmlReady($point['name']) ?></a> <?= $index++ < $sumPoints?'>':null ?>
        <? endif; ?>
    <? endif; ?>
<? endforeach; ?>
</div>
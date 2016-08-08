<div style="padding-bottom: 20px;">
    <? $breadCrumbPoints = $this->breadCrumb->getTrail(); ?>
    <? $sumPoints = count($breadCrumbPoints)-1;?>
    <? foreach($breadCrumbPoints as $index => $point):?>
    <a href="<?= $point['uri']?>"><?= $point['name'] ?></a> <?= $index < $sumPoints?'>':null ?> 
    <? endforeach; ?>
</div>
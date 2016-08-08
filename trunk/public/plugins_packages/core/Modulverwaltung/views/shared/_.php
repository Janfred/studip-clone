<?= $controller->jsUrl() ?>
<div id="mvv-chooser">
    <h2><?= _('Auswahl:') ?></h2>
    <div id="index" style="width: <?= round(100 / (sizeof($list_functions) + 1)) ?>%;">
        <h3><?= $first_list_name ?></h3>
        <ul>
            <? foreach ($first_elements as $key => $element) : ?>
                <li title="<?= htmlReady($element['name']) ?>"><span class="mvv-chooser-id"><?= $key ?></span><?= htmlReady($element['name']) ?></li>
            <? endforeach; ?>
        </ul>
    </div>
    <? foreach ($list_functions as $function) : ?>
    <div id="<?= $function ?>" style="width: <?= round(100 / (sizeof($list_functions) + 1)) ?>%;"></div>
    <? endforeach; ?>
</div>
<div class="clear"></div>
<div id="mvv-chooser-path">
    <ul></ul>
</div>
<div id="mvv-load-content"></div>
<? if (!$search_result || in_array($node->id, $search_result)) : ?>
<li class="lvgroup-tree-<?= htmlReady($node->id) ?> keep-node" data-id="<?= $node->id ?>" data-type="<?= get_class($node) ?>">
    <? if ($node->isAssignable()) : ?>
        <?= Icon::create('arr_2left', 'sort')->asInput(["name" => 'assign['.$node->id.']', "onclick" => "return MVV.CourseWizard.assignNode('".$node->id."')", "style" => in_array($node->id, $selection->getLvGruppenIDs()) ? 'display: none;' : '']) ?>
    <? endif; ?>
    <? if ($node->hasChildren()) : ?>
    <input type="checkbox" id="<?= htmlReady($node->id) ?>"<?= (in_array($node->id, $open_lvg_nodes)) ? ' checked="checked"' : '' ?>/>
    <label onclick="return MVV.CourseWizard.getTreeChildren('<?= htmlReady($node->id) ?>', true, '<?= htmlReady(get_class($node)) ?>')"
           for="<?= htmlReady($node->id) ?>" class="undecorated">
        <a href="<?= URLHelper::getLink($no_js_url,
            array('open_node' => $node->id, 'open_nodes' => json_encode($open_lvg_nodes))) ?>">
    <? endif; ?>
        <?= htmlReady($node->getDisplayname()) ?>

    <? if ($node->hasChildren()) : ?>
        </a>
    </label>
    <ul>
        <? if ($node->hasChildren() && in_array($node->id, $open_lvg_nodes)) : ?>

            <? foreach ($node->getChildren() as $child) : ?>
                <?= $this->render_partial('coursewizard/lvgroups/_node',
                    array('node' => $child, 'stepnumber' => $stepnumber,
                        'temp_id' => $temp_id, 'values' => $values,
                        'open_nodes' => $open_nodes ?: array(),
                        'search_result' => $search_result ?: array())) ?>
            <? endforeach ?>
        <? endif; ?>
    </ul>
    <? endif;  ?>
</li>
<? endif; ?>

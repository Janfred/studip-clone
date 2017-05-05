<? if ($tree_item_ids): ?>
    <? foreach ($tree_item_ids as $tree_item_id): ?>
        <table id="institute_result">
            <tbody>
                <tr>
                    <td>
                        <!-- breadcrumb -->
                        <? $parent_ids = $tree->getParents($tree_item_id); ?>
                        <? $parent_ids = array_reverse($parent_ids); ?>
                        <? $parent_ids[] = $tree_item_id; ?>

                        <? foreach ($parent_ids as $item_id): ?>
                            <? if ($item_id != 'root'): ?>
                                &gt;
                                <a href=" <?= URLHelper::getURL("institut_browse.php?open_item={$item_id}") ?>">  <?= htmlReady($tree->tree_data[$item_id]['name']) ?> </a>
                            <? else: ?>
                                <?= htmlReady($tree->tree_data[$item_id]['name']) ?>
                            <? endif ?>
                        <? endforeach ?>

                        <!-- representation of the found item -->
                        <?= $tree_view->getItemContent($tree_item_id) ?>
                    </td>
                </tr>
            </tbody>
        </table>
    <? endforeach ?>
<? endif ?>

<? if (!$search_text): ?>
    <div id="institute_tree">
        <? $tree_view->showTree(); ?>
    </div>
<? endif ?>

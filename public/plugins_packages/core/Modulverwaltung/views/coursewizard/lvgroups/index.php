<h1><?= _('Lehrveranstaltungsgruppen') ?></h1>
<div id="assigned" data-ajax-url="<?= $ajax_url ?>" data-forward-url="<?= $no_js_url ?>">
    <h2>
        <span class="required">
            <?= _('Bereits zugewiesen') ?>
        </span>
    </h2>
    <ul class="css-tree">
        <li class="lvgroup-tree-assigned-root keep-node" data-id="root">
            <ul id="lvgroup-tree-assigned-selected">
            
              <? foreach ($selection->getAreas() as $area) : ?>
              	<? if (Request::isXhr()): ?>
                	<?= studip_utf8decode($this->render_partial('coursewizard/lvgroups/lvgroup_entry', compact('area'))) ?>
                <? else: ?>
                	<?= $this->render_partial('coursewizard/lvgroups/lvgroup_entry', compact('area')) ?>
                <?endif; ?>
              <? endforeach ?>
            
            </ul>
        </li>
    </ul>
</div>
<? if (!$values['locked']) : ?>

	<div id="lvgroup-tree-open-nodes">
	<? foreach ($open_lvg_nodes as $opennode): ?>
		<input type="hidden" name="open_lvg_nodes[]" value="<?= $opennode; ?>">
	<? endforeach; ?>
	</div>

    <div id="studyareas" data-ajax-url="<?= $ajax_url ?>"
        data-forward-url="<?= $no_js_url ?>" data-no-search-result="<?=_('Es wurde kein Suchergebnis gefunden.') ?>">
        <h2><?= _('Lehrveranstaltungsgruppen Suche') ?></h2>
        <div>
            <input type="text" size="40" name="search" id="lvgroup-tree-search"
                   value="<?= $values['searchterm'] ?>"/>
            <span id="lvgroup-tree-search-start">
                <?= Icon::create('search', 'clickable')->asInput(["name" => 'start_search', "onclick" => "return MVV.CourseWizard.searchTree()", "class" => $search_result?'hidden-no-js':'']) ?>
            </span>
            <span id="lvgroup-tree-search-reset" class="hidden-js">
                <?= Icon::create('refresh', 'clickable')->asInput(["name" => 'reset_search', "onclick" => "return MVV.CourseWizard.resetSearch()", "class" => $search_result?'':' hidden-no-js']) ?>
            </span>
        </div>
        
        <div id="lvgsearchresults" style="display: none;">
        	<h2><?= _('Suchergebnisse') ?></h2>
        	<ul class="collapsable css-tree">
        
        	</ul>
        </div>
        <h2><?= _('Alle Lehrveranstaltungsgruppen') ?></h2>
        <ul class="collapsable css-tree">
            <li class="lvgroup-tree-root tree-loaded keep-node">
                <input type="checkbox" id="root" checked="checked"/>
                <label for="root">
                    <?= $GLOBALS['UNI_NAME'] ?>
                </label>
                <ul>
                <?php foreach ($tree as $node) : ?>
                <?= $this->render_partial('coursewizard/lvgroups/_node',
                        array('node' => $node, 'stepnumber' => $stepnumber,
                            'temp_id' => $temp_id, 'values' => $values,
                            'open_nodes' => $open_nodes ?: array(),
                            'search_result' => $search_result ?: array())) ?>
                <?php endforeach ?>
                </ul>
            </li>
        </ul>
    </div>
    <?php if ($values['open_node']) : ?>
    <input type="hidden" name="open_node" value="<?= $values['open_node'] ?>"/>
    <?php endif ?>
    <?php if ($values['searchterm']) : ?>
    <input type="hidden" name="searchterm" value="<?= $values['searchterm'] ?>"/>
    <?php endif ?>
    <script type="text/javascript" language="JavaScript">
    //<!--
    $(function() {
        var element = $('#lvgroup-tree-search');
        element.on('keypress', function(e) {
            if (e.keyCode == 13) {
                if (element.val() != '') {
                    return MVV.CourseWizard.searchTree();
                } else {
                    return MVV.CourseWizard.resetSearch();
                }
            }
        });
    });
    //-->
    </script>
<? endif ?>
<? if(!$locked) : ?>
    <form action="<?= $controller->url_for('lvgselector/index/' . $course_id, $url_params) ?>" method="post">
<? endif ?>
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
            	<?= $this->render_partial('coursewizard/lvgroups/lvgroup_entry', compact('area')) ?>
              <? endforeach ?>

            </ul>
        </li>
    </ul>
</div>
<? if (!$locked) : ?>

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
                    <?= htmlReady($GLOBALS['UNI_NAME_CLEAN']) ?>
                </label>
                <ul>
                <? foreach ((array) $tree as $node) : ?>
                <?= $this->render_partial('coursewizard/lvgroups/_node',
                        array('node' => $node, 'stepnumber' => $stepnumber,
                            'temp_id' => $temp_id, 'values' => $values,
                            'open_nodes' => $open_nodes ?: array(),
                            'search_result' => $search_result ?: array())) ?>
                <? endforeach ?>
                </ul>
            </li>
        </ul>
    </div>
    <? if ($values['open_node']) : ?>
    <input type="hidden" name="open_node" value="<?= $values['open_node'] ?>"/>
    <? endif ?>
    <? if ($values['searchterm']) : ?>
    <input type="hidden" name="searchterm" value="<?= $values['searchterm'] ?>"/>
    <? endif ?>
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
<? if(!$locked) : ?>
        <div data-dialog-button <?/* class="hidden-no-js"*/?> style="clear: both; text-align: center">
        <?= Studip\Button::createAccept(_('Speichern'), "save") ?>
    </div>
</form>
<? endif ?>

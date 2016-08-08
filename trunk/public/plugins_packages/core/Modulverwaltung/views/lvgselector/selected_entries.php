<?
# Lifter010: TODO
?>
<ul id="lvgruppe_selection_selected">

  <? foreach ($selection->getAreas() as $area) : ?>
    <?= $this->render_partial('lvgselector/selected_entry', compact('area')) ?>
  <? endforeach ?>

</ul>


<ul>
	<? foreach ($subtree->getChildren() as $child) : ?>
		<li>
			<?
				$more_children = $child->getChildren();
				$show_link = sizeof($more_children);
			?>

			<div class="<?= TextHelper::cycle('odd', 'even') ?>">
				<?= $this->render_partial('course/study_areas/entry',
				                          array('area' => $child,
				                                'show_link' => $show_link)) ?>
			</div>

			<? if ($selection->getShowAll() && sizeof($more_children)) : ?>
				<?= $this->render_partial('course/study_areas/subtree', array('subtree' => $child)) ?>
			<? endif ?>

		</li>
	<? endforeach ?>
</ul>

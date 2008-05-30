<ul id="tabs">
	<? foreach ($structure as $value) : ?>
		<? if (!$value['topKat']) : ?>
			<li<?= $value['active'] ? ' class="current"' : '' ?>>
				<? if ($value['disabled']) : ?>
					<span class="quiet"><?= $value['name'] ?></span>
				<? else: ?>
					<a target="<?= $value['target'] ?>" href="<?= $value['link'] ?>">
						<?= $value['name'] ?>
					</a>
				<? endif ?>
			</li>
		<? endif ?>
	<? endforeach ?>
</ul>
<div class="tabs2">
<ul id="tabs2">
	<? foreach ($structure as $value) : ?>
		<? if ($value['topKat'] && $structure[$value['topKat']]['active']) : ?>
			<li<?= $value['active'] && !$noAktiveBottomkat ? ' class="current"' : '' ?>>
				<? if ($value['disabled']) : ?>
					<span class="quiet"><?= $value['name'] ?></span>
				<? else: ?>
					<a target="<?= $value['target'] ?>" href="<?= $value['link'] ?>">
						<?= $value['name'] ?>
					</a>
				<? endif ?>
			</li>
		<? endif ?>
	<? endforeach ?>
</ul>
</div>
<div class="clear"></div>

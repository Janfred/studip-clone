<div id="m<?=$id?>" class="messagebox_outer">
	<div class="messagebox_buttons">
		<? if(!empty($details)):?>
		<a href="#" onclick="Effect.toggle('d<?=$id?>', 'blind'); $('min<?=$id?>').toggle(); $('max<?=$id?>').toggle(); return false;"><?=Assets::img('icons/maximize_inv.png', array('id'=> 'max'.$id, 'alt' => 'show details', 'title' => _('Details anzeigen')));?><?=Assets::img('icons/minimize_inv.png', array('id'=> 'min'.$id, 'alt' => 'close details', 'title' => _('Details ausblenden'), 'style'=>'display: none;'));?></a>
		<?endif;?>
		<a href="#" onclick="$('m<?=$id?>').fade(); return false;"><?=Assets::img('icons/cross_inv.png', array('alt' => 'close', 'title' => _('Nachrichtenbox schließen')));?></a>
	</div>
	<div class="messagebox <?=$class?>">
		<div class="messagebox_text"><?=$message?></div>
		<? if(!empty($details)):?>
			<div id="d<?=$id?>" style="display: none;">
				<ul>
				<?foreach($details as $li): ?>
					<li><?=$li?></li>
				<?endforeach;?>
				</ul>
			</div>
		<? endif;?>
	</div>
</div>
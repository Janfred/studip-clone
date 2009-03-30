<!-- <div style="position: absolute; top: 0px; left: 0px; right: 0px; height: 100%; opacity: 0.5; background-color: black;z-index: 99"></div> -->
<div id="approvalMessageShadow" class="modalshadow borders">
	<div id="approvalMessage" class="modaldialog borders">
		<?= formatReady($question) ?><br/>
		<br/>
		<a href="<?= $approvalLink ?>">
			<?= makebutton('ja2') ?>
		</a> 
		<a href="<?= $denialLink ?>" style="margin-left: 20px">
			<?= makebutton('nein') ?>
		</a>
	</div>
</div>

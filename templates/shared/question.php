<div class="modalshadow">
	<div class="modaldialog">
		<?= formatReady($question) ?><br>
		<br>
		<a href="<?= $approvalLink ?>">
			<?= makebutton('ja2') ?>
		</a>
		<a href="<?= $disapprovalLink ?>" style="margin-left: 2em;">
			<?= makebutton('nein') ?>
		</a>
	</div>
</div>

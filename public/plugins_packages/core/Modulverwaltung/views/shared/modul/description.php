<?= $this->render_partial('shared/modul/_modul') ?>
<? if ($type === 1) : ?>
<?= $this->render_partial('shared/modul/_modullvs') ?>
<?= $this->render_partial('shared/modul/_pruefungen') ?>
<?= $this->render_partial('shared/modul/_regularien') ?>
<? endif;?>
<? if ($type === 2): ?>
<?= $this->render_partial('shared/modul/_modullv') ?>
<? endif; ?>
<? if ($type === 3) : ?>
<?= $this->render_partial('shared/modul/_modul_ohne_lv') ?>
<? endif; ?>
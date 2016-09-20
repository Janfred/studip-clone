<? $table = get_class($modul) == 'Modul' ? 'mvv_modul_deskriptor' : 'mvv_modulteil_deskriptor'; ?>
<? foreach ($GLOBALS[strtoupper($table)]['SPRACHE']['values'] as $key => $value) : ?>
<? $deskriptor_lang = $modul->deskriptoren->findOneBy('sprache', $key); ?>
<div style="padding-top:10px;">
    <a href="<?= URLHelper::getLink($link, array('display_language' => $key)) ?>">
        <img src="<?= Assets::image_path('languages/lang_' . mb_strtolower($key) . '.gif') ?>" alt="<?= $value['name'] ?>">
        <?= $value['name'] ?> (<?= (!$deskriptor_lang || $deskriptor_lang->isNew()) ? 'neu anlegen' : 'bearbeiten' ?>)
        <?= $key == $sprache ? Icon::create('accept', 'accept', array())->asImg() : '' ?>
    </a>
</div>
<? endforeach; ?>

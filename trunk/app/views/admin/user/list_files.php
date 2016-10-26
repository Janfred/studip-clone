<section class="contentbox">
    <header>
        <h1><?= _('Dateiübersicht') ?></h1>
    </header>
    <? foreach ($files as $file) : ?>
        <article id="<?= $file->id ?>" class="<?= ContentBoxHelper::classes($file->id) ?>">
            <header>
                <h1>
                    <a href="<?= ContentBoxHelper::href($file->id) ?>">
                        <?= htmlReady($file->name) ?>
                    </a>
                </h1>
                <?
                $type = empty($file->url) ? 0 : 6;
                $actionMenu = ActionMenu::get();
                $actionMenu->addLink(GetDownloadLink($file->id, $file->filename, $type),
                        _('Datei herunterladen'),
                        GetFileIcon(getFileExtension($file->filename), true));
                if ($type != 6 && !in_array($document['extension'], words('bz2 gzip tgz zip'))) {
                    $actionMenu->addLink(GetDownloadLink($file->id, $file->filename, $type, 'zip'),
                            _('Als ZIP herunterladen'),
                            Icon::create('folder-full', 'clickable'));
                }
                echo $actionMenu->render();
                ?>

            </header>
            <section>
                <article>
                    <p><?= htmlReady($file->description ?: _('Keine Beschreibung vorhanden'), true, true) ?></p>
                    <p><?= sprintf(_('<strong>Dateigröße:</strong> %u kB '), round($file->filesize / 1024)) ?></p>
                    <p><?= sprintf(_('<strong>Dateiname:</strong> %s '), $file->filename) ?></p>
                </article>

                <? if ($file->protected): ?>
                    <article>
                        <?= MessageBox::warning(_('Diese Datei ist urheberrechtlich geschützt'), [
                                _('Sie darf nur im Rahmen dieser Veranstaltung verwendet werden, jede weitere '
                                  . 'Verbreitung ist strafbar!')]) ?>
                    </article>
                <? endif ?>
            </section>
        </article>

    <? endforeach ?>
</section>

<? if (Request::int('from_index')) : ?>
    <footer data-dialog-button>
        <?= \Studip\LinkButton::create(_('Zurück zur Übersicht'),
            $controller->url_for('admin/user/activities/' . $user->user_id, $params),
            ['data-dialog' => 'size=50%']) ?>
    </footer>
<? endif ?>
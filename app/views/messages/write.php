<form name="write_message" action="<?= URLHelper::getLink("dispatch.php/messages/send") ?>" method="post" style="margin-left: auto; margin-right: auto;" data-dialog data-secure="#adressees > li:eq(1), .files > li:eq(1)">
    <input type="hidden" name="message_id" id="message_id" value="<?= htmlReady($default_message->id) ?>">
    <input type="hidden" name="answer_to" value="<?= htmlReady($answer_to) ?>">
    <div>
        <label for="user_id_1"><h4><?= _("An") ?></h4></label>
        <ul class="list-csv" id="adressees">
            <li id="template_adressee" style="display: none;" class="adressee">
                <input type="hidden" name="message_to[]" value="">
                <span class="visual"></span>
                <a class="remove_adressee"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
            </li>
            <? foreach ($default_message->getRecipients() as $user) : ?>
            <li class="adressee">
                <input type="hidden" name="message_to[]" value="<?= htmlReady($user['user_id']) ?>">
                <span class="visual">
                    <?= htmlReady($user['fullname']) ?>
                </span>
                <a class="remove_adressee"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
            </li>
            <? endforeach ?>
        </ul>
        <div class="message-search-wrapper">
        <?= QuickSearch::get("user_id", new StandardSearch("user_id"))
            ->fireJSFunctionOnSelect("STUDIP.Messages.add_adressee")
            ->withButton()
            ->render();

        $search_obj = new SQLSearch("SELECT auth_user_md5.user_id, {$GLOBALS['_fullname_sql']['full_rev']} as fullname, username, perms "
            . "FROM auth_user_md5 "
            . "LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) "
            . "WHERE "
            . "username LIKE :input OR Vorname LIKE :input "
            . "OR CONCAT(Vorname,' ',Nachname) LIKE :input "
            . "OR CONCAT(Nachname,' ',Vorname) LIKE :input "
            . "OR CONCAT(Nachname,', ',Vorname) LIKE :input "
            . "OR Nachname LIKE :input "
            . "OR Vorname LIKE :input"
            . " ORDER BY fullname ASC",
            _("Nutzer suchen"), "user_id");
        $mps = MultiPersonSearch::get("add_adressees")
           ->setLinkText(_('Mehrere Adressaten hinzufügen'))
            //->setDefaultSelectedUser($defaultSelectedUser)
            ->setTitle(_('Mehrere Adressaten hinzufügen'))
            ->setExecuteURL(URLHelper::getURL("dispatch.php/messages/write"))
            ->setJSFunctionOnSubmit("STUDIP.Messages.add_adressees")
            ->setSearchObject($search_obj);
        foreach (Statusgruppen::findContactGroups() as $group) {
            $mps->addQuickfilter(
                $group['name'],
                $group->members->pluck('user_id')
            );
        }
        echo $mps->render();
        ?>
        </div>
        <script>
            STUDIP.MultiPersonSearch.init();
        </script>
    </div>
    <div>
        <label>
            <h4><?= _("Betreff") ?></h4>
            <input type="text" name="message_subject" style="width: 100%" required value="<?= htmlReady($default_message['subject']) ?>">
        </label>
    </div>
    <div>
        <label>
            <h4><?= _("Nachricht") ?></h4>
            <textarea style="width: 100%; height: 200px;" name="message_body" class="add_toolbar wysiwyg"><?= wysiwygReady($default_message['message'],false) ?></textarea>
        </label>
    </div>
    <div>
        <ul class="message-options">
        <? if ($GLOBALS['ENABLE_EMAIL_ATTACHMENTS']): ?>
            <li>
                <a href="" onClick="STUDIP.Messages.toggleSetting('attachments'); return false;">
                    <?= Icon::create('staple', 'clickable')->asImg(40) ?>
                    <br>
                    <strong><?= _("Anhänge") ?></strong>
                </a>
            </li>
        <? endif; ?>
            <li>
                <a href="" onClick="STUDIP.Messages.toggleSetting('tags'); return false;">
                    <?= Icon::create('star', 'clickable')->asImg(40) ?>
                    <br>
                    <strong><?= _("Schlagworte") ?></strong>
                </a>
            </li>
            <li>
                <a href="" onClick="STUDIP.Messages.toggleSetting('settings'); return false;">
                    <?= Icon::create('admin', 'clickable')->asImg(40) ?>
                    <br>
                    <strong><?= _("Optionen") ?></strong>
                </a>
            </li>
            <li>
                <a href="" onClick="STUDIP.Messages.toggleSetting('preview'); STUDIP.Messages.previewComposedMessage(); return false;">
                    <?= Icon::create('visibility-visible', 'clickable')->asImg(40) ?>
                    <br>
                    <strong><?= _("Vorschau") ?></strong>
                </a>
            </li>
        </ul>
    </div>

<? if ($GLOBALS['ENABLE_EMAIL_ATTACHMENTS']): ?>
    <div id="attachments" style="<?= $default_attachments ? '' : 'display: none;'?>">
        <h4><?= _("Anhänge") ?></h4>
        <div>
            <ul class="files">
                <li style="display: none;" class="file">
                    <span class="icon"></span>
                    <span class="name"></span>
                    <span class="size"></span>
                    <a class="remove_attachment"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
                </li>
                <? if ($default_attachments) : ?>
                    <? foreach ($default_attachments as $a) : ?>
                    <li class="file" data-document_id="<?=$a['document_id']?>">
                    <span class="icon"><?=$a['icon']?></span>
                    <span class="name"><?=$a['name']?></span>
                    <span class="size"><?=$a['size']?></span>
                    <a class="remove_attachment"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
                    </li>
                    <? endforeach ?>
                <? endif ?>
            </ul>
            <div id="statusbar_container">
                <div class="statusbar" style="display: none;">
                    <div class="progress"></div>
                    <div class="progresstext">0%</div>
                </div>
            </div>
            <label style="cursor: pointer;">
                <input type="file" id="fileupload" multiple onChange="STUDIP.Messages.upload_from_input(this);" style="display: none;">
                <?= Icon::create('upload', 'clickable', ['title' => _("Datei hochladen"), 'class' => "text-bottom"])->asImg(20) ?>
                <?= _("Datei hochladen") ?>
            </label>

            <div id="upload_finished" style="display: none"><?= _("wird verarbeitet") ?></div>
            <div id="upload_received_data" style="display: none"><?= _("gespeichert") ?></div>
        </div>
    </div>
<? endif; ?>
    <div id="tags" style="<?= Request::get("default_tags") ? "" : 'display: none; ' ?>">
        <label>
            <h4><?= _("Schlagworte") ?></h4>
            <input type="text" name="message_tags" style="width: 100%" placeholder="<?= _("z.B. klausur termin statistik etc.") ?>" value="<?= htmlReady(Request::get("default_tags")) ?>">
        </label>
    </div>
    <div id="settings" style="display: none;">
        <h4><?= _("Optionen") ?></h4>
        <table class="" style="width: 100%">
            <tbody>
                <tr>
                    <td>
                        <label for="message_mail"><strong><?= _("Immer per Mail weiterleiten") ?></strong></label>
                    </td>
                    <td>
                        <input type="checkbox" name="message_mail" id="message_mail" value="1"<?= $mailforwarding ? " checked" : "" ?>>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="show_adressees"><strong><?= _("Sollen die Adressaten für die Empfänger sichtbar sein?") ?></strong></label>
                    </td>
                    <td>
                        <input type="checkbox" name="show_adressees" id="show_adressees" value="1"<?= $show_adressees ? " checked" : "" ?>>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="preview" style="display: none;">
        <h4><?= _("Vorschau") ?></h4>
        <p class="message_body"></p>
    </div>

    <div style="text-align: center;" data-dialog-button>
        <?= \Studip\Button::create(_('Abschicken'), null, array('onclick' => "STUDIP.Messages.checkAdressee();")) ?>
    </div>

</form>

<br>

<?php
$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/mail-sidebar.png');

if (false && count($tags)) {
    $folderwidget = new LinksWidget();
    $folderwidget->setTitle(_("Verwendete Tags"));
    foreach ($tags as $tag) {
        $folderwidget->addLink(ucfirst($tag), URLHelper::getURL("?", array('tag' => $tag)), null, array('class' => "tag"));
    }
    $sidebar->addWidget($folderwidget, 'folder');
}

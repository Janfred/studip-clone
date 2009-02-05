<div class="white" style="padding: 1ex;">
  <? if (isset($error_msg)): ?>
    <table style="width: 100%;">
      <? my_error($error_msg, '', 1, false, true) ?>
    </table>
  <? endif ?>
    <form action="<?= $controller->url_for('siteinfo/save') ?>" method="POST">
    <label for="rubric_name"><?= _('Titel der Kategorie')?></label><br>
  <? if($edit_rubric): ?>
        <input type="text" name="rubric_name" id="rubric_name" value="<?=$rubric_name?>"><br>
        <input type="hidden" name="rubric_id" value="<?= $rubric_id?>">
  <? else: ?>
        <select name="rubric_id">
      <? foreach ($rubrics as $option) : ?>
            <option value="<?= $option['rubric_id'] ?>"<? if($controller->currentrubric==$option['rubric_id']){echo " selected";} ?>><?= $option['name'] ?></option>
      <? endforeach ?>
        </select><br>
        <label for="detail_name"><?= _('Seitentitel')?></label><br>
        <input style="width: 90%;" type="text" name="detail_name" id="detail_name" value="<?=$detail_name?>"><br>
        <label for="content"><?= _('Seiteninhalt')?></label><br>
        <textarea style="width: 90%;height: 15em;" name="content" id="content"><?= $content ?></textarea><br>
        <input type="hidden" name="detail_id" value="<?= $currentdetail?>">
  <? endif ?>
        <?= makeButton("abschicken", "input") ?>
        <a href="<?= $controller->url_for('siteinfo/show/'.$currentrubric.'/'.$currentdetail) ?>">
            <?= makeButton("abbrechen", "img") ?>
        </a>
    </form>
  <? if(!$edit_rubric): ?>
    <div style="width:90%;background-color:#DEE2E8;padding:10px;">
        <h3><?= _('Verfügbares Markup')?></h3>
        <p><?= sprintf(_('Zusätzlich zu den üblichen %sSchnellformatierungen%s und dem Wiki-Markup ist folgendes Markup verfügbar:'), '<a href="http://hilfe.studip.de/index.php/Basis/VerschiedenesFormat">', '</a>')?></p>
        <dl>
            <dt>(:logofloater:)</dt>
            <dd><?= _('Das Stud.IP-Logo im Textfluß rechts angeordnet.')?></dd>
            <dt>(:version:)</dt>
            <dd><?= _('Die Angabe der verwendeten Stud.IP-Version.')?></dd>
            <dt>(:versionfloater:)</dt>
            <dd><?= _('Die Angabe der verwendeten Stud.IP-Version im Textfluss rechts angeordnet.')?></dd>
            <dt>(:uniname:)</dt>
            <dd><?= _('Der Name des Standortes gemäß der Konfiguration.')?></dd>
            <dt>(:unicontact:)</dt>
            <dd><?= _('Der administrative Kontakt gemäß der Konfiguration.')?></dd>
            <dt>(:userinfo <em>user</em>:)</dt>
            <dd><?= sprintf(_('Ausgabe von Vor- und Nachnamen verlinkt mit der persönlichen Homepage und der E-Mail-Adresse des mit %s angegebenen Nutzers.'),'<em>user</em>')?></dd>
            <dt>(:rootlist:)</dt>
            <dd><?= _('Ausgabe von Vor- und Nachnamen verlinkt mit der persönlichen Homepage und der E-Mail-Adresse aller Nutzer mit Root-Status.')?></dd>
            <dt>(:adminlist:)</dt>
            <dd><?= _('Ausgabe von Vor- und Nachnamen verlinkt mit der persönlichen Homepage und der E-Mail-Adresse aller Nutzer mit Admin-Status.')?></dd>
            <dt>(:coregroup:)</dt>
            <dd><?= _('Ausgabe des Inhaltes von http://www.studip.de/crew.php.')?></dd>
            <dt>(:toplist <em>subject</em>:)</dt>
            <dd><?= sprintf(_('Ausgabe von Ranglisten für die mit %s angegebenen Kriterien die die Ausprägungen'),
                            '<em>subject</em>')?>
                <ul>
                    <li>mostparticipants</li>
                    <li>recentlycreated</li>
                    <li>mostdocuments</li>
                    <li>mostpostings</li>
                    <li>mostvisitedhomepages</li>
                </ul>
                haben können.
            </dd>
            <dt>(:indicator <em>subject</em>:)</dt>
            <dd><?= sprintf(_('Ausgabe von mit %s spezifizierten Kennzahlen aus den folgenden Möglichkeiten'),
                            '<em>subject</em>')?>
                <ul>
                    <li>seminar_all</li>
                    <li>seminar_archived</li>
                    <li>institute_secondlevel_all</li>
                    <li>institute_firstlevel_all</li>
                    <li>user_admin</li>
                    <li>user_dozent</li>
                    <li>user_tutor</li>
                    <li>user_autor</li>
                    <li>posting</li>
                    <li>document</li>
                    <li>link</li>
                    <li>litlist</li>
                    <li>termin</li>
                    <li>news</li>
                    <li>guestbook</li>
                    <li>vote</li>
                    <li>test</li>
                    <li>evaluation</li>
                    <li>wiki_pages</li>
                    <li>lernmodul</li>
                    <li>resource</li>
                </ul>
            </dd>
            <dt>(:history:)</dt>
            <dd><?= _('Ausgabe der history.txt')?></dd>
        </dl>
    </div>
    <? endif ?>
</div>

<tr id="questionnaire_<?= $questionnaire->getId() ?>">
    <td>
        <?= htmlReady($questionnaire['title']) ?>
        <span>
            <?
            $icons = array();
            foreach ($questionnaire->questions as $question) {
                $class = $question['questiontype'];
                $icons[$class] = $class::getIcon();
            }
            foreach ($icons as $class => $icon) {
                echo $icon->asImg("20px", array('class' => "text-bottom", 'title' => $class::getName()));
            }
            ?>
        </span>
    </td>
    <td>
        <?= $questionnaire['startdate'] ? date("d.m.Y H:i", $questionnaire['startdate']) : _("händisch") ?>
    </td>
    <td>
        <?= $questionnaire['stopdate'] ? date("d.m.Y H:i", $questionnaire['stopdate']) : _("händisch") ?>
    </td>
    <td class="context">
        <? if (count($questionnaire->assignments)) : ?>
            <ul class="clean">
                <? foreach ($questionnaire->assignments as $assignment) : ?>
                    <li>
                        <? if ($assignment['range_id'] === "start") : ?>
                            <?= _("Stud.IP Startseite")?>
                        <? endif ?>
                        <? if ($assignment['range_id'] === "public") : ?>
                            <?= _("Öffentlich per Link")?>
                        <? endif ?>
                        <? if ($assignment['range_type'] === "user") : ?>
                            <?= _("Profilseite")?>
                        <? endif ?>
                        <? if ($assignment['range_type'] === "course") : ?>
                            <?= htmlReady(Course::find($assignment['range_id'])->name) ?>
                        <? endif ?>
                        <? if ($assignment['range_type'] === "institute") : ?>
                            <?= htmlReady(Institute::find($assignment['range_id'])->name) ?>
                        <? endif ?>
                    </li>
                <? endforeach ?>
            </ul>
        <? else : ?>
            <?= _("Nirgendwo") ?>
        <? endif ?>
    </td>
    <td>
        <? $countedAnswers = $questionnaire->countAnswers() ?>
        <?= htmlReady($countedAnswers) ?>
    </td>
    <td style="white-space: nowrap;">
        <? if ($questionnaire->isStarted() && $countedAnswers) : ?>
            <?= Icon::create("edit", "inactive")->asimg("20px", array('title' => _("Der Fragebogen wurde gestartet und kann nicht mehr bearbeitet werden."))) ?>
        <? else : ?>
            <a href="<?= URLHelper::getLink("dispatch.php/questionnaire/edit/".$questionnaire->getId()) ?>" data-dialog title="<?= _("Fragebogen bearbeiten") ?>">
                <?= Icon::create("edit", "clickable")->asimg("20px", array()) ?>
            </a>
        <? endif ?>
        <a href="<?= URLHelper::getLink("dispatch.php/questionnaire/context/".$questionnaire->getId()) ?>" data-dialog title="<?= _("Zuweisungen bearbeiten") ?>">
            <?= Icon::create("group2", "clickable")->asimg("20px", array()) ?>
        </a>

        <?
        $menu = ActionMenu::get();
        if ($questionnaire->isStarted()) {
            $menu->addLink(
                URLHelper::getLink("dispatch.php/questionnaire/stop/".$questionnaire->getId(), $range_type ? ['redirect' => "questionnaire/courseoverview"] : []),
                _("Fragebogen beenden"),
                Icon::create("pause", "clickable")
            );
        } else {
            $menu->addLink(
                URLHelper::getLink("dispatch.php/questionnaire/start/".$questionnaire->getId(), $range_type ? ['redirect' => "questionnaire/courseoverview"] : []),
                _("Fragebogen starten"),
                Icon::create("play", "clickable")
            );
        }
        $menu->addLink(
            URLHelper::getLink("dispatch.php/questionnaire/evaluate/".$questionnaire->getId()),
            _("Auswertung"),
            Icon::create("stat", "clickable"),
            array('data-dialog' => 1)
        );
        $menu->addLink(
            URLHelper::getLink("dispatch.php/questionnaire/export/".$questionnaire->getId()),
            _("Export als CSV"),
            Icon::create("file-excel", "clickable"),
            array('data-dialog' => 1)
        );
        $menu->addLink(
            URLHelper::getLink("dispatch.php/questionnaire/delete/".$questionnaire->getId()),
            _("Fragebogen löschen"),
            Icon::create("trash", "clickable"),
            array('onClick' => "return window.confirm('". _("Wirklich löschen?") . "');")
        );
        echo $menu;
        ?>
    </td>
</tr>
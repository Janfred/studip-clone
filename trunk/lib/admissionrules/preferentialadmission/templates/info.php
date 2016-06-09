<?= _('Folgende Personenkreise werden bei der Platzverteilung bevorzugt:') ?>
<br/>
<ul id="prefadmission_conditions">
    <?php
    $i = 0;
    foreach ($rule->getConditions() as $condition) {
        $condition->show_user_count = true;
    ?>
    <li class="condition" id="condition_<?= $condition->getId() ?>">
        <i><?= $condition->toString() ?></i>
    </li>
    <?php
        $i++;
    }
    ?>
</ul>
<?php if ($rule->getFavorSemester()) { ?>
<br/>
<i><?= _('Höhere Fachsemester werden bevorzugt behandelt.') ?></i>
<?php } ?>

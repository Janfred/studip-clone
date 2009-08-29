<?php
require_once 'lib/classes/StudygroupAvatar.class.php';
require_once 'lib/classes/Avatar.class.php';

$infobox = array();
$infobox['picture'] = 'infoboxbild_studygroup.jpg';
$infobox['content'] = array(
    array(
        'kategorie'=>_("Information"),
        'eintrag'=>array(
            array("text"=>"Studiengruppen sind eine einfache Möglichkeit, mit Kommilitonen, Kollegen und anderen zusammenzuarbeiten. Jeder kann Studiengruppen gründen. Auf dieser Seite finden Sie eine Liste aller Studiengruppen. Klicken Sie auf auf die Überschriften um die jeweiligen Spalten zu sortieren.","icon"=>"ausruf_small2.gif")
        )
    )
);


?>


<script type="text/javascript" src="<?=ASSETS::javascript_path('tablekit.js')?>"></script>

<table class="sortable" border="0" cellpadding="2" cellspacing="0" width="100%" align="center">
    <tr>
        <th width="60%" align="center"><?= _("Name") ?></th>
        <th width="10%" align="center" class="sortfirstasc"><?= _("gegründet") ?></th>
        <th width="3%" align="center"><?= _("Mitglieder") ?></th>
        <th width="2%" align="center"></th>
        <th width="15%" align="center"><?= _("GründerIn") ?></th>
        <th width="10%" align="center"><?= _("Zugang") ?></th>
    </tr>

    <? foreach ($groups as $group) : ?>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td>   
                    <a href="<?=URLHelper::getlink("dispatch.php/course/studygroup/details/".$group['Seminar_id'])?>">
                        <?=StudygroupAvatar::getAvatar($group['Seminar_id'])->getImageTag(Avatar::SMALL)?> <?=htmlready($group['Name'])?></a>
                
                       </td>
             <td align="center">
                <?=strftime('%x', $group['mkdate']);?>
            </td>
            <td align="center">
                <?=StudygroupModel::countMembers($group['Seminar_id']);?>
            </td>
            <td align="center">
                <? $founder = StudygroupModel::getFounder($group['Seminar_id']);
                   echo Avatar::getAvatar($founder['user_id'])->getImageTag(Avatar::SMALL);
                ?>
            </td>
            <td align="center">    
                <a href="<?=URLHelper::getlink('about.php?username='.$founder['uname'])?>"><?=htmlready($founder['fullname'])?></a>
            </td>
            <td align="center">
                <? if ($group['admission_prelim'] == 1) :?>
                    <?=Assets::img("closelock",array('title' => _('Mitgliedschaft muss beantragt werden')))?>
                <? endif;?>
            </td>
        </tr>

<? endforeach ; ?>

</table>
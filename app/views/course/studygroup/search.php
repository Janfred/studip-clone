<?php
require_once 'lib/classes/StudygroupAvatar.class.php';


$cssSw = new cssClassSwitcher();
$cssSw->enableHover();
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

<table class="sortable" border="0" cellpadding="2" cellspacing="0" width="98%" align="center" class="blank">
    <tr>
        <th width="70%" align="center"><b><?= _("Name") ?></b></th>
        <th width="10%" align="center"><b><?= _("Mitglieder") ?></b></th>
        <th width="10%" align="center"><b><?= _("GründerIn") ?></b></th>
        <th width="10%" align="center"><b><?= _("Weiteres") ?></b></th>
    </tr>

    <?$cssSw->resetClass();?>

    <? foreach ($groups as $group) : ?>
   
    
        <?$cssSw->switchClass();?>
        <tr <?=$cssSw->getHover()?> >
            <td class="<?=$cssSw->getClass()?>">   
               
                    <a href="<?=URLHelper::getlink("dispatch.php/course/studygroup/details/".$group['Seminar_id'])?>">
                        <?=StudygroupAvatar::getAvatar($SessSemName[1])->getImageTag(Avatar::SMALL)?> <?=$group['Name']?></a>
                
                       </td>
            <td class="<?=$cssSw->getClass()?>" align="center">
                <?=StudygroupModel::countMembers($group['Seminar_id']);?>
            </td>
            <td class="<?=$cssSw->getClass()?>" align="center">
                <? $founder = StudygroupModel::getFounder($group['Seminar_id']);?>
                <a href="<?=URLHelper::getlink('about.php?username='.$founder['uname'])?>"><?=$founder['fullname']?></a>
            </td>
            <td class="<?=$cssSw->getClass()?>" align="center">
                <? if ($group['admission_prelim'] == 1) :?>
                    <?=Assets::img("closelock",array('title' => _('Mitgliedschaft muss beantragt werden')))?>
                <? endif;?>
            </td>
        </tr>

<? endforeach ; ?>

</table>
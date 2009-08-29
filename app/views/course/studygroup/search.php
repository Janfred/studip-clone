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
<script type="text/javascript">
TableKit.options.rowEvenClass = 'cycle_even';
TableKit.options.rowOddClass = 'cycle_odd';
</script>
<style>
.sortasc {
	background-image: url(<?=Assets::image_path('dreieck_up.png')?>);
	background-repeat:no-repeat;
	background-position:center right;
	padding: 2px 15px 2px 15px;
	text-align:center;
}
.sortdesc {
	background-image: url(<?=Assets::image_path('dreieck_down.png')?>);
	background-repeat:no-repeat;
	background-position:center right;
	padding: 2px 15px 2px 15px;
	text-align:center;
}
th {
	background: #B5B5B5;
	padding: 2px 15px 2px 15px;
	text-align:center;

}
</style>
<table class="sortable" border="0" cellpadding="2" cellspacing="0" width="100%" align="center">
    <tr>
        <th width="60%"><?= _("Name") ?></th>
        <th width="10%" class="sortfirstdesc"><?= _("gegründet") ?></th>
        <th width="5%"><?= _("Mitglieder") ?></th>
        <th width="15%"><?= _("GründerIn") ?></th>
        <th width="10%"><?= _("Zugang") ?></th>
    </tr>

    <? foreach ($groups as $group) : ?>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td style="text-align:left;white-space:nowrap;">
            <img src="<?=StudygroupAvatar::getAvatar($group['Seminar_id'])->getUrl(Avatar::SMALL);?>" style="vertical-align:middle;">
                   <a href="<?=URLHelper::getlink("dispatch.php/course/studygroup/details/".$group['Seminar_id'])?>">
                   <?=htmlready($group['Name'])?></a>
             </td>
             <td align="center">
                <?=strftime('%x', $group['mkdate']);?>
            </td>
            <td align="center">
                <?=StudygroupModel::countMembers($group['Seminar_id']);?>
            </td>
            <td style="text-align:left;white-space:nowrap;">
                <? $founder = StudygroupModel::getFounder($group['Seminar_id']);?>
                <img src="<?=Avatar::getAvatar($founder['user_id'])->getUrl(Avatar::SMALL);?>" style="vertical-align:middle;">
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
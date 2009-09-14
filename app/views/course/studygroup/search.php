<?php
require_once 'lib/classes/StudygroupAvatar.class.php';
require_once 'lib/classes/Avatar.class.php';

$infobox = array();
$infobox['picture'] = 'infoboxbild_studygroup.jpg';
$infobox['content'] = array(
    array(
        'kategorie'=>_("Information"),
        'eintrag'=>array(
            array("text"=>_("Studiengruppen sind eine einfache Möglichkeit, mit Kommilitonen, Kollegen und anderen zusammenzuarbeiten. Jeder kann Studiengruppen gründen. Auf dieser Seite finden Sie eine Liste aller Studiengruppen. Klicken Sie auf auf die Überschriften um die jeweiligen Spalten zu sortieren."),"icon"=>"ausruf_small2.gif")
        )
    )
);


?>


<script type="text/javascript" src="<?=ASSETS::javascript_path('tablekit.js')?>"></script>
<script type="text/javascript">
TableKit.options.rowEvenClass = 'cycle_even';
TableKit.options.rowOddClass = 'cycle_odd';
TableKit.Sortable.addSortType(
	new TableKit.Sortable.Type('date-de_DE',{
		pattern : /^\d{2}\.\d{2}\.\d{4}/,
		normal : function(v) {
			v = v.strip();
			if(!this.pattern.test(v)) {return 0;}
			var r = v.match(/^(\d{2})\.(\d{2})\.(\d{4})/);
			var yr_num = r[3];
			var mo_num = parseInt(r[2],10)-1;
			var day_num = r[1];
			return new Date(yr_num, mo_num, day_num).valueOf();
		}})
	);
TableKit.Sortable.addSortType(
	new TableKit.Sortable.Type('date-en_GB',{
		pattern : /^\d{2}\/\d{2}\/\d{2}/,
		normal : function(v) {
			v = v.strip();
			if(!this.pattern.test(v)) {return 0;}
			var r = v.match(/^(\d{2})\/(\d{2})\/(\d{2})/);
			var yr_num = '20' + r[3];
			var mo_num = parseInt(r[2],10)-1;
			var day_num = r[1];
			return new Date(yr_num, mo_num, day_num).valueOf();
		}})
	);
</script>
<style>
.sortasc {
	background-image: url(<?=Assets::image_path('dreieck_up.png')?>);
	background-repeat:no-repeat;
	background-position:center right;
}
.sortdesc {
	background-image: url(<?=Assets::image_path('dreieck_down.png')?>);
	background-repeat:no-repeat;
	background-position:center right;
}
th {
	background: none;
	padding: 2px 15px 2px 15px;
	text-align:center;
}
</style>
<table class="sortable" border="0" cellpadding="2" cellspacing="0" width="100%">
<tr style="background: url(<?=Assets::image_path('steelgraudunkel.gif')?>);cursor: pointer;" title="<?=_("Klicken, um die Sortierung zu ändern")?>">
        <th width="60%"><?= _("Name") ?></th>
        <th width="10%" class="date-<?=$GLOBALS['_language']?> sortfirstdesc"><?= _("gegründet") ?></th>
        <th width="5%"><?= _("Mitglieder") ?></th>
        <th width="15%"><?= _("GründerIn") ?></th>
        <th width="5%"><?= _("Mitglied") ?></th>
        <th width="5%"><?= _("Zugang") ?></th>
    </tr>

    <? foreach ($groups as $group) : ?>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td style="text-align:left;white-space:nowrap;">
            <img src="<?=StudygroupAvatar::getAvatar($group['Seminar_id'])->getUrl(Avatar::SMALL);?>" style="vertical-align:middle;">
                   <a href="<?=URLHelper::getlink("dispatch.php/course/studygroup/details/".$group['Seminar_id'])?>">
                   <?=htmlready($group['Name'])?></a>
             </td>
             <td align="center"><?=strftime('%x', $group['mkdate']);?>
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
                <? if (StudygroupModel::isMember($this->userid,$group['Seminar_id'] )) :?>
                    <?=Assets::img("members.png",array('title' => _('Sie sind Mitglied in dieser Gruppe')))?>
                <? endif;?>
            </td>
            <td align="center">
                <? if ($group['admission_prelim'] == 1) :?>
                    <?=Assets::img("closelock",array('title' => _('Mitgliedschaft muss beantragt werden')))?>
                <? endif;?>
            </td>
        </tr>

<? endforeach ; ?>

</table>
<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_lit_element.php
// 
// 
// Copyright (c) 2003 André Noack <noack@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("autor");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipLitCatElement.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipLitClipBoard.class.php");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

//html attributes for form
$_attributes['text'] = array('style' => 'width:100%');
$_attributes['textarea'] = array('style' => 'width:100%','rows'=>2);
$_attributes['select'] = array();
$_attributes['date'] = array();
$_attributes['combo'] = array('style' => 'width:45%');
$_attributes['lit_select'] = array('style' => 'font-size:8pt;width:100%');


if ($_REQUEST['cmd'] == "new_entry"){
	$_catalog_id = "new_entry";
} else {
	$_catalog_id = isset($_REQUEST['_catalog_id']) ? $_REQUEST['_catalog_id'] : "new_entry";
}

//dump data into db if $_catalog_id points to a search result
if ($_catalog_id{0} == "_"){
		$parts = explode("__", $_catalog_id);
		if ( ($fields = $GLOBALS[$parts[0]][$parts[1]]) ){
			$cat_element = new StudipLitCatElement();
			$cat_element->setValues($fields);
			$cat_element->setValue("catalog_id", "new_entry");
			$cat_element->insertData();
			$_catalog_id = $cat_element->getValue("catalog_id");
			$GLOBALS[$parts[0]][$parts[1]]['catalog_id'] = $_catalog_id;
			unset($cat_element);
		}
}

$_the_element =& new StudipLitCatElement($_catalog_id, true);
$_the_form =& $_the_element->getFormObject();
$_the_clipboard =& StudipLitClipBoard::GetInstance();
$_the_clip_form =& $_the_clipboard->getFormObject();

$_the_clip_form->form_fields['clip_cmd']['options'][] = array('name' => _("In Merkliste eintragen"), 'value' => 'ins');
$_the_clip_form->form_fields['clip_cmd']['options'][] = array('name' => _("Markierten Eintrag bearbeiten"), 'value' => 'edit');


if ($_the_form->IsClicked("reset") || $_REQUEST['cmd'] == "new_entry"){
	$_the_form->doFormReset();
}

if ($_the_form->IsClicked("delete") && $_catalog_id != "new_entry" && $_the_element->isChangeable()){
	if ($_the_element->reference_count){
		$_msg = "info§" . sprintf(_("Sie k&ouml;nnen diesen Eintrag nicht l&ouml;schen, da er noch in %s Literaturlisten referenziert wird."),$_the_element->reference_count) ."§";
	} else {
		$_msg = "info§" . _("Wollen Sie diesen Eintrag wirklich l&ouml;schen?") . "<br>"
				. "<a href=\"" . $PHP_SELF . "?cmd=delete_element&_catalog_id=" . $_catalog_id . "\">"
				. "<img " .makeButton("ja2","src") . tooltip(_("löschen"))
				. " border=\"0\"></a>&nbsp;"
				. "<a href=\"" . $PHP_SELF . "?_catalog_id=" . $_catalog_id  . "\">"
				. "<img " .makeButton("nein","src") . tooltip(_("abbrechen"))
				. " border=\"0\"></a>§";
	}
}

if ($_REQUEST['cmd'] == "delete_element" && $_the_element->isChangeable() && !$_the_element->reference_count){
	$_the_element->deleteElement();
}

if ($_the_form->IsClicked("send")){
	$_the_element->setValuesFromForm();
	if ($_the_element->checkValues()){
		$_the_element->insertData();
	}
}

if ($_the_clip_form->isClicked("clip_ok")){
	if ($_the_clip_form->getFormFieldValue("clip_cmd") == "ins" && $_catalog_id != "new_entry"){
		$_the_clipboard->insertElement($_catalog_id);
	}
	if ($_the_clip_form->getFormFieldValue("clip_cmd") == "edit"){
		$marked = $_the_clip_form->getFormFieldValue("clip_content");
		if (count($marked) && $marked[0]){
			$_the_element->getElementData($marked[0]);
		}
	}
	$_the_clipboard->doClipCmd();
}

$_catalog_id = $_the_element->getValue("catalog_id");

$_msg .= $_the_element->msg;
$_msg .= $_the_clipboard->msg;
	
?>
<body>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
	<tr>
		<td class="topic" colspan="2"><b>&nbsp;<?=_("Literatureintrag bearbeiten")?></b></td>
	</tr>
	<tr>
	<td class="blank" width="99%" align="left" valign="top">

	<?
if ($_msg)	{
	echo "\n<table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
	parse_msg ($_msg,"§","blank",1,false);
	echo "\n</table>";
} else {
	echo "<br><br>";
}
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<?
$class_changer = new CssClassSwitcher();
echo $_the_form->getFormStart("$PHP_SELF?_catalog_id=$_catalog_id");
echo "<tr><td " . $class_changer->getFullClass() . " align=\"left\" width=\"40%\" style=\"font-size:10pt;\">"
	. sprintf(_("Anzahl an Referenzen für diesen Eintrag: %s"), (int)$_the_element->reference_count) ."</td>";
echo "<td " . $class_changer->getFullClass() . " align=\"center\">";
if ($_the_element->isChangeable()){
	echo $_the_form->getFormButton("send") .  $_the_form->getFormButton("delete") ;
}
echo  $_the_form->getFormButton("reset");
echo "<img src=\"pictures/blank.gif\"  height=\"28\" width=\"15\" border=\"0\">";
echo "<a href=\"$PHP_SELF?cmd=new_entry\"><img border=\"0\" "
	. makeButton('neuanlegen','src') . tooltip(_("Neuen Eintrag anlegen")) ."></a></td></tr>";

foreach ($_the_element->fields as $field_name => $field_detail){
	if ($field_detail['caption']){
		echo "<tr><td " . $class_changer->getFullClass() . ">";
		echo $_the_form->getFormFieldCaption($field_name,array('style'=>'font-weight:bold;font-size:10pt;'));
		echo $_the_form->getFormFieldInfo($field_name);
		echo "</td><td " . $class_changer->getFullClass() . ">";
		$attributes = $_attributes[$_the_form->form_fields[$field_name]['type']];
		if (!$_the_element->isChangeable()){
			$attributes['readonly'] = 'readonly';
		}
		echo $_the_form->getFormField($field_name, $attributes);
		if ($field_name == "lit_plugin"){
			echo "&nbsp;&nbsp;<span style=\"font-size:10pt;\">";
			if (($link = $_the_element->getValue("external_link"))){
				echo formatReady("=) [Test externer Link]" . $link);
			} else {
				echo _("(Kein externer Link vorhanden.)");
			}
			echo "</span>";
		}
		echo "</td></tr>";
	}
	$class_changer->switchClass();
}
$class_changer->switchClass();
echo "<tr><td align=\"left\" " . $class_changer->getFullClass() . " width=\"40%\" style=\"font-size:10pt;\">&nbsp;</td>";
echo "<td " . $class_changer->getFullClass() . " align=\"center\">";
if ($_the_element->isChangeable()){
	echo $_the_form->getFormButton("send") .  $_the_form->getFormButton("delete") ;
}
echo  $_the_form->getFormButton("reset");
echo "<img src=\"pictures/blank.gif\"  height=\"28\" width=\"15\" border=\"0\">";
echo "<a href=\"$PHP_SELF?cmd=new_entry\"><img border=\"0\" "
	. makeButton('neuanlegen','src') . tooltip(_("Neuen Eintrag anlegen")) ."></a></td></tr>";
?>
</table>
</td>
<td class="blank" align="center" valign="top">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="blank" width="270" align="right" valign="top">
<?
$infobox[0] = array ("kategorie" => _("Information:"),
					"eintrag" =>	array(	
									array("icon" => "pictures/blank.gif","text"  =>	_("Hier können Sie Literatur / Quellen erfassen, oder von Ihnen erfasste Einträge ändern.")),
									array("icon" => "pictures/blank.gif","text"  =>	"<b>" . _("Eingetragen von:") . "</b><br>" . get_fullname($_the_element->getValue("user_id"))),
									array("icon" => "pictures/blank.gif","text"  =>	"<b>" . _("Letzte Änderung am:") . "</b><br>" . strftime("%d.%m.%Y",$_the_element->getValue("chdate")))
									)
					);
if ($_the_element->isNewEntry()){
	$infobox[0]["eintrag"][] = array("icon" => "pictures/ausruf_small.gif","text"  => _("Dies ist ein neuer Eintrag, der noch nicht gespeichert wurde!") );
}
if (!$_the_element->isChangeable()){
	$infobox[0]["eintrag"][] = array("icon" => "pictures/ausruf_small.gif","text"  => _("Sie haben diesen Eintrag nicht selbst vorgenommen, und dürfen ihn daher nicht verändern!") );
}						
$infobox[1] = array ("kategorie" => _("Aktionen:"));
$infobox[1]["eintrag"][] = array("icon" => "pictures/link_intern.gif","text"  => "<a href=\"admin_lit_list.php\">" . _("Literaturlisten bearbeiten") . "</a>" );
$infobox[1]["eintrag"][] = array("icon" => "pictures/link_intern.gif","text"  => "<a href=\"lit_search.php\">" . _("Literatur suchen") . "</a>" );

print_infobox ($infobox,"pictures/browse.jpg");

?>
</td>
</tr>
<tr>
	<td class="blank" align="center" valign="top">
	<b><?=_("Merkliste:")?></b>
	<br>
	<?=$_the_clip_form->getFormField("clip_content", array_merge(array('size' => $_the_clipboard->getNumElements()), $_attributes['lit_select']))?>
	<div align="center" style="background-image:url(pictures/border.jpg);background-repeat:repeat-y;margin:3px;"><img src="pictures/blank.gif" height="2" border="0"></div>
	<?=$_the_clip_form->getFormField("clip_cmd", $_attributes['lit_select'])?>
	<div align="center">
	<?=$_the_clip_form->getFormButton("clip_ok",array('style'=>'vertical-align:middle;margin:3px;'))?>
	</div>
	</td>
</tr>
</table>
<?
echo $_the_clip_form->getHiddenField(md5("is_sended"),1) . $_the_form->getFormEnd();
?>
</td>
</tr>
</table>
</body>
<?
page_close()
?>

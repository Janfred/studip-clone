<?php

require_once('lib/msg.inc.php');

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

class StudIPTemplateEngine {

	function makeHeadline($title,$full_width=true,$img=""){
		if (!$full_width) {
			echo "\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"blank\" width=\"70%\">";
		} else {
			echo"\n<table  border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" >";
		}
		// echo "\n<tr><td>";
		if (strlen($img) > 0){
			printf("\n<tr><td class=\"topic\" width=\"99%%\">&nbsp;<img src=\"$img\" border=\"0\" align=\"texttop\"><b>&nbsp;&nbsp;");
		}
		else {
			print("\n<tr><td class=\"topic\" width=\"99%%\">&nbsp;<b>&nbsp;&nbsp;");
		}
		printf($title);
		printf("</b></td></tr></table>");
	}

	function startContentTable($full_width=true){
		if (!$full_width){
			echo ("<table border=\"0\" width=\"70%\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\">");
		}
		else {
			echo ("<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\">");
		}
		?>
		<tr>
			<td height="5" colspan="3"></td>
		</tr>
		<tr>
			<td width="5">
			<!-- Pixelrand 1%??-->
			</td>
			<td valign="top">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
		<?php
	}

	function createInfoBoxTableCell(){
		?>
					</td>
				</tr>
				</table>
			   </td>
			<td align="right" valign="top" width="270" class="blank">
		<?php
	}

	function endInfoBoxTableCell(){
		?>
			</td>
		</tr>
		<tr>
			<td width="5">
			<!-- Pixelrand 1%??-->
			</td>
			<td valign="top">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
		<?php
	}

	function endContentTable(){
		?>
					</td>
				</tr>
				   </table>
			</td>
			<td width="5">
			</td>
		</tr>
		<tr>
			<td height="5" colspan="3"></td>
		</tr>
		</table>
		<?php
	}

	function makeContentHeadline($title,$colspan=2){
		echo(sprintf("<table width=\"100%%\" cellpadding=0 cellspacing=0><tr><th align=\"left\">&nbsp;%s</th></tr></table>",$title));
	}

	function showErrorMessage($text,$colspan=2){
                parse_msg_array(array(array('error', $text)));
	}

	function showSuccessMessage($text,$colspan=2){
                parse_msg_array(array(array('msg', $text)));
	}

	function showInfoMessage($text,$colspan=2){
                parse_msg_array(array(array('info', $text)));
	}

	function showQuestionMessage($text,$colspan=2,$newrow=true){
		$colspan = $colspan -1;
		?>

		<tr>
			<td valign="top"><img src="<?=$GLOBALS['ASSETS_URL']?>images/ausruf.gif"></td>
			<td valign="top" colspan=<?= $colspan?>>
			<?= sprintf("%s <br>", htmlReady($text))?>
			<?= sprintf("<a href=\"%s\">" . makeButton("ja2") . "</a>&nbsp; \n",$GLOBALS["PHP_SELF"])?>
			<?= sprintf("<a href=\"$PHP_SELF\">" . makeButton("nein") . "</a>\n")?>
			</td>
		</tr>
		<tr>
			<td colspan="<?=$colspan?>" height="5">&nbsp;</td>
		</tr>
		<?php
	}
}
?>

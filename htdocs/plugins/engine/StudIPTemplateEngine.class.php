<?php
/**
* @author Dennis Reil <Dennis.Reil@offis.de>
* @version $Revision$
* @package pluginengine
*/

require_once("msg.inc.php");

class StudIPTemplateEngine {
	
	function makeHeadline($title,$full_width=false,$img=""){
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
		printf("</b></td>\n<td align = \"right\" class=\"topic\">&nbsp;&nbsp;</td></tr>");
	}
	
	function startContentTable($full_width=false){
		if (!$full_width){
			echo ("<table border=\"0\" width=\"70%\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\">");
		}
		else {
			echo ("<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\">");
		}
		?>
		<tr>
			<td height="5" colspan="2"></td>
	    </tr>
		<tr>
			<td width="5">
			<!-- Pixelrand 1%??-->
			</td>
			<td valign="top">
				<table border="0" cellpadding="0" cellspacing="0">
		<?php
	}
	
	function createInfoBoxTableCell(){
		?>		
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
				<table border="0" cellpadding="0" cellspacing="0">
		<?php
	}
	
	function endContentTable(){
		?>
		  	 	</table>
			</td>
			<td width="5">
			</td>
		</tr>
		<tr>
			<td height="5" colspan="2"></td>
		</tr>
		</table>
		<?php
	}
	
	function showErrorMessage($text,$colspan=2){
		my_error($text,"blank",$colspan);
	}
	
	function showSuccessMessage($text,$colspan=2){
		my_msg($text,"blank",$colspan);
	}
	
	function showInfoMessage($text,$colspan=2){
		my_info($text,"blank",$colspan);
	}
}
?>
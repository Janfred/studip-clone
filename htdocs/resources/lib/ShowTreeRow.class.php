<?

/*****************************************************************************
print a row in the common stud.ip printhead/content style
/*****************************************************************************/
class ShowTreeRow {

	function ShowRow($icon, $link, $titel, $zusatz, $level='', $lines='', $weitere, $new=FALSE, $open="close", $content=FALSE, $edit='', $breite="99%") {
		
		?><table border=0 cellpadding=0 cellspacing=0 width="100%">
			<tr>
				<td class="blank" valign="top" heigth=21 nowrap><img src="pictures/forumleer.gif"><img src="pictures/forumleer.gif"><?
	
		if (!$content)
			$content=_("Keine Beschreibung");
		
		//Struktur darstellen
		$striche = "";
		for ($i=0;$i<$level;$i++) {
			if ($i==($level-1)) {
				if ($this->lines[$i+1]>1) 
					$striche.= "<img src=\"pictures/forumstrich3.gif\" border=0>"; 		//Kreuzung
				else
					$striche.= "<img src=\"pictures/forumstrich2.gif\" border=0>"; 		//abknickend
				$this->lines[$i+1] -= 1;
			} else {
				if ($this->lines[$i+1]==0) 
					$striche .= "<img src=\"pictures/forumleer.gif\" border=0>";			//Leerzelle
				else
					$striche .= "<img src=\"pictures/forumstrich.gif\" border=0>";		//Strich
			}
		}
	
		echo $striche;
					?></td>
					<?
	
		//Kofzeile ausgeben
		 printhead ($breite, 0, $link, $open, $new, $icon, $titel, $zusatz);
			?><td class="blank" width="*">&nbsp;</td>
			</tr>
		</table>
		<?	 
		 
		 //weiter zur Contentzeile
		 if ($open=="open") {
		?><table width="100%" cellpadding=0 cellspacing=0 border=0>
			<tr>
				<?
			 	//wiederum Striche fuer Struktur
				?><td class="blank" nowrap background="pictures/forumleer.gif"><img src="pictures/forumleer.gif"><img src="pictures/forumleer.gif"></td>
				<?
				$striche='';
				if ($level)
					for ($i=1;$i<=$level;$i++) {
						if ($this->lines[$i]==0) {
							$striche.= "<td class=\"blank\" nowrap background=\"pictures/forumleer.gif\"><img src=\"pictures/forumleer.gif\"></td>";
							}
						else {
							$striche.= "<td class=\"blank\" nowrap background=\"pictures/forumstrich.gif\"><img src=\"pictures/forumleer2.gif\"></td>";
							}
					}

				if ($weitere)
					$striche.= "<td class=\"blank\" nowrap background=\"pictures/forumstrichgrau.gif\"><img src=\"pictures/forumleer.gif\"></td>";
				else 
					$striche.= "<td class=\"blank\" nowrap background=\"pictures/steel1.jpg\"><img src=\"pictures/forumleer.gif\"></td>";

				echo $striche;
		
				//Contenzeile ausgeben
				printcontent ($breite, FALSE, $content, $edit);
				?><td class="blank" width="*">
					&nbsp;
				</td>
			</tr>	
		</table>
		<?
		}
	}
}
?>
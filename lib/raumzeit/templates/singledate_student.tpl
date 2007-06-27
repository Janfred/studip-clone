<? if (!$tpl['deleted']) : ?>
<TR>
	<?
		/*$open = ($issue_open[$tpl['sd_id']] ? 'close' : 'open');
		$link = $PHP_SELF."cmd=$open&open_close_id={$tpl['sd_id']}#{$tpl['sd_id']}";
		$icon = '<IMG src="'.$GLOBALS['ASSETS_URL'].'images/termin-icon.gif" border="0" align="abstop">&nbsp;';

		$zusatz = '';
		if ($tpl['fileEntry']) {
	    $zusatz = "<a href=\"folder.php?open={$tpl['folder_id']}&cmd=tree#anker\">";
			if ($tpl['fileCount'] > 0) {
				$zusatz .= "<img src=\"{$GLOBALS['ASSETS_URL']}images/icon-disc2.gif\" border=\"0\" align=\"absbottom\" ".tooltip($tpl['fileCount'].' '._("neue Dateien")).'>';
			} else {
				$zusatz .= "<img src=\"{$GLOBALS['ASSETS_URL']}images/icon-disc.gif\" border=\"0\" align=\"absbottom\" ".tooltip(_("Zum Dateiordner wechseln")).'>';
			}
			$zusatz .= '</a>';
		}
																		
		printhead(FALSE, FALSE, $link, $open, FALSE, $icon, $tpl['theme_title'], $zusatz);*/
	?>
	<TD width="1%" align="right" valign="top" class="<?=$tpl['class']?>" nowrap>
		<A name="<?=$tpl['sd_id']?>">
		<A href="<?=$PHP_SELF?>?cmd=<?=($issue_open[$tpl['sd_id']]) ? 'close' : 'open'?>&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
			<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/forumgrau<?=($issue_open[$tpl['sd_id']] || $tpl['openall']) ? 'runt' : ''?>.gif" border="0" align="abstop">
		</A>
	</TD>
	<TD width="1%" align="right" valign="top" class="<?=$tpl['class']?>" nowrap>
		<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/termin-icon.gif" border="0" align="abstop">&nbsp;
	</TD>
	<TD nowrap class="<?=$tpl['class']?>" valign="bottom">
		<A class="tree" href="<?=$PHP_SELF?>?cmd=<?=($issue_open[$tpl['sd_id']]) ? 'close' : 'open'?>&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
			<FONT size="-1">
				<?=$tpl['date']?>&nbsp;&nbsp;&nbsp;&nbsp;
			</FONT>
		</A>
	</TD>
	<TD width="80%" nowrap class="<?=$tpl['class']?>" valign="bottom">
		<FONT size="-1" color="#000000">
			<?=mila($tpl['theme_title']);?>
		</FONT>
	</TD>
	<TD width="2%" class="<?=$tpl['class']?>" valign="bottom">
	<? if ($tpl['forumEntry']) { ?>
		<a href="forum.php?open=<?=$tpl['issue_id']?>&treeviewstartposting=&view=#anker">
		<? if ($tpl['forumCount'] > 0) { ?>
			<img src="<?=$GLOBALS['ASSETS_URL']?>images/icon-posting2.gif" border="0" align="absbottom" <?=tooltip($tpl['forumCount'].' '._("neue Postings"))?>>
		<? } else { ?>
			<img src="<?=$GLOBALS['ASSETS_URL']?>images/icon-posting.gif" border="0" align="absbottom" <?=tooltip(_("Zum Forenthema wechseln"))?>>
		<? } ?>
		</a>
	<? } ?>
	</TD>
	<TD width="2%" class="<?=$tpl['class']?>" valign="bottom">
	<? if ($tpl['fileEntry']) { ?>
		<a href="folder.php?open=<?=$tpl['folder_id']?>&cmd=tree#anker">
		<? if ($tpl['fileCount'] > 0) { ?>
			<img src="<?=$GLOBALS['ASSETS_URL']?>images/icon-disc2.gif" border="0" align="absbottom" <?=tooltip($tpl['fileCount'].' '._("neue Dateien"))?>>
		<? } else { ?>
			<img src="<?=$GLOBALS['ASSETS_URL']?>images/icon-disc.gif" border="0" align="absbottom" <?=tooltip(_("Zum Dateiordner wechseln"))?>>
		<? } ?>
		</a>
	<? } ?>
	</TD>
	<TD width="10%" nowrap class="<?=$tpl['class']?>" valign="bottom" align="right">
		<FONT size="-1" color="#000000">
			<?=$tpl['room']?>&nbsp;&nbsp;
		</FONT>
		<? if ($tpl['calendar']) :
			echo $tpl['calendar'];
			echo '&nbsp;';
		endif ?>
	</TD>
</TR>
<? if ($issue_open[$tpl['sd_id']] || $tpl['openall']) { ?>
<TR>
	<TD colspan="7" class="steel1" align="left">
			<FONT size="-1">
				&nbsp;&nbsp;<BR/>
				&nbsp;&nbsp;<B><?=($tpl['theme_title']) ? $tpl['theme_title'] : _("Keine Titel vorhanden.")?></B><BR/>
				&nbsp;&nbsp;<?=($tpl['theme_description']) ? $tpl['theme_description'] : _("Keine Beschreibung vorhanden.")?><BR/>
				&nbsp;&nbsp;<BR/>
				&nbsp;&nbsp;<B><?=_("Art des Termins:")?></B>&nbsp;<?=$tpl['art']?><BR/>
				&nbsp;&nbsp;<BR/>
				<? if ($tpl['additional_themes']) { ?>
				&nbsp;&nbsp;<U><?=_("Weitere Themen:")?></U><BR/>
				<?	foreach ($tpl['additional_themes'] as $val) { ?>
					&nbsp;&nbsp;<B><?=$val['title']?></B><BR/>
					&nbsp;&nbsp;<?=$val['desc']?><BR/>
					&nbsp;&nbsp;<BR/>
				<? 	}
					}
				?>
			</FONT>
	</TD>
</TR>
<? } ?>
<? else: 	// Gelöschter Termin... ?>
<tr>
	<td width="1%" align="right" valign="top" class="steelred" nowrap>
		<a name="<?=$tpl['sd_id']?>" />
		<!--<a href="<?=$PHP_SELF?>?cmd=<?=($issue_open[$tpl['sd_id']]) ? 'close' : 'open'?>&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
			<img src="<?=$GLOBALS['ASSETS_URL']?>images/forumrot<?=($issue_open[$tpl['sd_id']]) ? 'runt' : '3'?>.gif" border="0" align="abstop">
		</a>-->
	</td>
	<td width="1%" align="right" valign="top" class="steelred" nowrap>
		<img src="<?=$GLOBALS['ASSETS_URL']?>images/termin-icon.gif" border="0" align="abstop">&nbsp;
	</td>
	<td nowrap class="steelred" valign="bottom">
		<a class="tree" href="<?=$PHP_SELF?>?cmd=<?=($issue_open[$tpl['sd_id']]) ? 'close' : 'open'?>&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
			<font size="-1">
				<?=$tpl['date']?>&nbsp;&nbsp;&nbsp;&nbsp;
			</font>
		</a>
	</td>
	<td width="80%" colspan="4" class="steelred" valign="bottom">
		<font size="-1" style="text-color: red">
			<b><?=_("Dieser Termin findet nicht statt!")?></b>
		<font>
		<font size="-1">
			&nbsp;(<?=_("Kommentar")?>: <?=$tpl['comment']?>)
		</font>
	</td>
</tr>
<?
endif;
unset($tpl)
?>

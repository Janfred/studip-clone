<tr>
	<td class="blank" valign="top">
		<div style="border: 1px solid black; padding-left: 10px; background-image: url('<?=$GLOBALS['ASSETS_URL']?>/images/steel1.jpg');">
		<form action="<?=$PHP_SELF?>?view=Karriere&subview=AddPersonToRole" method="post">
			<center><b><?=_("Person einer Gruppe zuordnen")?></b></center>
			<br/>			
			<? if (!$subview_id || !($groups = GetAllStatusgruppen($subview_id))) { ?>
			<?=_("Einrichtung auswählen")?>:<br/>
			<select name="subview_id">						
				<option value="NULL"><?=_("-- bitte Einrichtung ausw&auml;hlen --")?></option>
				<? if (is_array($admin_insts)) foreach ($admin_insts as $data) { ?>
				<option value="<?=$data['Institut_id']?>" style="<?=($data["is_fak"] ? "font-weight:bold;" : "")?>" <?=($subview_id==$data['Institut_id'])? 'selected="selected"':''?>><?=htmlReady(substr($data["Name"], 0, 70))?></option>
				<?
				if ($data["is_fak"]) {
					$db_r = new DB_Seminar();
					$db_r->query("SELECT Institut_id, Name FROM Institute WHERE fakultaets_id='" . $data['Institut_id'] . "' AND institut_id!='" . $data['Institut_id'] . "' ORDER BY Name");
					while ($db_r->next_record()) {
						printf("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s </option>\n", ($subview_id == $db_r->f('Institut_id')) ? 'selected="selected"' : '', $db_r->f("Institut_id"), htmlReady(substr($db_r->f("Name"), 0, 70)));
							}
						}
					}
				?>
			</select>
			<br/>
			<input type="image" <?=makeButton('anzeigen','src')?>>
			<?
			} else {
				$db_r = new DB_Seminar();
				$db_r->query("SELECT Institut_id, Name FROM Institute WHERE Institut_id='$subview_id'");
				$db_r->next_record();
				echo _("Einrichtung") . ':&nbsp;<i>' . $db_r->f('Name') . '</i>';
			?>
				<a href="<?=$PHP_SELF?>?view=Daten&subview=AddPersonToRole&username=<?=$username?>">
					<img src="<?=$GLOBALS['ASSETS_URL']?>/images/rewind.gif" border="0">
				</a>
				<br/><br/>
				<?=_("Funktion auswählen")?>:<br/>
				<select name="role_id">
				<?
				Statusgruppe::displayOptionsForRoles($groups);
				//displayChildsSelectBox($groups);
				?>
				</select>
				<br/>
				<input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
				<input type="hidden" name="subview_id" value="<?=$subview_id?>">
				<input type="hidden" name="cmd" value="addToGroup">
				<input type="image" <?=makeButton('zuordnen','src')?>>
			<?
			}
			if ($subview_id && !$groups) {
				echo '<br/><font color="red">' . _("In dieser Einrichtung gibt es keine Gruppen!") . '</font>';
			}
			?>
			<input type="hidden" name="view" value="Karriere">
			<input type="hidden" name="subview" value="addPersonToRole">
			<input type="hidden" name="studipticket" value="<?=get_ticket()?>">
			<input type="hidden" name="username" value="<?=$username?>">
		</form>
	</div> 
	</td>
</tr>

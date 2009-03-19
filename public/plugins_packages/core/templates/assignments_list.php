<table border="0" width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top">			
			<div class="topic"><b>Rollenzuweisungen anzeigen</b></div>
			<table border="0" cellpadding="2" cellspacing="0" width="100%">
				<tr class="steel1">
					<td>
					<form action="<?=$links['showRoleAssignments']?>" method="post">
					<select name="role" style="width: 300px">
						<? foreach($roles as $getrole): ?>
							<option value="<?=$getrole->getRoleid()?>"<? if($getrole->getRoleid()==$roleid):?>selected="selected"<? endif; ?>><?=$getrole->getRolename()?> <? if($getrole->getSystemtype()):?>[Systemrolle]<? endif; ?></option>
						<? endforeach; ?>
					</select>
					<?= makeButton("auswaehlen","input",_("Rolle auswählen"),"selectrole") ?>
					</form>
					</td>
				</tr>
			</table>
			<br/>
			<? if(!empty($role)): ?>
			<div class="topic"><b>Liste der Benutzer mit der Rolle <i><?=$role->getRolename()?></i></b></div>
			<? if (count($users) > 0): ?>
			<table border="0" cellpadding="2" cellspacing="0" width="100%">
				<tr>
					<th width="3%"></th>
					<th align="left">Name</th>
					<th align="left">Benutzername</th>
				</tr>
				<? foreach ($users as $index=>$user): ?>
				<tr class="<?=($index%2==0)?'steel1':'steelgraulight'?>">
					<td align="right"><?=$index+1?>.) </td>
					<td><?=$user['vorname']?> <?=$user['nachname']?></td>
					<td><a href="about.php?username=<?=$user['username']?>"><?=$user['username']?></a></td>
				</tr>
				<? endforeach; ?>
			</table>
			<? else:?>
			<div class="steel1" style="padding:5px;"><?=_("Es wurden keine Benutzer gefunden.") ?></div>
			<? endif; ?>
			<br/>
			<div class="topic"><b>Liste der Plugins mit der Rolle <i><?=$role->getRolename()?></i></b></div>
			<? if (count($plugins) > 0): ?>
			<table border="0" cellpadding="2" cellspacing="0" width="100%">
				<tr>
					<th width="3%"></th>
					<th align="left">Name</th>
					<th align="left">Typ</th>
				</tr>
				<? foreach ($plugins as $index=>$plugin): ?>
				<tr class="<?=($index%2==0)?'steel1':'steelgraulight'?>">
					<td align="right"><?=$index+1?>.) </td>
					<td><?=$plugin['pluginname']?></td>
					<td><?=$plugin['plugintype']?></td>
				</tr>
				<? endforeach; ?>
			</table>
			<? else:?>
			<div class="steel1" style="padding:5px;"><?=_("Es wurden keine Plugins gefunden.") ?></div>
			<? endif; ?>
			<? endif; ?>
		</td>
		<td width="270" align="right" valign="top">
		<!-- Hinweisbox -->
		<?
			//Infobox
			$infobox = array(
				array(
					'kategorie' => _('Hinweise').':',
					'eintrag' => array(
						array(
							'icon' => 'ausruf_small.gif',
							'text' => _('Hier werden alle Benutzer und Plugins angezeigt, die der ausgewählten Rolle zugewiesen sind.')
						),
						array(
							'icon' => 'ausruf_small.gif',
							'text' => _('Klicken Sie auf den Benutzernamen, um sich die Homepage des Benutzers anzeigen zulassen.')
						)
					)
				),
				array  ("kategorie"  => _("Aktionen:"),
						"eintrag" => array	(
							array (	"icon" => "link_intern.gif",
											"text"  => '<a href="'.$links['createRole'].'">'._("Rollen verwalten").'</a>'
							),
							array (	"icon" => "link_intern.gif",
											"text"  => '<a href="'.$links['doRoleAssignment'].'">'._("Benutzerzuweisungen bearbeiten").'</a>'
							),
							array (	"icon" => "link_intern.gif",
											"text"  => '<a href="'.$links['doPluginRoleAssignment'].'">'._("Pluginzuweisungen bearbeiten").'</a>'
							),
							array (	"icon" => "link_intern.gif",
											"text"  => '<a href="'.$links['showRoleAssignments'].'">'._("Rollenzuweisungen anzeigen").'</a>'
							),
						)
				)
			);
			print_infobox ($infobox,"modules.jpg");
		?>
		<!-- Hinweisbox Ende -->
		</td>
	</tr>
</table>
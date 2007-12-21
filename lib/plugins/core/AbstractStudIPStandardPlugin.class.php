<?php

/* vim: noexpandtab */
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once("lib/classes/TreeAbstract.class.php");

class AbstractStudIPStandardPlugin extends AbstractStudIPLegacyPlugin{

	// relativer Name des Icons für Änderungen an diesem Plugin
	var $changeindicatoriconname;

	// Id, der dieses Plugin zugeordnet ist (bspw. Veranstaltung oder Institution)
	var $id;

	// wird dieses Plugin in der Übersicht (z.B. meine_seminare) angezeigt
	var $overview;

	function AbstractStudIPStandardPlugin() {
		parent::AbstractStudIPLegacyPlugin();
		$this->pluginiconname = "";
		$this->changeindicatoriconname = "";
		$this->id = UNKNOWN_ID;
		$this->overview = false;
		$this->pluginengine = PluginEngine::getPluginPersistence("Standard");
		// create the standard AdminInfo
		$admininfo = new AdminInfo();
		$this->setPluginAdminInfo($admininfo);
	}

	function setId($newid) {
		$this->id = $newid;
	}

	function getId() {
		if ($this->id === UNKNOWN_ID) {
			$this->id = $GLOBALS['SessSemName'][1];
		} else {
			$this->id = trim(str_replace($GLOBALS["SessSemName"]["class"],
			                             '', $this->id));
	 	}
		return $this->id;
	}

	/**
	 * Hat sich seit dem letzten Login etwas geändert?
	 * @param lastlogin - letzter Loginzeitpunkt des Benutzers
	 */
	function hasChanged($lastlogin) {
		return false;
	}

	/**
	 * Nachricht für tooltip in der Übersicht
	 * @param lastlogin - letzter Loginzeitpunkt des Benutzers
	 */
	function getOverviewMessage($has_changed = false) {
		return $this->getPluginname() . ($has_changed ? ' ' . _("geändert") : '');
	}

	/**
	 * Wird dieses Plugin in der Übersicht angezeigt?
	 */
	function isShownInOverview() {
		return $this->overview;
	}

	/**
	 * Liefert die Änderungsmeldungen für die übergebenen ids zurück
	 * @param lastlogin - letzter Loginzeitpunkt des Benutzers
	 * @param ids - ein Array von Veranstaltungs- bzw. Institutionsids, zu denen
	 * die Änderungsnachricht bestimmt werden soll.
	 * @return Änderungsmeldungen
	 */
	function getChangeMessages($lastlogin, $ids) {
		return array();
	}

	/**
	 * Getter- und Setter für die Attribute
   */
	function getChangeindicatoriconname() {
		return $this->getPluginpath() . "/" . $this->changeindicatoriconname;
	}

	function setChangeindicatoriconname($newicon) {
		$this->changeindicatoriconname = $newicon;
	}

	function setShownInOverview($value = true) {
		$this->overview = $value;
	}


	/**
	 * Shows the standard configuration.
	 */
	function actionShowConfigurationPage() {
		$user = $this->getUser();
		$permission = $user->getPermission();
		if (!$permission->hasAdminPermission()) {
			throw new Studip_AccessDeniedException(_("Sie besitzen keine Berechtigung, um dieses Plugin zu konfigurieren."));
		}
		else {
			StudIPTemplateEngine::makeContentHeadline(_("Default-Aktivierung"));
			$sel_institutes = $_POST["sel_institutes"];
			if ($_GET["selected"]){
				if ($_POST["nodefault"] == true) {
					if ($this->pluginengine->removeDefaultActivations($this)) {
						StudIPTemplateEngine::showSuccessMessage(_("Die Voreinstellungen wurden erfolgreich gelöscht."));
						$sel_institutes = array();
					}
					else {
						StudIPTemplateEngine::showErrorMessage(_("Die Voreinstellungen konnten nicht gelöscht werden"));
					}
				}
				else {
					// save selected institutes
					if ($this->pluginengine->saveDefaultActivations($this, $sel_institutes)) {
						// show info
						if (count($sel_institutes) > 1) {
							StudIPTemplateEngine::showSuccessMessage(_("Für die ausgewählten Institute wurde das Plugin standardmäßig aktiviert!"));
						}
						else {
							StudIPTemplateEngine::showSuccessMessage(_("Für das ausgewählte Institut wurde das Plugin standardmäßig aktiviert!"));
						}
					}
					else {
						StudIPTemplateEngine::showErrorMessage(_("Das Abspeichern der Default-Einstellungen ist fehlgeschlagen"));
					}
				}
			}
			else {
				// load old config
				$sel_institutes = $this->pluginengine->getDefaultActivations($this);
			}

			?>
			<tr>
				<td>
					<?
					echo _("Wählen Sie die Einrichtungen, in deren Veranstaltungen das Plugin automatisch aktiviert sein soll.<p>");
					$institutes = StudIPCore::getInstitutes();
					?>
					<form action="<?= PluginEngine::getLink($this, array("selected" => true), "showConfigurationPage") ?>" method="POST">
					<select name="sel_institutes[]" multiple size="20">
					<?

					foreach ($institutes as $institute) {
						// if id is in selected institutes, the mark it as selected

						if (array_search($institute->getId(),  $sel_institutes) !== false){
							$selected = "selected";
						}
						else {
							$selected = "";
						}
						echo(sprintf("<option value=\"%s\" %s> %s </option>", $institute->getId(), $selected, $institute->getName()));
						$childs = $institute->getAllChildInstitutes();
						foreach ($childs as $child) {
							if (array_search($child->getId(), $sel_institutes) !== false) {
								$selected = "selected";
							}
							else {
								$selected = "";
							}
							echo(sprintf("<option value=\"%s\" %s>&nbsp;&nbsp;&nbsp;&nbsp; %s </option>",$child->getId(),$selected, $child->getName()));
						}
					}

					?>
					</select><br>
					<input type="checkbox" name="nodefault"><?= _("keine Voreinstellung wählen") ?>
					<p>

					<?= makeButton("uebernehmen", "input", _("Einstellungen speichern")) ?>
					<a href="<?= PluginEngine::getLinkToAdministrationPlugin() ?>"><?= makeButton("zurueck", "img",  _("Zurück zur Plugin-Verwaltung")) ?></a>
					</form>
				</td>
			</tr>

			<?php

			StudIPTemplateEngine::createInfoBoxTableCell();
			$infobox = array(array(
				"kategorie" => _("Hinweise:"),
				"eintrag" => array(array("icon" => "ausruf_small.gif",
				                         "text" => _("Wählen Sie die Institute, in deren Veranstaltungen das Plugin standardmäßig eingeschaltet werden soll.")),
				                   array("icon" => "ausruf_small.gif",
				                         "text" => _("Eine Mehrfachauswahl ist durch Drücken der Strg-Taste möglich.")))));
			print_infobox($infobox);
			StudIPTemplateEngine::endInfoBoxTableCell();
		}
	}

	/**
	 * returns the score which the current user get's for activities in this plugin
	 *
	 */
	function getScore()  {
		return 0;
	}


	/**
	 * This abstract method sets everything up to perform the given action and
	 * displays the results or anything you want to.
	 *
	 * @param  string the name of the action to accomplish
	 *
	 * @return void
	 */
	function display_action($action) {
		$GLOBALS['CURRENT_PAGE'] =
			$GLOBALS['SessSemName']['header_line'] . ' - ' . $this->getDisplayTitle();

		include 'lib/include/html_head.inc.php';
		include 'lib/include/header.php';

		$pluginparams = $_GET["plugin_subnavi_params"];

		include 'lib/include/links_openobject.inc.php';

		// let the plugin show its view
		StudIPTemplateEngine::startContentTable(true);
		$this->$action($pluginparams);
		StudIPTemplateEngine::endContentTable();

		// close the page
		include 'lib/include/html_end.inc.php';
		page_close();
	}
}

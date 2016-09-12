<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* admin_extern.inc.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       extern
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_extern.inc.php
//
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
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

use Studip\Button, Studip\LinkButton;

include('lib/seminar_open.php'); // initialise Stud.IP-Session


// -- here you have to put initialisations for the current page

if (Request::option('view') == 'extern_global') {
    $range_id = 'studip';
    URLHelper::addLinkParam('view', 'extern_global');
} else {
    $range_id = $SessSemName[1] ? $SessSemName[1] : '';
    URLHelper::addLinkParam('view', 'extern_inst');
}
URLHelper::addLinkParam('cid', $range_id);
$config_id = Request::option('config_id');
// when downloading a config, do it here and stop afterwards
if (Request::get('com') == 'download_config') {
    if ($range_id) {
        download_config($range_id, $config_id, Request::quoted('module'));
        page_close();
        exit;
    }
}

PageLayout::setTitle(_("Verwaltung externer Seiten"));

if ($range_id != 'studip') {
    Navigation::activateItem('/admin/institute/external');
    require_once 'lib/admin_search.inc.php';
} else {
    Navigation::activateItem('/admin/locations/external');
}
$mod=Request::quoted('mod');//Change header_line if open object
$header_line = getHeaderLine($range_id);
if ($header_line) {
    PageLayout::setTitle($header_line." - ".PageLayout::getTitle());
    foreach ($GLOBALS['EXTERN_MODULE_TYPES'] as $key => $type) {
        if ($type["module"] == $mod) {
            PageLayout::setTitle(PageLayout::getTitle() . " ({$GLOBALS['EXTERN_MODULE_TYPES'][$key]['name']})");
            break;
        }
    }
}

// upload of configuration
if (Request::option('com') == "do_upload_config") {
    $file_content = file_get_contents($_FILES['the_file']['tmp_name']);

    // revert the changes done by indentJson
    $file_content_wo_tabs = str_replace("\t", '', str_replace("\n", '', $file_content));

    $jsonconfig = json_decode($file_content_wo_tabs, true);

    // utf8-decode the values after json_decode has worked on it
    array_walk_recursive($jsonconfig, 'utf8Decode');

    if (!check_config($jsonconfig, Request::quoted('check_module'))) {
        PageLayout::postError(_('Die Konfigurationsdatei hat den falschen Modultyp!'));
    } else if (!store_config($range_id, $config_id, $jsonconfig)) {
        PageLayout::postError(_('Die Konfigurationsdatei konnte nicht hochgeladen werden!'));
    } else {
        PageLayout::postSuccess(_('Die Datei wurde erfolgreich übertragen!'));
    }
}

//Output starts here

ob_start();

// copy existing configuration
if (Request::option('com') == 'copyconfig') {
    if (Request::option('copyinstid') && Request::option('copyconfigid')) {
        $config = ExternConfig::GetInstance(Request::option('copyinstid'), '', Request::option('copyconfigid'));
        $config_copy = $config->copy($range_id);
        echo MessageBox::success(sprintf(_("Die Konfiguration wurde als \"%s\" nach Modul \"%s\" kopiert."),
                htmlReady($config_copy->getConfigName()),
                htmlReady($GLOBALS['EXTERN_MODULE_TYPES'][$config_copy->getTypeName()]['name'])));
    } else {
        Request::set('com','');
    }
}

if (Request::option('com') == 'delete') {
    $config = ExternConfig::GetInstance($range_id, '', $config_id);
    if ($config->deleteConfiguration()) {
        echo MessageBox::success(sprintf(_("Konfiguration <strong>\"%s\"</strong> für Modul <strong>\"%s\"</strong> gelöscht!"),
                htmlReady($config->getConfigName()),
                htmlReady($GLOBALS['EXTERN_MODULE_TYPES'][$config->getTypeName()]['name'])));
    } else {
        echo MessageBox::erro(_("Konfiguration konnte nicht gelöscht werden"));
    }
}



if (Request::option('com') == 'delete_sec') {
    $config = ExternConfig::GetConfigurationMetaData($range_id, $config_id);

    $message = sprintf(_("Wollen Sie die Konfiguration <b>&quot;%s&quot;</b> des Moduls <b>%s</b> wirklich löschen?"), $config["name"], $GLOBALS["EXTERN_MODULE_TYPES"][$config["type"]]["name"]);
    $message .= '<br><br>';
    $message .= LinkButton::createAccept("JA", URLHelper::getURL('?com=delete&config_id='.$config_id));
    $message .= LinkButton::createCancel("NEIN", URLHelper::getURL('?list=TRUE&view=extern_inst'));

    echo MessageBox::info($message);

    $template = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();
    echo $template->render();
    page_close();
    die;
}

if (Request::option('com') == 'info') {
    include $RELATIVE_PATH_EXTERN . '/views/extern_info_module.inc.php';

    $template = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();
    echo $template->render();
    page_close();
    die;
}

if (Request::option('com') == 'new' || Request::option('com') == 'edit' || Request::option('com') == 'open' ||
        Request::option('com') == 'close' || Request::option('com') == 'store') {

    require_once($RELATIVE_PATH_EXTERN . "/views/extern_edit_module.inc.php");
    

    $template = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();
    echo $template->render();
    page_close();
    die;
}

// Some browsers don't reload the site by clicking the same link twice again.
// So it's better to use different commands to do the same job.
if (Request::option('com') == 'set_default' || Request::option('com') == 'unset_default') {
    if (!ExternConfig::SetStandardConfiguration($range_id, $config_id)) {
        page_close();
        exit;
    }
}

if ($EXTERN_SRI_ENABLE_BY_ROOT && Request::option('com') == 'enable_sri'
        && $perm->have_perm('root')) {
    enable_sri($range_id, Request::quoted('sri_enable'));
}


if ($EXTERN_SRI_ENABLE_BY_ROOT && $perm->have_perm('root')) {
    echo '<form method="post" action="' . URLHelper::getLink('?com=enable_sri') . '">';
    echo CSRFProtection::tokenTag();
    echo '<blockquote>';
    echo _("SRI-Schnittstelle freigeben");
    echo ' <input type="checkbox" name="sri_enable" value="1"';
    if (sri_is_enabled($range_id)) {
        echo ' checked="checked"';
    }
    echo '>';

    echo Button::createAccept();

    echo "</blockquote></form>";
}

$configurations = ExternConfig::GetAllConfigurations($range_id);
$module_types_ordered = ExternModule::GetOrderedModuleTypes();

$choose_module_form = '';
// remove global configuration
array_shift($module_types_ordered);
foreach ($module_types_ordered as $i) {
    if ((sizeof($configurations[$GLOBALS['EXTERN_MODULE_TYPES'][$i]['module']]) < $EXTERN_MAX_CONFIGURATIONS)
        && ExternModule::HaveAccessModuleType(Request::option('view'), $i)) {
        $choose_module_form .= "<option value=\"{$GLOBALS['EXTERN_MODULE_TYPES'][$i]['module']}\">"
                . $GLOBALS['EXTERN_MODULE_TYPES'][$i]['name'] . "</option>\n";
    }
    if (isset($configurations[$GLOBALS['EXTERN_MODULE_TYPES'][$i]["module"]])) {
        $have_config = TRUE;
    }
}
// add global configuration on first position
array_unshift($module_types_ordered, 0);
// check for global configurations
if (isset($configurations[$GLOBALS['EXTERN_MODULE_TYPES'][0]["module"]])) {
    $have_config = TRUE;
}

if (Request::option('com') != 'copychoose') {
    echo "<blockquote>";
    echo _("Neue globale Konfiguration anlegen.") . " ";
    echo LinkButton::create(_("Neu anlegen"), URLHelper::getURL('?com=new&mod=Global'));
    echo "</blockquote>";
}

if ($choose_module_form != '') {
    if (Request::option('com') != 'copychoose') {
        echo '<form method="post" action="' . URLHelper::getLink('?com=new') . '">';
        echo CSRFProtection::tokenTag();
        echo "<blockquote>";
        $choose_module_form = "<select name=\"mod\">\n$choose_module_form</select>\n";
        printf(_("Neue Konfiguration für Modul %s anlegen.") . " ", $choose_module_form);
        echo Button::create(_("Neu anlegen"));
        echo "</blockquote>\n";
        echo "</form>\n";

        $conf_institutes = ExternConfig::GetInstitutesWithConfigurations(($GLOBALS['perm']->have_perm('root') && Request::option('view') == 'extern_global') ? 'global' : array('inst', 'fak'));
        if (sizeof($conf_institutes)) {
            echo '<form method="post" action="' . URLHelper::getLink('?com=copychoose') . '">';
            echo CSRFProtection::tokenTag();
            echo "<blockquote>";
            $choose_institute_copy = "<select name=\"copychooseinst\" class=\"nested-select\">\n";
            foreach ($conf_institutes as $conf_institute) {
                $choose_institute_copy .= sprintf("<option value=\"%s\" class=\"%s\">%s</option>\n", $conf_institute['institut_id'], ($conf_institute['fakultaets_id'] == $conf_institute['institut_id'] ? 'nested-item-header' : 'nested-item'), htmlReady(strlen($conf_institute['name']) > 60 ? substr_replace($conf_institute['name'], '[...]', 30, -30) : $conf_institute['name']));
            }
            $choose_institute_copy .= "</select>\n";
            printf(_("Konfiguration aus Einrichtung %s kopieren."), $choose_institute_copy);
            echo Button::create(_("Weiter") . " >>");
            echo "</blockquote>\n";
            echo "</form>\n";
        }
    } else {
        if (Request::option('com') == 'copychoose') {
            $choose_module_select = "<select name=\"copyconfigid\" class=\"nested-select\">\n";
            $configurations_copy = ExternConfig::GetAllConfigurations(Request::option('copychooseinst'));
            foreach ($module_types_ordered as $module_type) {
                $print_module_name = TRUE;

                if (is_array($configurations_copy[$GLOBALS['EXTERN_MODULE_TYPES'][$module_type]['module']])) {
                    foreach ($configurations_copy[$GLOBALS['EXTERN_MODULE_TYPES'][$module_type]['module']] as $config_id_copy => $config_data_copy) {
                        if ($print_module_name) {
                            $choose_module_select .= '<option value="" class="nested-item-header">' . htmlReady($GLOBALS['EXTERN_MODULE_TYPES'][$module_type]['name']) . '</option>';
                        }
                        $choose_module_select .= '<option value="' . $config_id_copy . '" class="nested-item">&' . htmlReady($config_data_copy['name']) . '</option>';
                        $print_module_name = FALSE;
                    }
                }
            }

            echo '<form method="post" action="' . URLHelper::getLink('?com=copyconfig') . '">';
            echo CSRFProtection::tokenTag();
            echo "<blockquote>";
            printf(_("Konfiguration %s aus Einrichtung kopieren."), $choose_module_select . '</select>');
            echo Button::create(_("Kopieren"));
            echo LinkButton::create("<< " . _("Zurück"), URLHelper::getURL('?list=TRUE&view=extern_inst'));
            echo "</blockquote>\n";
            echo "<input type=\"hidden\" name=\"copyinstid\" value=\"" . htmlReady(Request::quoted('copychooseinst')) . "\">\n";
            echo "</form>\n";

        }
    }
}
else {
    echo "<blockquote>";
    echo _("Sie haben bereits für alle Module die maximale Anzahl von Konfigurationen angelegt. Um eine neue Konfiguration anzulegen, müssen Sie erst eine bestehende im gewünschten Modul löschen.");
    echo "</blockquote>\n";
}


if (!$have_config) {
    echo "<blockquote>\n";
    echo _("Es wurden noch keine Konfigurationen angelegt.");
    echo "</blockquote>";
} else {
    echo "<table class=\"default\">\n";
    echo "<caption>\n";
    echo _("Angelegte Konfigurationen");
    echo "</caption>\n";
    
    foreach ($module_types_ordered as $order) {
        $module_type = $GLOBALS['EXTERN_MODULE_TYPES'][$order];
        if (isset($configurations[$module_type["module"]])) {
          
            echo "<thead>\n";
            echo "<tr>\n<th colspan=\"2\">";
            
            if (isset($configurations[$module_type["module"]][$config_id])) {
                echo "<a name=\"anker\"></a>\n";
            }
            echo $module_type["name"];

            echo "</th></tr>\n</thead>\n";
            echo "<tbody>\n";
            

            foreach ($configurations[$module_type["module"]] as $configuration) {
                echo "<tr><td style=\"width: 65%\">";
                echo $configuration["name"] . "</td>\n";
                $actionMenu = ActionMenu::get();
                $actionMenu->addLink(
                        URLHelper::getLink('?com=download_config&config_id='. $configuration['id'] .'&module='. $module_type['module']),
                        _('Konfigurationsdatei herunterladen'),
                        Icon::create('download', 'clickable', ['title' => _('Konfigurationsdatei herunterladen')]));
                
                $actionMenu->addLink(
                        URLHelper::getLink('?com=upload_config&config_id='. $configuration['id']),
                        _('Konfigurationsdatei hochladen'),
                        Icon::create('upload', 'clickable', ['title' => _('Konfigurationsdatei hochladen')]));
                $actionMenu->addLink(
                        URLHelper::getLink('?com=info&config_id=' . $configuration['id']),
                        _('weitere Informationen anzeigen'),
                        Icon::create('infopage', 'clickable', ['title' => _('weitere Informationen anzeigen')]));


                // Switching for the is_default option. Read the comment above.
                if ($configuration["is_default"]) {
                    $actionMenu->addLink(
                            URLHelper::getLink('?com=unset_default&config_id=' . $configuration['id']) . '#anker',
                            _('Standard entziehen'),
                            Icon::create('checkbox-checked', 'clickable', ['title' => _('Standard entziehen')]));
                } else {
                    $actionMenu->addLink(
                            URLHelper::getLink('?com=set_default&config_id=' . $configuration['id']) . '#anker',
                            _('Standard zuweisen'),
                            Icon::create('checkbox-checked', 'clickable', ['title' => _('Standard zuweisen')]));
                }

                $actionMenu->addLink(
                        URLHelper::getLink('?com=delete_sec&config_id=' . $configuration['id']) . '#anker',
                        _('Konfiguration löschen'),
                        Icon::create('trash', 'clickable', ['title' => _('Konfiguration löschen')]));
                $actionMenu->addLink(
                        URLHelper::getURL('?com=edit&mod=' . $module_type['module'] . '&config_id=' . $configuration['id']),
                        _('Konfiguration bearbeiten'),
                        Icon::create('edit', 'clickable', ['title' => _('Konfiguration bearbeiten')]));
                ?>
                <?
                echo "<td class=\"actions\" style=\"width: 20%\" ";
                echo ">\n";
                echo $actionMenu->render();
                echo "</td></tr>\n";

                if (Request::option('com') == 'upload_config' && Request::option('config_id') == $configuration['id']) {
                    $template = $GLOBALS['template_factory']->open('extern/upload_form');
                    $template->set_attribute('module', $module_type['module']);
                    $template->set_attribute('config_id', $configuration['id']);
                    $template->set_attribute('max_filesize', 1024 * 100); // currently 100kb

                    echo $template->render();
                }
            }
        }

    }
}
echo "</table>\n";

$info_max_configs = sprintf(_("Sie können pro Modul maximal %s Konfigurationen anlegen."),
        $EXTERN_MAX_CONFIGURATIONS);

Helpbar::get()->addPlainText(_('Information'), sprintf(_("Sie können pro Modul maximal %s Konfigurationen anlegen."),
        $EXTERN_MAX_CONFIGURATIONS));

if (sizeof($configurations)) {

    Helpbar::get()->addPlainText(_('Standard-Konfiguration'),
            _('Dieses Symbol kennzeichnet die Standard-Konfiguration, die zur Formatierung herangezogen wird, wenn Sie beim Aufruf dieses Moduls keine Konfiguration angeben.'),
            Icon::create('checkbox-checked'));
    Helpbar::get()->addPlainText(_('Keine Standard-Konfiguration'),
            _('Wenn Sie keine Konfiguration als Standard ausgewählt haben, wird die Stud.IP-Konfiguration verwendet.'),
            Icon::create('info'));
    Helpbar::get()->addPlainText(_('Standard-Konfiguration zuweisen'),
            _('Klicken Sie auf diesen Button, um eine Konfiguration zur Standard-Konfiguration zu erklären.'),
            Icon::create('checkbox-unchecked'));
    Helpbar::get()->addPlainText(_('Weitere Informationen'),
            _('Klicken Sie auf diesen Button um weitere Informationen über diese Konfiguration zu erhalten. Hier finden Sie auch die Links, über die Sie die Module in Ihrer Website einbinden können.'),
            Icon::create('infopage'));
    
}


//print_footer();

$template = $GLOBALS['template_factory']->open('layouts/base.php');
$template->content_for_layout = ob_get_clean();
echo $template->render();
page_close();

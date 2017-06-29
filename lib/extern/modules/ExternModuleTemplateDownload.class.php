<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleTemplateDownload.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleTemplateDownload
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleTemplateDownload.class.php
//
// Copyright (C) 2007 Peter Thienel <thienel@data-quest.de>,
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


require_once 'lib/extern/views/extern_html_templates.inc.php';
require_once 'lib/user_visible.inc.php';
require_once 'lib/statusgruppe.inc.php';


class ExternModuleTemplateDownload extends ExternModule {

    var $markers = array();
    var $args = array('seminar_id');

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {

        $this->data_fields = array("icon", "filename", "description", "mkdate",
                             "filesize", "fullname");
        $this->registered_elements = array(
                'LinkInternTemplate', 'TemplateGeneric'
        );

        $this->field_names = array (
                _("Icon"),
                _("Dateiname"),
                _("Beschreibung"),
                _("Datum"),
                _("Größe"),
                _("Upload durch")
        );

        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }

    function setup () {
        // extend $data_fields if generic datafields are set
    //  $config_datafields = $this->config->getValue("Main", "genericdatafields");
    //  $this->data_fields = array_merge((array)$this->data_fields, (array)$config_datafields);

        // setup module properties
    //  $this->elements["LinkIntern"]->link_module_type = 2;
    //  $this->elements["LinkIntern"]->real_name = _("Link zum Modul MitarbeiterInnendetails");

        $this->elements['TemplateGeneric']->real_name = _("Template");
        $this->elements['LinkInternTemplate']->link_module_type = array(2, 14);
        $this->elements['LinkInternTemplate']->real_name = _("Link zum Modul MitarbeiterInnendetails");

    }

    function toStringEdit ($open_elements = '', $post_vars = '',
            $faulty_values = '', $anker = '') {

        $this->updateGenericDatafields('TemplateGeneric', 'user');
        $this->elements['TemplateGeneric']->markers = $this->getMarkerDescription('TemplateGeneric');

        return parent::toStringEdit($open_elements, $post_vars, $faulty_values, $anker);
    }

    function getMarkerDescription ($element_name) {
        $markers['TemplateGeneric'][] = array('__GLOBAL__', _("Globale Variablen (gültig im gesamten Template)."));
        $markers['TemplateGeneric'][] = array('###FILES-COUNT###', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN DOWNLOAD -->', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN NO-FILES -->', '');
        $markers['TemplateGeneric'][] = array('###NO-FILES-TEXT###', '');
        $markers['TemplateGeneric'][] = array('<!-- END NO-FILES -->', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN FILES -->', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN FILE -->', '');
        $markers['TemplateGeneric'][] = array('###FILE_NAME###', '');
        $markers['TemplateGeneric'][] = array('###FILE_FILE-NAME###', '');
        $markers['TemplateGeneric'][] = array('###FILE_SIZE###', '');
        $markers['TemplateGeneric'][] = array('###FILE_NO###', '');
        $markers['TemplateGeneric'][] = array('###FILE_DESCRIPTION###', '');
        $markers['TemplateGeneric'][] = array('###FILE_UPLOAD-DATE###', '');
        $markers['TemplateGeneric'][] = array('###FULLNAME###', '');
        $markers['TemplateGeneric'][] = array('###LASTNAME###', '');
        $markers['TemplateGeneric'][] = array('###FIRSTNAME###', '');
        $markers['TemplateGeneric'][] = array('###TITLEFRONT###', '');
        $markers['TemplateGeneric'][] = array('###TITLEREAR###', '');
        $markers['TemplateGeneric'][] = array('###PERSONDETAIL-HREF###', '');
        $markers['TemplateGeneric'][] = array('###USERNAME###', '');
        $this->insertDatafieldMarkers('user', $markers, 'TemplateGeneric');
        $markers['TemplateGeneric'][] = array('###FILE_HREF###', '');
        $markers['TemplateGeneric'][] = array('###FILE_ICON-HREF###', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN PERSONDETAIL-LINK -->');
        $markers['TemplateGeneric'][] = array('###LINK_FULLNAME###', '');
        $markers['TemplateGeneric'][] = array('###LINK_LASTNAME###', '');
        $markers['TemplateGeneric'][] = array('###LINK_FIRSTNAME###', '');
        $markers['TemplateGeneric'][] = array('###LINK_TITLEFRONT###', '');
        $markers['TemplateGeneric'][] = array('###LINK_TITLEREAR###', '');
        $markers['TemplateGeneric'][] = array('###LINK_PERSONDETAIL-HREF###', '');
        $markers['TemplateGeneric'][] = array('<!-- END PERSONDETAIL-LINK -->');
        $markers['TemplateGeneric'][] = array('<!-- END FILE -->');
        $markers['TemplateGeneric'][] = array('<!-- END FILES -->', '');
        $markers['TemplateGeneric'][] = array('<!-- END DOWNLOAD -->', '');

        return $markers[$element_name];
    }

    function getContent ($args = NULL, $raw = FALSE) {
        $error_message = "";
        if (!$args) {
            $args = array();
        }
        $content = array();

        // check for valid range_id
        if(!$this->checkRangeId($this->config->range_id)) {
            $error_message = $GLOBALS['EXTERN_ERROR_MESSAGE'];
        }
        // if $args['seminar_id'] is given, check for free access
        if ($args['seminar_id']) {
            $seminar_id = $args['seminar_id'];
            $query = "SELECT Lesezugriff FROM seminare s LEFT JOIN seminar_inst si ";
            $query .= "USING(seminar_id) WHERE s.seminar_id = ? ";
            $query .= "AND si.institut_id = ?";
            $params = array($seminar_id, $this->config->range_id);
            $statement = DBManager::get()->prepare($query);
            $statement->execute($params);
            $row = $statement->fetchColumn();
            if ($row !== false && $row == 0 ) {
                 $error_message = $GLOBALS['EXTERN_ERROR_MESSAGE'];
            }
        } else {
            $seminar_id = $this->config->range_id;
        }

        $sort = (array) $this->config->getValue('Main', 'sort');
        $query_order = '';
        foreach ($sort as $key => $position) {
            if ($position > 0) {
                $query_order[$position] = $this->data_fields[$key];
            }
        }
        if ($query_order) {
            ksort($query_order, SORT_NUMERIC);
            $query_order = ' ORDER BY ' . implode(',', $query_order) . ' DESC';
        }

        if (!$nameformat = $this->config->getValue('Main', 'nameformat')) {
            $nameformat = 'no_title_short';
        }

        // generic data fields
        $generic_datafields = $this->config->getValue('TemplateGeneric', 'genericdatafields');

        $downloadable_file_refs = [];
        
        $top_folder = Folder::findTopFolder($seminar_id);
        $top_folder = $top_folder->getTypedFolder();
        
        $subfolders = FileManager::getFolderFilesRecursive($top_folder, 'nobody')['folders'];
        
        foreach($top_folder->getFiles() as $file_ref) {
            if($top_folder->isFileDownloadable($file_ref->id, 'nobody')) {
                $downloadable_file_refs[] = $file_ref;
            }
        }
        
        foreach($subfolders as $subfolder) {
            foreach($subfolder->getFiles() as $file_ref) {
                if($subfolder->isFileDownloadable($file_ref->id, 'nobody')) {
                    $downloadable_file_refs[] = $file_ref;
                }
            }
        }
        
        
        
        /*
        $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $seminar_id));

        $allowed_folders = $folder_tree->getReadableFolders('nobody');
        $mrks =  str_repeat('?,', count($allowed_folders) - 1) . '?';
        $query = "SELECT dokument_id, name, description, filename, d.mkdate, d.chdate, filesize, ";
        $query .= $GLOBALS['_fullname_sql'][$nameformat];
        $query .= "AS fullname, Vorname, Nachname, title_front, title_rear, username, aum.user_id, author_name FROM dokumente d LEFT JOIN user_info USING (user_id) ";
        $query .= "LEFT JOIN auth_user_md5 aum USING (user_id) WHERE ";
        $query .= "seminar_id = ? AND range_id IN ($mrks)$query_order";

        $parameters = $allowed_folders;
        $parameters[] = $seminar_id;          
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        */
        
        
        if (empty($downloadable_file_refs)) {
            $content['NO-FILES']['NO-FILES-TEXT'] = $this->config->getValue('Main', 'nodatatext');
        } else {
            $i = 0;
            foreach($downloadable_file_refs as $downloadable_file_ref) {
                
                $content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = Icon::create(
                    FileManager::getIconNameForMimeType($downloadable_file_ref->file->mime_type),
                    'clickable'
                    )->asImagePath(16);
                
                /*
                preg_match("/^.+\.([a-z1-9_-]+)$/i", $row['filename'], $file_suffix);

                $icon = '';
                switch ($file_suffix[1]) {
                    case 'txt' :
                        if (!$content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = $this->config->getValue('Main', 'icontxt'))
                            $content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = Icon::create('file-text', 'clickable')->asImagePath(16);
                        break;
                    case 'xls' :
                        if (!$content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = $this->config->getValue('Main', 'iconxls'))
                            $content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = Icon::create('file-xls', 'clickable')->asImagePath(16);
                        break;
                    case 'ppt' :
                        if (!$content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = $this->config->getValue('Main', 'iconppt'))
                            $content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = Icon::create('file-presentation', 'clickable')->asImagePath(16);
                        break;
                    case 'rtf' :
                        if (!$content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = $this->config->getValue('Main', 'iconrtf'))
                            $content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = Icon::create('file-text', 'clickable')->asImagePath(16);
                        break;
                    case 'zip' :
                    case 'tgz' :
                    case 'gz' :
                        if (!$content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = $this->config->getValue('Main', 'iconzip'))
                            $content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = Icon::create('file-archive', 'clickable')->asImagePath(16);
                        break;
                    case 'jpg' :
                    case 'png' :
                    case 'gif' :
                    case 'jpeg' :
                    case 'tif' :
                        if (!$content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = $this->config->getValue('Main', 'iconpic'))
                            $content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = Icon::create('file-pic', 'clickable')->asImagePath(16);
                        break;
                    case 'pdf' :
                        if (!$content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = $this->config->getValue('Main', 'iconpdf'))
                            $content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = Icon::create('file-pdf', 'clickable')->asImagePath(16);
                        break;
                    default :
                        if (!$content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = $this->config->getValue('Main', 'icondefault'))
                            $content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = Icon::create('file-generic', 'clickable')->asImagePath(16);
                }
                */
                
                $content['FILES']['FILE'][$i]['FILE_NO'] = $i + 1;

                $download_link = $downloadable_file_ref->getDownloadURL();
                //$download_link = GetDownloadLink($row['dokument_id'], $row['filename']);

                $content['FILES']['FILE'][$i]['FILE_HREF'] = $download_link;
                $content['FILES']['FILE'][$i]['FILE_NAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->name);
                $content['FILES']['FILE'][$i]['FILE_FILE-NAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->filename);
                $content['FILES']['FILE'][$i]['FILE_DESCRIPTION'] = ExternModule::ExtHtmlReady(mila_extern($downloadable_file_ref->description,
                                                     $this->config->getValue("Main", "lengthdesc")));
                $content['FILES']['FILE'][$i]['FILE_UPLOAD-DATE'] = strftime($this->config->getValue("Main", "dateformat"), $downloadable_file_ref->mkdate);
                $content['FILES']['FILE'][$i]['FILE_SIZE'] = $downloadable_file_ref->file->size > 1048576 ? round($downloadable_file_ref->file->size / 1048576, 1) . " MB" : round($downloadable_file_ref->file->size / 1024, 1) . " kB";

                $content['FILES']['FILE'][$i]['USERNAME'] = $downloadable_file_ref->user->username;
                $content['FILES']['FILE'][$i]['FULLNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->owner->getFullName());
                $content['FILES']['FILE'][$i]['FIRSTNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->owner->vorname);
                $content['FILES']['FILE'][$i]['LASTNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->owner->nachname);
                $content['FILES']['FILE'][$i]['TITLEFRONT'] = ExternModule::ExtHtmlReady($downloadable_file_ref->owner->title_front);
                $content['FILES']['FILE'][$i]['TITLEREAR'] = ExternModule::ExtHtmlReady($downloadable_file_ref->owner->title_rear);
                $content['FILES']['FILE'][$i]['PERSONDETAIL-HREF'] = $this->elements['LinkInternTemplate']->createUrl('Persondetails', array('link_args' => 'username=' . $downloadable_file_ref->owner->username));

                // if user is member of a group then link name to details page
                $link_persondetail = '';
                if (GetRoleNames(GetAllStatusgruppen($this->config->range_id, $downloadable_file_ref->user_id))) {
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_PERSONDETAIL-HREF'] = $this->elements['LinkInternTemplate']->createUrl('Persondetails', array('link_args' => 'username=' . $downloadable_file_ref->owner->username));
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_FULLNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->owner->getFullName());
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_FIRSTNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->owner->vorname);
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_LASTNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->owner->nachname);
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_TITLEFRONT'] = ExternModule::ExtHtmlReady($downloadable_file_ref->owner->title_front);
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_TITLEREAR'] = ExternModule::ExtHtmlReady($downloadable_file_ref->owner->title_rear);
                }

                // generic data fields
                if (is_array($generic_datafields)) {
                    $localEntries = DataFieldEntry::getDataFieldEntries($downloadable_file_ref->owner->user_id, 'user');
                    $k = 1;
                    foreach ($generic_datafields as $datafield) {
                        if (isset($localEntries[$datafield]) && is_object($localEntries[$datafield])) {
                            $localEntry = $localEntries[$datafield]->getDisplayValue();
                            if ($localEntry) {
                                $content['FILES']['FILE'][$i]['DATAFIELD_' . $k] = $localEntry;
                            }
                        }
                        $k++;
                    }
                }

                $i++;
            //}while($row = $statement->fetch(PDO::FETCH_ASSOC));
            }
        }
        $content = array('DOWNLOAD' => $content);
        $content['__GLOBAL__']['FILES-COUNT'] = $i;

        return $content;
    }

    function printout ($args) {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->elements['TemplateGeneric']->toString(array('content' => $this->getContent($args), 'subpart' => 'DOWNLOAD'));

    }

    function printoutPreview () {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->elements['TemplateGeneric']->toString(array('content' => $this->getContent($args), 'subpart' => 'DOWNLOAD', 'hide_markers' => FALSE));

    }

}

?>

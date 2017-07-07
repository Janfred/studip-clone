<?php
/**
 * files.php - controller to display files in a course
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.0
 */
class Course_FilesController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        checkObject();
        checkObjectModule('documents');
        object_set_visit_module('documents');

        $this->course = Course::findCurrent();
        $this->last_visitdate = object_get_visit($this->course->id, 'documents');

        PageLayout::addSqueezePackage('tablesorterfork');
        PageLayout::setHelpKeyword('Basis.Dateien');
        PageLayout::setTitle($this->course->getFullname() . ' - ' . _('Dateien'));

        Navigation::activateItem('/course/files');
    }

    private function buildSidebar($index = 'index')
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/files-sidebar.png');

        $actions = new ActionsWidget();

        if ($this->topFolder->isEditable($GLOBALS['user']->id) && $this->topFolder->parent_id) {
            $actions->addLink(
                _("Ordner bearbeiten"),
                $this->url_for("file/edit_folder/".$this->topFolder->getId()),
                Icon::create("edit", "clickable"),
                array('data-dialog' => 1)
            );
        }

        if ($this->topFolder && $this->topFolder->isSubfolderAllowed($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Neuer Ordner'),
                URLHelper::getUrl('dispatch.php/file/new_folder/' . $this->topFolder->getId()),
                Icon::create('folder-empty+add', 'clickable')
            )->asDialog();

        }
        if ($this->topFolder && $this->topFolder->isWritable($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Datei hinzufügen'),
                '#',
                Icon::create('file+add', 'clickable'),
                ['onclick' => "STUDIP.Files.openAddFilesWindow(); return false;"]
            );
        }
        $sidebar->addWidget($actions);

        $views = new ViewsWidget();
        $views->addLink(
            _('Ordneransicht'),
            $this->url_for('course/files/index'),
            null,
            [],
            'index'
        )->setActive(true);
        $views->addLink(
            _('Alle Dateien'),
            $this->url_for('course/files/flat'),
            null,
            [],
            'flat'
        );

        $sidebar->addWidget($views);
    }

    /**
     * Displays the files in tree view
     **/
    public function index_action($topFolderId = '')
    {
        $this->marked_element_ids = [];

        if (!$topFolderId) {
            $folder = Folder::findTopFolder($this->course->id);
        } else {
            $folder = Folder::find($topFolderId);
        }

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        if (!$this->topFolder->isVisible($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }
        $this->buildSidebar('index');
        $this->render_template('files/index.php', $this->layout);
    }

    /**
     * Displays the files in flat view
     **/
    public function flat_action()
    {
        $this->marked_element_ids = [];

        $folder = Folder::findTopFolder($this->course->id);
        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        //find all files in all subdirectories:
        list($this->files, $this->folders) = array_values(FileManager::getFolderFilesRecursive($this->topFolder, $GLOBALS['user']->id));

        $this->range_type = 'course';
        $this->render_template('files/flat.php', $this->layout);
    }
}

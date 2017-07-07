<?php
/**
 * StandardFolder.php
 *
 * the standard folder is the default implementation for folders from courses institutes
 * and users. Special folders should extend this class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class StandardFolder implements FolderType
{
    /**
     * @var Folder
     */
    protected $folderdata;

    /**
     * StandardFolder constructor.
     * @param Folder|StandardFolder|null $folderdata
     */
    public function __construct($folderdata = null)
    {
        if ($folderdata instanceof StandardFolder) {
            $this->folderdata = $folderdata->folderdata;
        } elseif ($folderdata instanceof Folder) {
            $this->folderdata = $folderdata;
        } else {
            $this->folderdata = Folder::build($folderdata);
        }
        $this->folderdata['folder_type'] = get_class($this);
    }

    /**
     * @return string
     */
    public static function getTypeName()
    {
        return _('Ordner');
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        return true;
    }

    /**
     * @param string $role
     * @return Icon
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        if ($this->parent_id || !$this->id) {
            $shape = count($this->getSubfolders()) + count($this->getFiles()) === 0
                   ? 'folder-empty'
                   : 'folder-full';
        } else {
            $shape = count($this->getSubfolders()) + count($this->getFiles()) === 0
                   ? 'folder-home-empty'
                   : 'folder-home-full';
        }

        return Icon::create($shape, $role);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->folderdata->getId();
    }

    /**
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        return $this->folderdata[$attribute];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->folderdata[$name] = $value;
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isVisible($user_id)
    {
        $visible = ($this->range_type === 'user' && $this->range_id === $user_id)
                    || Seminar_Perm::get()->have_studip_perm('user', $this->range_id, $user_id);
        if ($visible && $parent_folder = $this->getParent()) {
            return $parent_folder->isVisible($user_id);
        }

        return $visible;
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isReadable($user_id)
    {
        $readable = ($this->range_type === 'user' && $this->range_id === $user_id)
                     || Seminar_Perm::get()->have_studip_perm('user', $this->range_id, $user_id);
        if ($readable && $parent_folder = $this->getParent()) {
            return $parent_folder->isReadable($user_id);
        }

        return $readable;
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isWritable($user_id)
    {
        return ($this->range_type === 'user' && $this->range_id === $user_id)
            || Seminar_Perm::get()->have_studip_perm('autor', $this->range_id, $user_id);
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isEditable($user_id)
    {
        return ($this->range_type === 'user' && $this->range_id === $user_id)
            || Seminar_Perm::get()->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isSubfolderAllowed($user_id)
    {
        return ($this->range_type === 'user' && $this->range_id === $user_id)
            || Seminar_Perm::get()->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    /**
     * @return string|Flexi_Template
     */
    public function getDescriptionTemplate()
    {
        return htmlReady($this->folderdata['description']);
    }

    /**
     * @return string|Flexi_Template
     */
    public function getEditTemplate()
    {
        return '';
    }

    /**
     * @param Request $request
     * @return FolderType|MessageBox
     */
    public function setDataFromEditTemplate($request)
    {
        if (!$request['name']) {
            return MessageBox::error(_('Die Bezeichnung des Ordners fehlt.'));
        }
        $this->folderdata['name']        = $request['name'];
        $this->folderdata['description'] = $request['description'] ?: '';
        return $this;
    }

    /**
     * @return bool|number
     */
    public function store()
    {
        return $this->folderdata->store();
    }

    /**
     * @param array $uploadedfile
     * @param string $user_id
     * @return string
     */
    public function validateUpload($uploadedfile, $user_id)
    {
        if ($this->range_type === 'course') {
            $status = $GLOBALS['perm']->get_studip_perm($this->range_id, $user_id);
            $active_upload_type = Course::find($this->range_id)->status;
        } elseif ($this->range_type === 'institute') {
            $status = $GLOBALS['perm']->get_studip_perm($this->range_id, $user_id);
            $active_upload_type = 'institute';
        } else {
            $status = $GLOBALS['perm']->get_perm($user_id);
            $active_upload_type = "personalfiles";
        }

        if (!isset($GLOBALS['UPLOAD_TYPES'][$active_upload_type])) {
            $active_upload_type = 'default';
        }

        $upload_type = $GLOBALS['UPLOAD_TYPES'][$active_upload_type];
        if ($upload_type['file_sizes'][$status] < $uploadedfile['size']) {
            return sprintf(
                _('Die maximale Größe für einen Upload (%s) wurde überschritten.'),
                relsize($upload_type['file_sizes'][$status])
            );
        }

        $ext   = strtolower(pathinfo($uploadedfile['name'], PATHINFO_EXTENSION));
        $types = array_map('strtolower', $upload_type['file_types']);

        if (!in_array($ext, $types) && $upload_type['type'] === 'deny') {
            return sprintf(
                _('Sie dürfen nur die Dateitypen %s hochladen!'),
                join(',', $upload_type['file_types'])
            );
        }

        if (in_array($ext, $types) && $upload_type['type'] === 'allow') {
            return sprintf(_('Sie dürfen den Dateityp %s nicht hochladen!'), $ext);
        }
    }

    /**
     * @return FolderType[]
     */
    public function getSubfolders()
    {
        // We must load the subfolders from the database instead
        // of using $this->folderdata->subfolders, because subfolders
        // that have been added to this folder aren't included in
        // $this->folderdata->subfolders although they are in the database.
        $subfolders = [];
        $database_subfolders = Folder::findByParent_id($this->getId());
        foreach ($database_subfolders as $subfolder) {
            //check FolderType of subfolder
            $subfolders[] = $subfolder->getTypedFolder();
        }
        return $subfolders;
    }

    /**
     * @return FileRef[]
     */
    public function getFiles()
    {
        // We must load the files (FileRefs) directly from the database
        // since files that were added to this folder object after it was
        // created are not included in the file_refs attribute:
        return FileRef::findByFolder_id($this->getId());
    }

    /**
     * Returns the parent-folder as a StandardFolder
     * @return FolderType
     */
    public function getParent()
    {
        return $this->folderdata->parentfolder
             ? $this->folderdata->parentfolder->getTypedFolder()
             : null;
    }

    /**
     * @param File|array $file
     * @return FileRef
     */
    public function createFile($file)
    {
        $newfile = $file;
        $file_ref_data = [];

        if (!is_a($newfile, 'File')) {
            $newfile = new File();
            $newfile->name      = $file['name'];
            $newfile->mime_type = $file['type'];
            $newfile->size      = $file['size'];
            $newfile->storage   = 'disk';
            $newfile->id        = $newfile->getNewId();
            $newfile->connectWithDataFile($file['tmp_path']);

            $file_ref_data['description'] = $file['description'];
            $file_ref_data['content_terms_of_use_id'] = $file['content_terms_of_use_id'];
        }

        if ($newfile->isNew()) {
            $newfile->store();
        }

        return $this->folderdata->linkFile(
            $newfile,
            array_filter($file_ref_data)
        );
    }

    /**
     * @param string $file_ref_id
     * @return int
     */
    public function deleteFile($file_ref_id)
    {
        $file_ref = $this->folderdata->file_refs->find($file_ref_id);

        if ($file_ref) {
            return $file_ref->delete();
        }
    }


    /**
     * @param FolderType $foldertype
     * @return bool
     */
    public function createSubfolder(FolderType $foldertype)
    {
        $foldertype->range_id   = $this->folderdata['range_id'];
        $foldertype->range_type = $this->folderdata['range_type'];
        $foldertype->parent_id  = $this->folderdata['id'];
        return $foldertype->store();
    }

    /**
     * @param string $subfolder_id
     * @return bool
     */
    public function deleteSubfolder($subfolder_id)
    {
        $subfolders = $this->folderdata->subfolders;

        if ($subfolders) {
            foreach ($subfolders as $subfolder) {
                if ($subfolder->id === $subfolder_id) {
                    //we found the subfolder that shall be deleted
                    return $subfolder->delete();
                }
            }
        }

        //if no subfolders are present or the subfolder can't be found
        //we return false:
        return false;
    }

    /**
     * @return int
     */
    public function delete()
    {
        return $this->folderdata->delete();
    }

    /**
     * @param FileRef|string $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileDownloadable($fileref_or_id, $user_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);
        if ($this->range_type === 'user') {
            return $user_id === $this->range_id;
        }

        if (in_array($this->range_type, ['course', 'institute'])) {
            if (is_object($fileref->terms_of_use)) {
                //terms of use are defined for this file!
                return $fileref->terms_of_use->fileIsDownloadable($fileref, true, $user_id);
            }
            return $GLOBALS['perm']->have_studip_perm('user', $this->range_id, $user_id);
        }

        return true;
    }

    /**
     * @param FileRef|string $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileEditable($fileref_or_id, $user_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);
        return $fileref->user_id === $user_id
            || $GLOBALS['perm']->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    /**
     * Checks if a user has write permissions to a file.
     *
     * For standard folders write permissions are granted
     * if the user is the owner of the file or if the user has at least
     * tutor permissions on the Stud.IP object specified by range_id
     * (such objects may be courses or institutes for example).
     *
     * @param FileRef|string $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileWritable($fileref_or_id, $user_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);
        return $fileref->user_id == $user_id
            || $GLOBALS['perm']->have_studip_perm('tutor', $this->range_id, $user_id);
    }
}

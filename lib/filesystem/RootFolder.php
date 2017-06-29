<?php
/**
 * RootFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright 2017 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class RootFolder extends StandardFolder
{

    /**
     * @return string
     */
    public static function getTypeName()
    {
        return _('Hauptordner');
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }

    /**
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if ($attribute === 'name' && $this->range_type && $this->range_id) {
            if (Context::getId() === $this->range_id) {
                $range = Context::get();
            } else {
                $range = call_user_func([$this->range_type, 'find'], $this->range_id);
            }
            return isset($range) ? $range->getFullname('short') : '';
        }
        return $this->folderdata[$attribute];
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isWritable($user_id)
    {
        return ($this->range_type === 'user' && $this->range_id === $user_id)
            || Seminar_Perm::get()->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function isEditable($user_id)
    {
        return false;
    }

    /**
     * Returns the parent-folder as a StandardFolder
     * @return FolderType
     */
    public function getParent()
    {
        return null;
    }

    /**
     * @return bool|number
     */
    public function store()
    {
        $this->folderdata['parent_id'] = '';
        return $this->folderdata->store();
    }
}
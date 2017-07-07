<?php
/**
 * CourseTopicFolder.php
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
class CourseTopicFolder extends StandardFolder implements FolderType
{
    public static function getTypeName()
    {
        return _('Themen-Ordner');
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        $course = Course::toObject($range_id_or_object);
        if ($course && !$course->isNew()) {
            return Seminar_Perm::get()->have_studip_perm('tutor', $course->id, $user_id) && CourseTopic::countBySql("seminar_id = ?" , [$course->id]);
        }
    }

    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        return Icon::create(
            count($this->getFiles()) ? 'folder-topic-full' : 'folder-topic-empty',
            $role
        );
    }

    /**
     * This method returns the special part for the edit template for the folder type GroupFolder
     *
     * @return mixed  A edit template for a instance of the type GroupFolder
     */
    public function getEditTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/topic_folder/edit.php');
        $template->set_attribute('folder', $this);
        return $template;
    }

    /**
     * Stores the data which was edited in the edit template
     * @return mixed The template with the edited data
     */
    public function setDataFromEditTemplate($request)
    {
        if ($request['topic_id'] == null) {
            return MessageBox::error(_('Es wurde kein Thema ausgewählt.'));
        }

        $this->folderdata['data_content']['topic_id'] = $request['topic_id'];
        if (!$request['name']) {
            $request['name'] = CourseTopic::find($request['topic_id'])->title;
        }

        return parent::setDataFromEditTemplate($request);
    }

    /**
     * Returns the description template for a instance of a GroupFolder type
     *
     * @return mixed A description template for a instance of the type GroupFolder
     */
    public function getDescriptionTemplate()
    {
        $topic = CourseTopic::find($this->folderdata['data_content']['topic_id']);

        $template = $GLOBALS['template_factory']->open('filesystem/topic_folder/description.php');
        $template->type      = self::getTypeName();
        $template->folder    = $this;
        $template->topic = $topic;

        return $template;
    }


}

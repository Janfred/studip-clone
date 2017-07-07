<?php
# Lifter010: TODO

/*
 * Copyright (C) 2009 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * This controller is used to manipulate the avatar of a course.
 *
 * @author    mlunzena
 */
class Course_AvatarController extends AuthenticatedController
{
    
    public function before_filter(&$action, &$args)
    {
        
        parent::before_filter($action, $args);
        
        $this->course_id = Request::get('cid', current($args));
        if ($this->course_id === '' || get_object_type($this->course_id) !== 'sem'
            || !$GLOBALS['perm']->have_studip_perm("tutor", $this->course_id)
        ) {
            $this->set_status(403);
            return false;
        }
        
        $this->body_id = 'custom_avatar';
        PageLayout::setTitle(Course::findCurrent()->getFullname() . ' - ' . _('Veranstaltungsbild ändern'));
        
        $sem                   = Seminar::getInstance($this->course_id);
        $this->studygroup_mode = $sem->getSemClass()->offsetget('studygroup_mode');
        
        if ($this->studygroup_mode) {
            $this->avatar = StudygroupAvatar::getAvatar($this->course_id);
        } else {
            $this->avatar = CourseAvatar::getAvatar($this->course_id);
        }
        
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/admin-sidebar.png');
        
        if ($this->avatar->is_customized()) {
            $actions = new ActionsWidget();
            $actions->addLink(_('Bild löschen'),
                $this->link_for('course/avatar/delete', $this->course_id), Icon::create('trash', 'clickable'),
                ['data-confirm' => _('Wirklich löschen?')])->asDialog(false);
            $sidebar->addWidget($actions);
        }
        
        if ($GLOBALS['perm']->have_studip_perm('admin', $this->course_id)) {
            $list = new SelectorWidget();
            $list->setUrl("?#admin_top_links");
            $list->setSelectParameterName("cid");
            foreach (AdminCourseFilter::get()->getCoursesForAdminWidget() as $seminar) {
                $list->addElement(new SelectElement($seminar['Seminar_id'], $seminar['Name']), 'select-' . $seminar['Seminar_id']);
            }
            $list->setSelection($this->course_id);
            $sidebar->addWidget($list);
        }
        
        Navigation::activateItem('/course/admin/avatar');
    }
    
    /**
     * This method is called to show the form to upload a new avatar for a
     * course.
     *
     * @return void
     */
    public function update_action()
    {
        // nothing to do
    }
    
    /**
     * This method is called to upload a new avatar for a course.
     *
     * @return void
     */
    public function put_action()
    {
        try {
            CourseAvatar::getAvatar($this->course_id)->createFromUpload('avatar');
        } catch (Exception $e) {
            $error = $e->getMessage();
            PageLayout::postError($error);
        }
        if (!$error) {
            PageLayout::postMessage(MessageBox::success(
                _('Die Bilddatei wurde erfolgreich hochgeladen.'),
                [_('Eventuell sehen Sie das neue Bild erst, nachdem Sie diese Seite neu geladen haben (in den meisten Browsern F5 drücken).')]
            ));
        }
        $this->render_action("update");
    }
    
    /**
     * This method is called to remove an avatar for a course.
     *
     * @return void
     */
    public function delete_action()
    {
        CourseAvatar::getAvatar($this->course_id)->reset();
        PageLayout::postMessage(MessageBox::success(_('Veranstaltungsbild gelöscht.')));
        if ($this->studygroup_mode) {
            $this->redirect(URLHelper::getUrl('dispatch.php/course/studygroup/edit/' . $this->course_id));
        } else {
            $this->redirect(URLHelper::getUrl('dispatch.php/course/avatar/update/' . $this->course_id));
        }
    }
}

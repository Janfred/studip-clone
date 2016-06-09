<?php

/*
 * archive.php - contains Course_ArchiveController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5.alpha-svn
 */

//rewrite of /public/archiv_assi.php

/**
    Course_ArchiveController is a controller that allows users
    who have the required permissions to archive a course.
    
    It replaces the old script at /public/archiv_assi.php.
*/
class Course_ArchiveController extends AuthenticatedController
{
    /**
        This method checks if the current user has the required
        permissions to archive a course.
    */
    private function userHasPermission($courseId = null)
    {
        global $perm;
        //check permissions: user has to be an administrator of the course:
        $requiredPermission = 'admin';
        if(get_config('ALLOW_DOZENT_ARCHIV')) {
            //members of the "dozent" role may also archive the course:
            $requiredPermission = 'dozent';
        }
        return $perm->have_studip_perm($requiredPermission, $courseId);
    }
    
    
    
    /**
        This action collects all required data about the course.
    */
    public function confirm_action($courseId = null)
    {
        //TODO: make it possible to archive more than one course at a time!
        
        global $perm;
        //check if the user has the required permission
        //to archive the selected course:
        if (!$this->userHasPermission($courseId)) {
            //no permission: access denied!
            throw new AccessDeniedException();
        }
        
        //TODO: enable navigation items, depending wheter the user
        // is in the admin role or not.
        
        
        //get the course object: TODO: resolve multiple course-IDs (to archive more than one course)
        
        //tcourse is only temporary until the whole code can handle an array of courses
        $tcourse = Course::find($courseId);
        if ($tcourse == false) {
            //course not found!
            throw new Exception(_("Veranstaltung nicht gefunden!"));
        }
        
        
        $this->courses = array($tcourse); //temporary workaround
        
        //activate navigation elements if they exist:
        if ($perm->have_perm('admin')) {
            if (Navigation::hasItem('/browse/my_courses/list')) {
                Navigation::activateItem('/browse/my_courses/list');
            }
        } else {
            if (Navigation::hasItem('/course/admin/main/archive')) {
                Navigation::activateItem('/course/admin/main/archive');
            }
        }
        
        //set page title with the area of Stud.IP and the course's name:
        PageLayout::setTitle(
            $_SESSION['SessSemName']["header_line"] //Stud.IP area + course name from here
            .  ' - ' 
            . _("Archivieren von Veranstaltungen") 
            );
        
        //get list of "dozenten" for each course:
        $this->dozenten = array();
        
        foreach ($this->courses as $course) {
            /*$course->dozenten = $course->members->filter(
                                function ($member) {
                                    return $member['status'] === "dozent"; 
                                }
                            );
            */
            //cannot add attributes to course directly. TODO: resolve that problem!
            $this->dozenten[$course->id] = $course->members->filter(
                                function ($member) {
                                    return $member['status'] === "dozent"; 
                                }
                            );
        }
    }
    
    
    /**
        This action does the actual archiving of a course.
    */
    public function archive_action($courseId = null)
    {
        global $perm;
        
        //check if the user has the required permission
        //to archive the course:
        if (!$this->userHasPermission($courseId)) {
            //no permission: access denied!
            throw new AccessDeniedException();
        }
        
        
    }
}

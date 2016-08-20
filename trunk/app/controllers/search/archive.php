<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Moritz Strohm <strohm@data-quest.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    3.5.alpha-svn
 */

class Search_ArchiveController extends AuthenticatedController
{
    
    public function index_action()
    {
        PageLayout::setTitle(_('Suche im Veranstaltungsarchiv'));
        
        
        if(Request::get('search')) {
            /* 
                A search form was sent here:
                We have to make lookups in the database.
            */
            
            $this->searchRequested = true;
            
            //read parameters from HTTP POST, if they exist:
            $this->myCoursesOnly = Request::get('myCoursesOnly', false);
            $this->archivedCourseName = trim(Request::get('search', '')); //strip whitespaces here
            
            //mb_strlen is used for unicode compatibility
            if(mb_strlen($this->archivedCourseName) < 4) {
                //search keyword too short
                $this->errorMessage = sprintf(_('Suchbegriff muss mindestens %s Zeichen lang sein!'), 4);
            }
            else
            {
                
                if($this->myCoursesOnly) {
                    /*
                        If the user wants to see only his courses 
                        we have to filter the courses:
                    */
                    
                    $user = User::findCurrent();
                    
                    $allUserEntries = ArchivedCourseMember::findBySQL(
                        "user_id = :userId", array('userId' => $user->id));
                    
                    $this->foundCourses = array();
                    
                    foreach($allUserEntries as $userEntry) {
                        
                        $this->foundCourses[] = $userEntry->course;
                    }
                    
                }
                else
                {
                    $queryParameters = array('criteria' => $this->archivedCourseName);
                    //get courses where at least one field matches the search criteria
                    
                    $sql = "name LIKE CONCAT('%', :criteria, '%') "
                        . "OR untertitel LIKE CONCAT('%', :criteria, '%') "
                        . "OR beschreibung LIKE CONCAT('%', :criteria, '%') "
                        . "OR dozenten LIKE CONCAT('%', :criteria, '%') "
                        . "OR institute LIKE CONCAT('%', :criteria, '%') "
                        . "OR semester LIKE CONCAT('%', :criteria, '%') "
                        
                        //order:
                        . "ORDER BY start_time DESC, name DESC ";
                    
                    $this->foundCourses = ArchivedCourse::findBySQL($sql, $queryParameters);
                }
                
            }
            
            $sidebar = Sidebar::get();
            $checkboxWidget = new OptionsWidget();
            $checkboxWidget->addCheckbox(
                _('Nur eigene Veranstaltungen anzeigen'),
                (bool)Request::get('myCoursesOnly', false),
                URLHelper::getUrl('dispatch.php/search/archive', array('search' => $this->archivedCourseName, 'myCoursesOnly' => '1')),
                URLHelper::getUrl('dispatch.php/search/archive', array('search' => $this->archivedCourseName))
            );
            
            $sidebar->addWidget($checkboxWidget);
        }
    }
}

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
        if(Navigation::hasItem('/search/archive')) {
            Navigation::activateItem('/search/archive');
        }
        
        
        $this->criteria = trim(Request::get('criteria', '')); //strip whitespaces here
        $this->selectedSemester = Request::get('selectedSemester', '');
        $this->selectedDepartment = Request::get('selectedDepartment', '');
        //the optional parameter myCoursesOnly says that
        //only the courses of the current user shall be searched
        //with the search criteria
        $this->myCoursesOnly = Request::get('myCoursesOnly', false);
        
        $this->searchRequested = Request::get('searchRequested');
        
        
        //build sidebar:
        $sidebar = Sidebar::get();
        $checkboxWidget = new OptionsWidget();
        $checkboxWidget->addCheckbox(
            _('Nur eigene Veranstaltungen anzeigen'),
            (bool)Request::get('myCoursesOnly', false),
            URLHelper::getUrl(
                'dispatch.php/search/archive',
                [
                    'criteria' => $this->criteria,
                    'selectedSemester' => $this->selectedSemester,
                    'selectedDepartment' => $this->selectedDepartment,
                    'myCoursesOnly' => '1'
                ]
            ),
            URLHelper::getUrl(
                'dispatch.php/search/archive',
                [
                    'criteria' => $this->criteria,
                    'selectedSemester' => $this->selectedSemester,
                    'selectedDepartment' => $this->selectedDepartment
                ]
            )
        );
        $sidebar->addWidget($checkboxWidget);
        
        //get available semesters:
        
        $this->availableSemesters = Semester::getAll();
        $this->availableDepartments = Institute::findBySql('fakultaets_id = institut_id ORDER BY fakultaets_id');
        
        
        if($this->searchRequested) {
            //check if at least one search criteria was given:
            if(!$this->criteria and !$this->selectedSemester and !$this->selectedDepartment) {
                //no search criteria was set
                PageLayout::postError(_('Es wurde kein Suchkriterium ausgewählt!'));
                return;
                $this->errorOccured = true;
            }
            
            
            //mb_strlen is used for unicode compatibility
            if((mb_strlen($this->criteria) > 0) and (mb_strlen($this->criteria) < 4)) {
                //search keyword is set and too short
                PageLayout::postError(sprintf(_('Der Name der Veranstaltung muss mindestens %s Zeichen lang sein!'), 4));
                return;
                $this->errorOccured = true;
            }
            
            //ok, checks are done: build SQL query:
            
            $sql = "(name LIKE CONCAT('%', :criteria, '%') "
                    . "OR untertitel LIKE CONCAT('%', :criteria, '%') "
                    . "OR beschreibung LIKE CONCAT('%', :criteria, '%') "
                    . "OR dozenten LIKE CONCAT('%', :criteria, '%')) ";
            $sqlArray = ['criteria' => $this->criteria];
            
            if($this->myCoursesOnly) {
                /*
                    If the user wants to see only his courses 
                    we have to filter the courses by user-ID:
                */
                $sql .= "AND (user_id = :userId) ";
                $sqlArray['userId'] = User::findCurrent()->id;
            }
            
            if($this->selectedDepartment) {
                $sql .= "AND (institute = :selectedDepartment) ";
                $sqlArray['selectedDepartment'] = $this->selectedDepartment;
            }
            
            if($this->selectedSemester) {
                $sql .= "AND (semester = :selectedSemester) ";
                $sqlArray['selectedSemester'] = $this->selectedSemester;
            }
            
            //database entry order:
            $sql .= "ORDER BY start_time DESC, name DESC ";
            
            //first we count the courses: if there are too many
            //we won't collect them!
            $this->amountOfCourses = ArchivedCourse::countBySql($sql, $sqlArray);
            if($this->amountOfCourses > 150) {
                PageLayout::postError(sprintf(_('Es wurden %s Veranstaltungen gefunden. Bitte grenzen sie die Suchkriterien weiter ein!'), $this->amountOfCourses));
            } else {
                //less than 151 courses: we can display them
                $this->foundCourses = ArchivedCourse::findBySQL($sql, $sqlArray);
                
                if($this->foundCourses) {
                    if(count($this->foundCourses) == 1){
                        PageLayout::postInfo(_('Es wurde eine Veranstaltung gefunden!'));
                    } else {
                        PageLayout::postInfo(sprintf(_('Es wurden %s Veranstaltungen gefunden!'), $this->amountOfCourses));
                    }
                } else {
                    PageLayout::postInfo(_('Es wurde keine Veranstaltung gefunden!'));
                }
            }
        }
    }
}

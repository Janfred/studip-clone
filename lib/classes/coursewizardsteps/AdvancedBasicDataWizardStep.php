<?php
/**
 * AdvancedBasicDataWizardStep.php
 * Course wizard step for getting the basic course data.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @author      Stefan Osterloh <s.osterloh@uni-oldenburg.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class AdvancedBasicDataWizardStep extends BasicDataWizardStep
{
    /**
     * Returns the Flexi template for entering the necessary values
     * for this step.
     *
     * @param Array $values Pre-set values
     * @param int $stepnumber which nqumber has the current step in the wizard?
     * @param String $temp_id temporary ID for wizard workflow
     * @return String a Flexi template for getting needed data.
     */
    public function getStepTemplate($values, $stepnumber, $temp_id)
    {
        $values = $this->adjustValues($values);

       // We only need our own stored values here.
        if ($values[__CLASS__]['studygroup']) {
            return parent::getStepTemplate($values, $stepnumber, $temp_id);
        }

        // Load template from step template directory.
        $factory = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'].'/app/views/course/wizard/steps');
        $template = $factory->open('advancedbasicdata/index');
        
        return $this->setupTemplateAttributes($template, $values, $stepnumber, $temp_id)->render();
    }
    
    public function validate($values) 
    {
        $values = $this->adjustValues($values);
        return parent::validate($values);
    }
    
    public function storeValues($course, $values) 
    {
        $values = $this->adjustValues($values);
        $course = parent::storeValues($course, $values);
        if ($course) {
            $course->Untertitel = $values[__CLASS__]['subtitle'];
            $course->art = $values[__CLASS__]['kind'];
            $course->ects = $values[__CLASS__]['ects'];
            $course->admission_turnout = $values[__CLASS__]['maxmembers'];
            if (!$course->store()) {
                PageLayout::postError(sprintf(_('Es ist ein Fehler beim Speichern der Erweiterten-Einstellungen für %s aufgetreten. Kontrollieren Sie bitte:')
                        ,$course->name),
                        array(_('Untertitel der Veranstalung'), 
                            _('Art der Veranstaltung'), 
                            _('ECTS-Punkte der Veranstaltung'),
                            _('Max. Teilnehmerzahl der Veranstaltung')));
            }
            return $course;            
        }
        return false;
    }
    
    private function adjustValues($values)
    {
        $parent_class = get_parent_class($this);
        
        if (!isset($values[__CLASS__]) && isset($values[$parent_class])) {
            $values[__CLASS__] = $values[$parent_class];
        } else {
            $values[$parent_class] = $values[__CLASS__];
        }

        return $values;
    }
}
    
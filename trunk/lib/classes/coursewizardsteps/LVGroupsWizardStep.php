<?php
/**
 * LVGroupsWizardStep.php
 * Course wizard step for assigning LV Groups.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once dirname(__FILE__) . '/../StudipLvgruppeSelection.class.php';

class LVGroupsWizardStep implements CourseWizardStep
{
    /**
     * Returns the Flexi template for entering the necessary values
     * for this step.
     *
     * @param Array $values Pre-set values
     * @param int $stepnumber which number has the current step in the wizard?
     * @param String $temp_id temporary ID for wizard workflow
     * @return String a Flexi template for getting needed data.
     */
    public function getStepTemplate($values, $stepnumber, $temp_id)
    {
        $mvv_plugin = PluginEngine::getPlugin("MVVPlugin");
        $mvv_basepath = $mvv_plugin->getPluginPath();
        
        // We only need our own stored values here.
        $values = $values[__CLASS__];        
        
        // Load template from step template directory.
        $factory = new Flexi_TemplateFactory($mvv_basepath.'/views');
        $tpl = $factory->open('coursewizard/lvgroups/index');
        $tpl->set_attribute('values', $values);
        
        $lvgtree = new StudipLvgruppeSelection();
        
       
        $selection = self::get_selection($temp_id);  
        if (!empty($values['lvgruppe_selection']['areas'])) {
            foreach ($values['lvgruppe_selection']['areas'] as $area_id) {
                $lvgroup = Lvgruppe::find($area_id);            
                $selection->add($lvgroup);
                //$open_nodes[] = $area_id;
            }     
        }
        
        $selection_details = $values['lvgruppe_selection']['area_details'];
        
        $open_nodes = !empty($values['open_lvg_nodes'])?$values['open_lvg_nodes']:array();
        
        $tpl->set_attribute('open_lvg_nodes', $open_nodes);    
        $tpl->set_attribute('selection', $selection);
        $tpl->set_attribute('selection_details', $selection_details);
        $tpl->set_attribute('tree', $lvgtree->getRootItem()->getChildren());   
        
        $tpl->set_attribute('ajax_url', $values['ajax_url'] ?: URLHelper::getLink('dispatch.php/course/wizard/ajax'));
        $tpl->set_attribute('no_js_url', $values['no_js_url'] ? : 'dispatch.php/course/wizard/forward/'.$stepnumber.'/'.$temp_id);
        $tpl->set_attribute('stepnumber', $stepnumber);
        $tpl->set_attribute('temp_id', $temp_id);
        return $tpl->render();
    }
    
    /**
     * Returns a LvGruppen-Selection object for a given course ID.
     *
     * @param  string     either the MD5ish ID of a course or something falsy to
     *                    indicate a course that is currently being created
     *
     * @return mixed      LvGruppen-Selection object representing
     *                    the selection form
     */
    public function get_selection($course_id)
    {
        if (self::isCourseId($course_id)) {
            $selection = new StudipLvgruppeSelection($course_id);
        }
        else {
    
            $lvgruppen = array();
            if (isset($GLOBALS['sem_create_data']) &&
                isset($GLOBALS['sem_create_data']['sem_lvgruppen'])) {
                    $lvgruppen = $GLOBALS['sem_create_data']['sem_lvgruppen'];
                }
    
                $selection = new StudipStudyAreaSelection();
                $selection->setLvgruppen($lvgruppen);
        }
        return $selection;
    }
    
    /**
     * Every (non-empty) string is a valid course ID except the string '-'
     *
     * @param mixed  the value to check
     * @return bool  TRUE if it is courseID-ish, FALSE otherwise
     */
    public static function isCourseId($id)
    {
        return is_string($id) && $id !== '' && $id !== '-';
    }
    
    
    public function getLVGroupTreeLevel($parentId, $parentClass)
    {
        $level = array();
        $children = array();
        $searchtree = array();
        
        $lvgtree = new StudipLvgruppeSelection();       
        
        $mvvid = explode('-', $parentId);
        $mvvobj = $parentClass::find($mvvid[0]);        
        $children = $mvvobj->getChildren();
        
        foreach ($children as $c) {
            $level[] = array(
                'id' => $c->getId().'-'.$mvvid[0],
                'name' => studip_utf8encode($c->getDisplayname()),
                'has_children' => $c->hasChildren(),
                'parent' => $c->getTrailParentId(),
                'assignable' => $c->isAssignable(),
                'mvvclass' => get_class($c)
            ); 
        }
        
        if (Request::isXhr()) {
            return json_encode($level);
        } else {
            return $level;
        }
    }
    
    public function searchLVGroupTree($searchterm, $utf=true, $id_only=false)
    {
        $result = array();
        $mvv_plugin = PluginEngine::getPlugin("MVVPlugin");
        $mvv_basepath = $mvv_plugin->getPluginPath();
        
        $factory = new Flexi_TemplateFactory($mvv_basepath.'/views');
        $tpl = new Flexi_PhpTemplate('lvgselector/entry_trails', $factory);
        
        $selection = self::get_selection(Request::get("cid"));
        $selectedlvg = array();
        if (!empty($selection))$selectedlvg = $selection->getLvGruppenIDs();
        
        foreach (Lvgruppe::findBySearchTerm($searchterm) as $area) {              
            $inlist = in_array($area->id, $selectedlvg);
            if ($inlist) continue;
            
            $renderd = $tpl->render_partial('coursewizard/lvgroups/lvgroup_searchentry', compact('area', 'inlist'));            
            $data = array(
                'id' => $area->id,
                'html_string' => $renderd
            );
            $result[] = $data;
        }
        return json_encode($result);
    }
    
    public function getLVGroupDetails($id) {
        $mvvid = explode('-', $id);
        $area = Lvgruppe::find($mvvid[0]);
        $mvv_plugin = PluginEngine::getPlugin("MVVPlugin");
        $mvv_basepath = $mvv_plugin->getPluginPath();
        
        $factory = new Flexi_TemplateFactory($mvv_basepath.'/views');
        $tpl = new Flexi_PhpTemplate('lvgselector/entry_trails', $factory);
        $renderd = $tpl->render_partial('lvgselector/entry_trails', compact('area'));
        
        $data = array(
            'id' => $area->id,
            'html_string' => $renderd
        );
        if (Request::isXhr()) {
            return json_encode($data);
        } else {
            return $data;
        }
    }
    
    public function getAncestorTree($id)
    {
        $mvvid = explode('-', $id);
        $area = Lvgruppe::find($mvvid[0]);
        $mvv_plugin = PluginEngine::getPlugin("MVVPlugin");
        $mvv_basepath = $mvv_plugin->getPluginPath();
        
        $factory = new Flexi_TemplateFactory($mvv_basepath.'/views');
        $tpl = new Flexi_PhpTemplate('coursewizard/lvgroups/lvgroup_entry', $factory);
        $renderd = $tpl->render_partial('coursewizard/lvgroups/lvgroup_entry', compact('area'));
        
        $data = array(
            'id' => $area->id,
            'html_string' => $renderd
        );
        return json_encode($data);
    }
  

    /**
     * Catch form submits other than "previous" and "next" and handle the
     * given values. This is only important for no-JS situations.
     * @param Array $values currently set values for the wizard.
     * @return bool
     */
    public function alterValues($values)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];
                
        if (Request::submitted("open_nodes")) {
            $already_open_nodes = unserialize(Request::get("open_nodes"));
            foreach ($already_open_nodes as $open_lvgnode) {
                $values['open_lvg_nodes'][] = $open_lvgnode;
            }
        }
        
        if (Request::option("open_node")) {
            $node_to_open = Request::get("open_node");
            if (!in_array($node_to_open, $values['open_lvg_nodes'])) {
                $values['open_lvg_nodes'][] = $node_to_open;
            } else {
                $k = array_search($node_to_open, $values['open_lvg_nodes']);
                unset($values['open_lvg_nodes'][$k]);
            }
        }
        
        if (Request::submitted("lvgruppe_selection")) {
            
            $lvgruppe_selection = Request::getArray("lvgruppe_selection");
              
            if (isset($lvgruppe_selection['details'])) {                
                foreach (array_keys($lvgruppe_selection['details']) as $lvgid) {   
                    $detail = $this->getLVGroupDetails($lvgid);
                    $values['lvgruppe_selection']['area_details'][$detail['id']] = $detail['html_string'];
                }                
            }
            
            if (isset($lvgruppe_selection['remove'])) {
                $new_areas = array();
                foreach ($lvgruppe_selection['areas'] as $area) {
                    if(!key_exists($area, $lvgruppe_selection['remove'])) {
                        $new_areas[] = $area;
                    }
                }
                $values['lvgruppe_selection']['areas'] = $new_areas;
            }
        }
        
        if ($assign = array_keys(Request::getArray('assign'))) {            
            $values['lvgruppe_selection']['areas'][] = $assign[0];            
        }
        
        return $values;
    }

    /**
     * Validates if given values are sufficient for completing the current
     * course wizard step and switch to another one. If not, all errors are
     * collected and shown via PageLayout::postMessage.
     *
     * @param mixed $values Array of stored values
     * @return bool Everything ok?
     */
    public function validate($values)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];
        $ok = true;
       /* $errors = array();
        if (!$values['lvgruppe_selection']['areas']) {
            $ok = false;
            $errors[] = _('Der Veranstaltung muss mindestens eine Lehrveranstaltungsgruppe zugeordnet sein.');
        }
        if ($errors) {
            PageLayout::postMessage(MessageBox::error(
                _('Bitte beheben Sie erst folgende Fehler, bevor Sie fortfahren:'), $errors));
        }*/
        return $ok;
    }

    /**
     * Stores the given values to the given course.
     *
     * @param Course $course the course to store values for
     * @param Array $values values to set
     * @return Course The course object with updated values.
     */
    public function storeValues($course, $values)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];
        
        $selection = new StudipLvgruppeSelection($course->id);
        foreach ($values['lvgruppe_selection']['areas'] as $lvg_id) {
            $area = Lvgruppe::find($lvg_id);
            $selection->add($area);            
        }
        if ($this->is_locked($values)) {
            throw new AccessDeniedException();
        } else {
            LvGruppe::setLvgruppen($course->id, $selection->getLvgruppenIDs());
        }
        
        return $course;
    }

    /**
     * Checks if the current step needs to be executed according
     * to already given values. A good example are study areas which
     * are only needed for certain sem_classes.
     *
     * @param Array $values values specified from previous steps
     * @return bool Is the current step required for a new course?
     */
    public function isRequired($values)
    {
        // is locked?
        // Set global state in MVV_ACCESS_ASSIGN_LVGRUPPEN
        $locked = $this->is_locked($values);
        
        // DOES the course's class permit "lvgruppen"?
        $coursetype = $values['BasicDataWizardStep']['coursetype'];
        $class = $GLOBALS['SEM_TYPE'][$coursetype]["class"];
        $lvgruppen_not_allowed = !$GLOBALS['SEM_CLASS'][$class]["module"];
                
        if (!$locked && !$lvgruppen_not_allowed) {
            return true;
        } else {
            return false;
        }
        
    }
    
    public function is_locked($values)
    {
        global $perm;
    
        // Has user access to this function? Access state is configured in global config.
        $access_right = get_config('MVV_ACCESS_ASSIGN_LVGRUPPEN');
        if ($perm->have_perm('root')) {
            return false;
        } else {
            if ($access_right == 'fakadmin') {
                if ($perm->have_perm('admin')) {
                    /*$db = DBManager::get();
                    $st = $db->prepare("SELECT Seminar_id FROM user_inst a
                                LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)
                                LEFT JOIN Institute c ON (b.Institut_id=c.fakultaets_id)
                                LEFT JOIN seminare d ON (d.Institut_id=c.Institut_id)
                                WHERE a.user_id = ? AND a.inst_perms='admin' AND d.Seminar_id = ? LIMIT 1");
                    $st->execute(array($GLOBALS['user']->id, $course_id));
                    if ($st->fetchColumn()) {
                        return false;
                    }*/
                    
                    if (isset($values['BasicDataWizardStep'])){
                        $inst_id = $values['BasicDataWizardStep']['institute'];
                        if($GLOBALS['perm']->have_studip_perm('admin', $inst_id)) {
                            return false;
                        }
                    }
                }
                return true;
            }
        }
        return !$perm->have_studip_perm($access_right, $course_id);
                
    }

    /**
     * Copy values for study areas wizard step from given course.
     * @param Course $course
     * @param Array $values
     */
    public function copy($course, $values)
    {
        $data = array();
        $selection = new StudipLvgruppeSelection($course->id);        
        foreach ($selection->getAreas() as $a) {
            /*
             * Check if areas assigned to given course are
             * still assignable.
             */
            if ($a->isAssignable()) {
                $data['lvgroups'][] = $a->id;
            }
        }
        $values[__CLASS__] = $data;
        return $values;
    }

}

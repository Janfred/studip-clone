<?php
/*
 * siteinfo - display information about Stud.IP
 *
 * Copyright (c) 2008  Ansgar Bockstiegel
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'app/models/siteinfo.php';

class SiteinfoController extends Trails_Controller
{
    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        global $perm, $template_factory, $CURRENT_PAGE;
        global $_language_path, $_language;

        $this->populate_ids($args);

        # open session
        page_open(array('sess' => 'Seminar_Session',
                        'auth' => 'Seminar_Auth',
                        'perm' => 'Seminar_Perm',
                        'user' => 'Seminar_User'));

        $_language_path = init_i18n($_language);


	if($perm->have_perm('root')){
	        $this->layout = $template_factory->open('layouts/base');
            $this->layout->set_attribute('infobox', $this->infobox_content());
	}else{
            $action = "show";
	        $this->layout = $template_factory->open('layouts/base_without_infobox');

	}
        $this->layout->set_attribute('tabs', 'links_siteinfo');
        $this->layout->set_attribute('reiter_view', 'siteinfo');
        $this->set_layout($this->layout);
        $CURRENT_PAGE = _('Impressum');
    }

    function populate_ids($args)
    {
        global $view,$dynstradd;

        if (isset($args[0])&&is_numeric($args[0])){
            $this->currentrubric = $args[0];
            if(isset($args[1])&&is_numeric($args[1])){
                $this->currentdetail = $args[1];
                $view = $this->currentrubric.'_'.$this->currentdetail;
            }else{
                $this->currentdetail = first_detail_id($args[0]);
                $view = $this->currentrubric;
            }
        }else{
            $this->currentrubric = first_rubric_id();
            $this->currentdetail = first_detail_id();
        }
        if($this->currentdetail==0){
            $dynstradd['r'.$this->currentrubric.'_d0'] = array('topKat' => 'r'.$this->currentrubric, 
                                                               'name' => 'leere Kategorie', 
                                                               'link' => $this->url_for('siteinfo/show/'.$this->currentrubric),
                                                               'active' => FALSE);
        }
        $view = 'r'.$this->currentrubric.'_d'.$this->currentdetail;
    }
    
    function infobox_content()
    {
        global $rubrics_empty;
        if (!$rubrics_empty){
            $infobox_actions[] = array('icon' => 'add_sheet.gif',
                                       'text' => '<a href="'.$this->url_for('siteinfo/new/'.$this->currentrubric).'">neue Seite anlegen');
            $infobox_actions[] = array('icon' => 'edit_transparent.gif',
                                       'text' => '<a href="'.$this->url_for('siteinfo/edit/'.$this->currentrubric.'/'.$this->currentdetail).'">Seite bearbeiten');
            $infobox_actions[] = array('icon' => 'trash.gif',
                                       'text' => '<a href="'.$this->url_for('siteinfo/delete/'.$this->currentrubric.'/'.$this->currentdetail).'">Seite l&ouml;schen');
        }
        $infobox_actions[] = array('icon' => 'cont_folder_add.gif',
                                   'text' => '<a href="'.$this->url_for('siteinfo/new').'">neue Rubrik anlegen');
        $infobox_actions[] = array('icon' => 'cont_folder4.gif',
                                   'text' => '<a href="'.$this->url_for('siteinfo/edit/'.$this->currentrubric).'">Rubrik bearbeiten');
        $infobox_actions[] = array('icon' => 'trash.gif',
                                   'text' => '<a href="'.$this->url_for('siteinfo/delete/'.$this->currentrubric).'">Rubrik l&ouml;schen');
        return array('picture' => 'impressum.jpg',
                     'content' => array(array('kategorie' => _("Administration des Impressums"),
                                              'eintrag' => $infobox_actions))
                    );
    } 

    /**
     * common tasks for all actions
     */
    function after_filter ($action, $args)
    {
        page_close();
    }

    /**
     * Display the siteinfo
     */
    function show_action ()
    {
        $this->output = get_detail_content_processed($this->currentdetail);
    }

    function new_action ($givenrubric=NULL)
    {
        global $view, $dynstradd;
        if($givenrubric===NULL){
            $dynstradd['rubric_new'] = array('topKat' => '', 
                                             'name' => 'neue Rubrik', 
                                             'link' => '#',
                                             'active' => FALSE);
            $dynstradd['detail_new'] = array('topKat' => 'rubric_new', 
                                             'name' => 'neue Seite in neuer Rubrik', 
                                             'link' => '#',
                                             'active' => FALSE);
            $view = "detail_new";
            $this->edit_rubric = TRUE;
        }else{        
            $dynstradd['detail_new'] = array('topKat' => 'r'.$this->currentrubric, 
                                             'name' => 'neue Seite', 
                                             'link' => '#',
                                             'active' => FALSE);
            $this->rubrics = get_all_rubrics();
            $view = "detail_new";
        }
    }

    function edit_action ($givenrubric=NULL, $givendetail=NULL)
    {
        if(is_numeric($givendetail)){
            $this->rubrics = get_all_rubrics();
            $this->rubric_id = rubric_for_detail($this->currentdetail);
            $this->detail_name = get_detail_name($this->currentdetail);
            $this->content = get_detail_content($this->currentdetail);
        }else{
            $this->edit_rubric = TRUE;
            $this->rubric_id = $this->currentrubric;
       }
        $this->rubric_name = rubric_name($this->currentrubric);
    }

    function save_action ()
    {
        if (isset($_POST['rubric_id'])){
            if(isset($_POST['detail_id'])){
                list($rubric, $detail) = save("update_detail", array("rubric_id" => $_POST['rubric_id'],
                                                                     "detail_name" => $_POST['detail_name'],
                                                                     "content" => $_POST['content'],
                                                                     "detail_id" => $_POST['detail_id']));
            }else{
                if(isset($_POST['content'])){
                list($rubric, $detail) = save("insert_detail", array("rubric_id" => $_POST['rubric_id'],
                                                                     "detail_name" => $_POST['detail_name'],
                                                                     "content" => $_POST['content']));
                }else{
                    list($rubric, $detail) = save("update_rubric", array("rubric_id" => $_POST['rubric_id'],
                                                                         "rubric_name" => $_POST['rubric_name']));
                }
            }
        }else{
            list($rubric, $detail) = save("insert_rubric", array("rubric_name" => $_POST['rubric_name']));
        }
        $this->redirect('siteinfo/show/'.$rubric.'/'.$detail);
    }

    function delete_action ($givenrubric=NULL, $givendetail=NULL, $execute=FALSE)
    {
        $db = DBManager::get();
        if($execute){
            if($givendetail=="all"){
                $db->exec("DELETE FROM siteinfo_details WHERE rubric_id = ".$db->quote($this->currentrubric).";");
                $db->exec("DELETE FROM siteinfo_rubrics WHERE rubric_id = ".$db->quote($this->currentrubric).";");
                $this->redirect('siteinfo/show');
            }else{
                $db->exec("DELETE FROM siteinfo_details WHERE detail_id = ".$db->quote($this->currentdetail).";");
                $this->redirect('siteinfo/show/'.$rubric);
            }
        }else{
            if(is_numeric($givendetail)){
                $this->detail = TRUE;
            }
            $this->output = get_detail_content_processed($this->currentdetail);
        }
    }
}
?>

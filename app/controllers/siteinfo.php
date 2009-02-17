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
    private $si;
    
    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        global $perm, $template_factory, $CURRENT_PAGE;
        global $_language_path, $_language;
        
        $this->si = new Siteinfo();

        $this->populate_ids($args);

        # open session
        page_open(array('sess' => 'Seminar_Session',
                        'auth' => 'Seminar_Default_Auth',
                        'perm' => 'Seminar_Perm',
                        'user' => 'Seminar_User'));

        if (!isset($_language)) {
            $_language = get_accepted_languages();
        }

        $_language_path = init_i18n($_language);

        if ($perm->have_perm('root')) {
            $this->layout = $template_factory->open('layouts/base');
            $this->layout->set_attribute('infobox', $this->infobox_content());
        } else {
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

        if (isset($args[0]) && is_numeric($args[0])) {
            $this->currentrubric = $args[0];
            if (isset($args[1]) && is_numeric($args[1])) {
                $this->currentdetail = $args[1];
                $view = $this->currentrubric.'_'.$this->currentdetail;
            } else {
                $this->currentdetail = $this->si->first_detail_id($args[0]);
                $view = $this->currentrubric;
            }
        } else {
            $this->currentrubric = $this->si->first_rubric_id();
            $this->currentdetail = $this->si->first_detail_id();
        }
        $view = 'r'.$this->currentrubric;
        if ($this->currentdetail > 0){
            $view .= '_d'.$this->currentdetail;
        }
    }
    
    function infobox_content()
    {
        global $rubrics_empty;
        if (!$rubrics_empty) {
            if ($this->currentrubric > 0) {
                $infobox_actions[] = array('icon' => 'add_sheet.gif',
                                           'text' => '<a href="'.$this->url_for('siteinfo/new/'.$this->currentrubric).'">'._('neue Seite anlegen').'</a>');
            }
            if ($this->currentdetail > 0) {
                $infobox_actions[] = array('icon' => 'edit_transparent.gif',
                                           'text' => '<a href="'.$this->url_for('siteinfo/edit/'.$this->currentrubric.'/'.$this->currentdetail).'">'._('Seite bearbeiten').'</a>');
                $infobox_actions[] = array('icon' => 'trash.gif',
                                           'text' => '<a href="'.$this->url_for('siteinfo/delete/'.$this->currentrubric.'/'.$this->currentdetail).'">'._('Seite löschen').'</a>');
            }
        }
        $infobox_actions[] = array('icon' => 'cont_folder_add.gif',
                                   'text' => '<a href="'.$this->url_for('siteinfo/new').'">'._('neue Rubrik anlegen').'</a>');
        if ($this->currentrubric > 0) {
            $infobox_actions[] = array('icon' => 'cont_folder4.gif',
                                       'text' => '<a href="'.$this->url_for('siteinfo/edit/'.$this->currentrubric).'">'._('Rubrik bearbeiten').'</a>');
            $infobox_actions[] = array('icon' => 'trash.gif',
                                       'text' => '<a href="'.$this->url_for('siteinfo/delete/'.$this->currentrubric).'">'._('Rubrik löschen').'</a>');
        }
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
        $this->output = $this->si->get_detail_content_processed($this->currentdetail);
    }

    function new_action ($givenrubric=NULL)
    {
        global $view, $dynstradd;
        if($givenrubric===NULL){
            $dynstradd['rubric_new'] = array('topKat' => '', 
                                             'name'   => _('neue Rubrik'), 
                                             'link'   => '#',
                                             'active' => FALSE);
            $view = "rubric_new";
            $this->edit_rubric = TRUE;
        } else {        
            $dynstradd['detail_new'] = array('topKat' => 'r'.$this->currentrubric, 
                                             'name'   => _('neue Seite'), 
                                             'link'   => '#',
                                             'active' => FALSE);
            $this->rubrics = $this->si->get_all_rubrics();
            $view = "detail_new";
        }
    }

    function edit_action ($givenrubric=NULL, $givendetail=NULL)
    {
        if (is_numeric($givendetail)) {
            $this->rubrics = $this->si->get_all_rubrics();
            $this->rubric_id = $this->si->rubric_for_detail($this->currentdetail);
            $this->detail_name = $this->si->get_detail_name($this->currentdetail);
            $this->content = $this->si->get_detail_content($this->currentdetail);
        } else {
            $this->edit_rubric = TRUE;
            $this->rubric_id = $this->currentrubric;
       }
        $this->rubric_name = $this->si->rubric_name($this->currentrubric);
    }

    function save_action ()
    {
        $detail_name = remove_magic_quotes($_POST['detail_name']);
        $rubric_name = remove_magic_quotes($_POST['rubric_name']);
        $content = remove_magic_quotes($_POST['content']);
        if (isset($_POST['rubric_id'])) {
            $rubric_id = (int) $_POST['rubric_id'];
            if (isset($_POST['detail_id'])) {
                $detail_id = (int) $_POST['detail_id'];
                list($rubric, $detail) = $this->si->save("update_detail", array("rubric_id" => $rubric_id,
                                                                                "detail_name" => $detail_name,
                                                                                "content" => $content,
                                                                                "detail_id" => $detail_id));
            } else {
                if (isset($_POST['content'])) {
                list($rubric, $detail) = $this->si->save("insert_detail", array("rubric_id" => $rubric_id,
                                                                                "detail_name" => $detail_name,
                                                                                "content" => $content));
                } else {
                    list($rubric, $detail) = $this->si->save("update_rubric", array("rubric_id" => $rubric_id,
                                                                         "rubric_name" => $rubric_name));
                }
            }
        } else {
            list($rubric, $detail) = $this->si->save("insert_rubric", array("rubric_name" => $rubric_name));
        }
        $this->redirect('siteinfo/show/'.$rubric.'/'.$detail);
    }

    function delete_action ($givenrubric=NULL, $givendetail=NULL, $execute=FALSE)
    {
        $db = DBManager::get();
        if ($execute) {
            if ($givendetail == "all") {
                $this->si->delete("rubric", $this->currentrubric);
                $this->redirect('siteinfo/show/');
            } else {
                $this->si->delete("detail", $this->currentdetail);
                $this->redirect('siteinfo/show/'.$this->currentrubric);
            }
        } else {
            if (is_numeric($givendetail)) {
                $this->detail = TRUE;
            }
            $this->output = $this->si->get_detail_content_processed($this->currentdetail);
        }
    }
}
?>

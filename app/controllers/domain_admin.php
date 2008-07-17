<?php
/*
 * domain_admin.php - user domain admin controller
 *
 * Copyright (c) 2008  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/visual.inc.php';
require_once 'lib/classes/UserDomain.php';

class DomainAdminController extends Trails_Controller
{
    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        global $perm, $template_factory, $CURRENT_PAGE;
        global $_language_path, $_language;

        # open session
        page_open(array('sess' => 'Seminar_Session',
                        'auth' => 'Seminar_Auth',
                        'perm' => 'Seminar_Perm',
                        'user' => 'Seminar_User'));

        # set up language prefs
        $_language_path = init_i18n($_language);

        # user must have root permission
        $perm->check('root');

        # set page title and navigation
        $layout = $template_factory->open('layouts/base_without_infobox');
        $layout->set_attribute('tabs', 'links_admin');
        $layout->set_attribute('reiter_view', 'userdomains');
        $this->set_layout($layout);

        $CURRENT_PAGE = _('Nutzerdomänen');

        # fetch user domain
        $this->domains = UserDomain::getUserDomains();
    }

    /**
     * common tasks for all actions
     */
    function after_filter ($action, $args)
    {
        page_close();
    }

    /**
     * Display the list of user domains.
     */
    function show_action ()
    {
    }

    /**
     * Create a new user domain.
     */
    function new_action ()
    {
        $this->render_action('edit');
    }

    /**
     * Edit an existing user domain.
     */
    function edit_action ($id)
    {
        $this->edit_id = $id;
    }

    /**
     * Save changes to a user domain.
     */
    function save_action ()
    {
        try {
            $domain = new UserDomain($_REQUEST['id']);
            $domain->setName($_REQUEST['name']);
            $domain->store();
        } catch (Exception $ex) {
            $this->error_msg = $ex->getMessage();
        }

        $this->domains = UserDomain::getUserDomains();
        $this->render_action('show');
    }

    /**
     * Delete an existing user domain.
     */
    function delete_action ($id)
    {
        $domain = new UserDomain($id);
        $domain->delete();

        $this->domains = UserDomain::getUserDomains();
        $this->render_action('show');
    }
}
?>

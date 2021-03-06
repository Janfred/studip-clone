<?php
# Lifter010: TODO

/*
 * Copyright (C) 2009 - Marcus Lunzenauer <mlunzena@uos.de>
 * André Noack <noack@data-quest.de>
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
class Institute_AvatarController extends AuthenticatedController
{

    # see Trails_Controller#before_filter
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->institute_id = current($args);
        if ($this->institute_id === '' || !in_array(get_object_type($this->institute_id), words('inst fak'))
            || !$GLOBALS['perm']->have_studip_perm("admin", $this->institute_id)) {
            throw new AccessDeniedException();
        }

        $this->body_id = 'custom_avatar';
        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . _('Bild ändern'));

        Navigation::activateItem('/admin/institute/details');
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
            InstituteAvatar::getAvatar($this->institute_id)->createFromUpload('avatar');
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            $this->render_action("update");
        }
    }

    /**
     * This method is called to remove an avatar for a course.
     *
     * @return void
     */
    public function delete_action()
    {
        InstituteAvatar::getAvatar($this->institute_id)->reset();

        PageLayout::postMessage(MessageBox::success(_('Das Infobild wurde gelöscht.')));
        $this->redirect(URLHelper::getUrl('dispatch.php/institute/basicdata/index/' . $this->institute_id));
    }
}

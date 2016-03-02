<?php

interface AdminCourseAction
{
    public function getAdminActionURL();

    /**
     * Returns necessary attributes for the admin action link, e.g. "[data-dialog=<size etc.>]"
     * for opening the action in a modal dialog.
     * @return null|string: null, if no attributes need to be set, the attribute value otherwise.
     */
    public function getAdminActionAttributes();

    /**
     * Defines if the Plugin wants to use the multimode to edit multiple courses at once.
     * @return boolean|string: false, if multimode is not important, else true. But you can also set it to a string (means true) that is the label of the send-button like _("Veranstaltungen archivieren")
     */
    public function useMultimode();

    /**
     * Returns a template for a small table cell (the <td> wraps the template-content)
     * in which you can set inputs and links to display special actions for an admin
     * for the given course.
     * @param $course_id
     * @param null $values
     * @return null|Flex_Template
     */
    public function getAdminCourseActionTemplate($course_id, $values = null);
}

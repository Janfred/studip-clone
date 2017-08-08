<?php

interface AdminCourseContents
{
    /**
     * @return array : array('index' => _("Translated display name"))
     */
    public function adminAvailableContents();

    /**
     * @param Course $course
     * @param $index
     * @return Flexi_Template | String
     */
    public function adminAreaGetCourseContent($course, $index);
}

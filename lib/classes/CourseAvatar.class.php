<?php

/*
 * Copyright (C) 2009 - Marcus Lunzenauer (mlunzena@uos)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'lib/classes/Avatar.class.php';


/**
 * This class represents the avatar of a course.
 *
 * @package    studip
 * @subpackage lib
 *
 * @author    Marcus Lunzenauer (mlunzena@uos)
 * @copyright (c) Authors
 * @since     1.10
 */
class CourseAvatar extends Avatar
{

    /**
     * Returns an avatar object of the appropriate class.
     *
     * @param  string  the course's id
     *
     * @return mixed   the course's avatar.
     */
    static function getAvatar($course_id)
    {
        return new CourseAvatar($course_id);
    }

    /**
     * Returns an avatar object for "nobody".
     *
     * @return mixed   the course's avatar.
     */
    static function getNobody()
    {
        return new CourseAvatar('nobody');
    }

    /**
     * Returns the URL to the courses' avatars.
     *
     * @return string     the URL to the avatars
     */
    function getAvatarDirectoryUrl()
    {
        return $GLOBALS['DYNAMIC_CONTENT_URL'] . "/course";
    }


    /**
     * Returns the file system path to the courses' avatars
     *
     * @return string      the file system path to the avatars
     */
    function getAvatarDirectoryPath()
    {
        return $GLOBALS['DYNAMIC_CONTENT_PATH'] . "/course";
    }

    /**
     * Constructs a desired HTML image tag for a Avatar.
     *
     * @param string  one of the constants Avatar::(NORMAL|MEDIUM|SMALL)
     *
     * @return string returns the HTML image tag
     */
    function getImageTag($size = Avatar::MEDIUM)
    {
        return sprintf('<img src="%s" align="middle" class="avatar-%s course-%s"/>',
                       $this->getURL($size), $size, $this->user_id);
    }

    /**
     * Return the dimension of a size
     *
     * @param  string     the dimension of a size
     *
     * @return array      a tupel of integers [width, height]
     */
    function getDimension($size)
    {
        $dimensions = array(
          Avatar::NORMAL => array(200, 250),
          Avatar::MEDIUM => array( 80, 100),
          Avatar::SMALL  => array( 20,  25));
        return $dimensions[$size];
    }
}


<?php

/*
 * UserPic.class.php - UserPic class
 *
 * Copyright (C) 2007 - André Klaßen (aklassen@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * UserPic Class
 *
 * @package    studip
 * @subpackage lib
 *
 * @author    André Klaßen (aklassen@uos)
 * @copyright (c) Authors
 * @since     1.6
 */
class UserPic {

  /**
   * This constant stands for the maximal size of a user picture.
   */
  const NORMAL = 'normal';

  /**
   * This constant stands for a medium size of a user picture.
   */
  const MEDIUM = 'medium';

  /**
   * This constant stands for an icon size of a user picture.
   */
  const SMALL  = 'small';


  /**
   * This constant represents the maximal size of a user picture in bytes.
   */
  const MAX_FILE_SIZE = 102400;


  /**
   * Constructs a new UserPic object belonging to a user with the given id.
   *
   * @param  string  the user's' id
   *
   * @return void
   */
  function __construct($user_id) {
    $this->user_id = $user_id;
  }


  /**
   * Returns the file name of a user's picture.
   *
   * @param  string  the user's id
   * @param  string  one of the constants UserPic::(NORMAL|MEDIUM|SMALL)
   * @param  string  an optional extension of the user's picture
   *
   * @return string  the absolute file path to the user's picture
   */
  private static function getFilename($id, $size, $ext = 'png') {
    return sprintf('%s/user/%s_%s.%s',
      $GLOBALS['DYNAMIC_CONTENT_PATH'], $id, $size, $ext);
  }


  /**
   * Returns the URL of a user's picture.
   *
   * @param  string  the user's id
   * @param  string  one of the constants UserPic::(NORMAL|MEDIUM|SMALL)
   * @param  string  an optional extension of the user's picture
   *
   * @return string  the URL to the user's picture
   */
  private static function getURL($id, $size, $ext = 'png') {
    return sprintf('%s/user/%s_%s.%s',
      $GLOBALS['DYNAMIC_CONTENT_URL'], $id, $size, $ext);
  }


  /**
   * Returns whether a user has uploaded a custom picture.
   *
   * @return boolean  returns TRUE if the user customized her picture, FALSE
   *                  otherwise.
   */
  function is_customized() {
    return file_exists(self::getFilename($this->user_id, UserPic::MEDIUM));
  }


  /**
   * Constructs a desired HTML image tag for a UserPic.
   *
   * @param string  one of the constants UserPic::(NORMAL|MEDIUM|SMALL)
   * @param string  the tooltip for the user's picture
   *
   * @return string returns the HTML image tag
   */
  function getImageTag($size = UserPic::MEDIUM, $tooltip = '') {

    // check wether userpic is avaible if not use corresponding nobody pic
    $filename = $this->is_customized()
      ? self::getURL($this->user_id, $size)
      : self::getURL('nobody', $size);

    return sprintf('<img src="%s" %s align="middle" />',
      $filename, tooltip($tooltip));
  }


  /**
   * Creates all the different sized thumbnails for an uploaded file.
   *
   * @param  string  the key of the uploaded file,
   *                 see documentation about $_FILES
   *
   * @return void
   *
   * @throws several Exceptions if the uploaded file does not satisfy the
   *         requirements
   */
  public function createFromUpload($userfile) {

    // Bilddatei ist zu groß
    if ($_FILES[$userfile]['size'] > self::MAX_FILE_SIZE) {
      throw new Exception(sprintf(_("Die hochgeladene Bilddatei ist %s KB groß. Die maximale Dateigröße beträgt %s KB!"),
                                  round($_FILES[$userfile]['size'] / 1024),
                                  self::MAX_FILE_SIZE));
    }

    // keine Datei ausgewählt!
    if (!$_FILES[$userfile]['name']) {
      throw new Exception(_("Sie haben keine Datei zum Hochladen ausgewählt!"));
    }

    // get extension
    $pathinfo = pathinfo($_FILES[$userfile]['name']);
    $ext = $pathinfo['extension'];

    // passende Endung ?
    if (!in_array($ext, _w('jpg jpeg gif png'))) {
      throw new Exception(sprintf(_("Der Dateityp der Bilddatei ist falsch (%s). Es sind nur die Dateiendungen .gif, .png, .jpeg und .jpg erlaubt!"),
                                  $ext));
    }

    // na dann kopieren wir mal...
    $filename = sprintf('%s/user/$s.%s',
      $GLOBALS['DYNAMIC_CONTENT_PATH'], $this->user_id, $ext);

    if (!@move_uploaded_file($_FILES[$userfile]['tmp_name'], $filename)) {
      throw new Exception(_("Es ist ein Fehler beim Kopieren der Datei aufgetreten. Das Bild wurde nicht hochgeladen!"));
    }

    // set permissions for uploaded file
    chmod($filename, 0666 & ~umask());

    $this->createFrom($filename);
    unlink($filename);
  }


  /**
   * Creates thumbnails from an image.
   *
   * @param string  filename of the image to create thumbnails from
   *
   * @return void
   */
  public function createFrom($filename) {

    if (!extension_loaded('gd')) {
      throw new Exception(_("Es ist ein Fehler beim Bearbeiten des Bildes aufgetreten."));
    }

    $this->resize(UserPic::NORMAL, $filename);
    $this->resize(UserPic::MEDIUM, $filename);
    $this->resize(UserPic::SMALL,  $filename);
  }

  /**
   * Removes all uploaded pictures of a user.
   *
   * @return void
   */
  function reset() {
    @unlink(self::getFilename($this->user_id, UserPic::NORMAL));
    @unlink(self::getFilename($this->user_id, UserPic::MEDIUM));
    @unlink(self::getFilename($this->user_id, UserPic::SMALL));
  }


  /**
   * Create from an image thumbnails of a specified size.
   *
   * @param string  the size of the thumbnail to create
   * @param string  the filename of the image to make thumbnail of
   *
   * @return void
   */
  function resize($size, $filename) {

    $sizes = array();
    $sizes[UserPic::NORMAL] = array(200, 250);
    $sizes[UserPic::MEDIUM] = array( 80, 100);
    $sizes[UserPic::SMALL]  = array( 20,  25);
    list($thumb_width, $thumb_height) = $sizes[$size];

    list($width, $height, $type) = getimagesize($filename);

    # create image resource from filename
    $lookup = array(
      IMAGETYPE_GIF  => "imagecreatefromgif",
      IMAGETYPE_JPEG => "imagecreatefromjpeg",
      IMAGETYPE_PNG  => "imagecreatefrompng");
    if (!isset($lookup[$type])) {
      throw new Exception(_("Der Typ des Bilds wird nicht unterstützt."));
    }
    $image = $lookup[$type]($filename);


    # resize image if needed
    if ($height > $thumb_height || $width > $thumb_width) {
      $factor = min($thumb_width / $width, $thumb_height / $height);
      $resized_width  = round($width  * $factor);
      $resized_height = round($height * $factor);
    }

    else {
      $resized_width  = $width;
      $resized_height = $height;
    }

    $image = self::imageresize($image, $type, $width, $height,
      $resized_width, $resized_height);

    $i = imagecreatetruecolor($thumb_width, $thumb_height);
    $color = imagecolorallocatealpha($i, 0, 0, 0, 127);
    imagefill($i, 0, 0, $color);
    imagesavealpha($i, true);

    // center the new image
    $ypos = intval($thumb_height - $resized_height) >> 1;
    $xpos = intval($thumb_width - $resized_width) >> 1;

    imagecopy($i, $image, $xpos, $ypos, 0, 0, $resized_width, $resized_height);

    imagepng($i, self::getFilename($this->user_id, $size));
  }


  private function imageresize($image, $type,
                               $current_width, $current_height,
                               $width, $height) {

    $image_resized = imagecreatetruecolor($width, $height);

    if ($type == IMAGETYPE_GIF ||
        $type == IMAGETYPE_PNG) {

      // If we have a specific transparent color, allocate the same in the new
      // image resource and completely fill it with that one
      $index = imagecolortransparent($image);
      if ($index >= 0) {
        $color = @imagecolorsforindex($image, $index);
        $index = imagecolorallocate($image_resized,
          $color['red'], $color['green'], $color['blue']);
        imagefill($image_resized, 0, 0, $index);
        imagecolortransparent($image_resized, $index);
      }

      // Always make a transparent background color for PNGs that don't have
      // one allocated already, turn off transparency blending (temporarily)
      // create a new transparent color for image, completely fill the new
      // image with it and restore transparency blending
      else if ($type == IMAGETYPE_PNG) {
        imagealphablending($image_resized, FALSE);
        $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
        imagefill($image_resized, 0, 0, $color);
        imagesavealpha($image_resized, true);
      }
    }
    imagecopyresampled($image_resized, $image, 0, 0, 0, 0,
                       $width, $height, $current_width, $current_height);

    return $image_resized;
  }
}


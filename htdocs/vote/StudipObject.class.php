<?php
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipObject.class.php
//
// Class to provide basic properties of an object in Stud.IP
//
// Copyright (c) 2003 Alexander Willner <mail@AlexanderWillner.de>
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+


# Include all required files ================================================ #
require_once ($ABSOLUTE_PATH_STUDIP."vote/vote.config.php");
# =========================================================================== #



# Define all required constants ============================================= #
/**
 * When a normal error occurs
 * @access public
 * @const ERROR_NORMAL
 */
define ("ERROR_NORMAL", 0);

/**
 * When a critical error occurs
 * @access public
 * @const ERROR_CRITICAL
 */
define ("ERROR_CRITICAL", 1);
# =========================================================================== #



/**
 * StudipObject.class.php
 *
 * Class to provide basic properties of an object in Stud.IP
 *
 * @author      Alexander Willner <mail@alexanderwillner.de>
 * @version     $Id$
 * @access      public
 * @package     vote
 * @modulegroup vote_modules
 */
class StudipObject {

# Define all required variables ============================================= #
   /**
    * Holds the code and description of an internal error
    * @access   private
    * @var      array $errorArray
    */
   var $errorArray;

   /**
    * Holds the emailadress of the author
    * @access   private
    * @var      array $authorEmail
    */
   var $authorEmail;

   /**
    * Holds the name of the author
    * @access   private
    * @var      array $authorName
    */
   var $authorName;

   /**
    * Holds the type of object. See INSTANCEOF_*
    * @access   private
    * @var      string $instanceof
    */
   var $instanceof;

   
# =========================================================================== #



# Define constructor and destructor ========================================= #
   /**
    * Constructor
    * @access   public
    */
   function StudipObject () {
 
      /* For good OOP: Set errorhandler and destruktor --------------------- */
      restore_error_handler ();
      $this->oldErrorhandler = set_error_handler (array (&$this, 
							 'errorHandler'));
      register_shutdown_function (array (&$this, 'finalize'));
      $this->instanceof = INSTANCEOF_OBJECT;
      /* ------------------------------------------------------------------- */

      /* Set default values ------------------------------------------------ */
      $this->errorArray       = array ();
      /* ------------------------------------------------------------------- */
   }


   /**
    * Destructor. Should be used every time after an object is not longer
    * usefull!
    * @access   public
    */
   function finalize () {
      restore_error_handler ();
   }
# =========================================================================== #



# Define public functions =================================================== #
   /**
    * Sets the emailaddress of the author
    * @access  public
    * @param   string $email The emailaddress
    */
   function setAuthorEmail ($email) {
      $this->authorEmail = $email;
   }
   
   /**
    * Gets the emailaddress of the author
    * @access  public
    * @return  string The emailaddress
    */
   function getAuthorEmail () {
      return $this->authorEmail;
   }

   /**
    * Sets the name of the author
    * @access  public
    * @param   string $name The name
    */
   function setAuthorName ($name) {
      $this->authorNmae = $name;
   }
   
   /**
    * Gets the name of the author
    * @access  public
    * @return  string The name
    */
   function getAuthorName () {
      return $this->authorName;
   }
   
   /**
    * Gets the type of object
    * @access  public
    * @return  string The type of object. See INSTANCEOF_*
    */
   function instanceof () {
      return $this->instanceof;
   }

   /**
    * Gives the internal errorcode
    * @access public
    * @return boolean True if an error exists
    */
   function isError () {
      return (count($this->errorArray) != 0);
   }

   /**
    * Gives the codes and descriptions of the internal errors
    * @access  public
    * @return  array  The errors as an Array like "1" => "Couldn´t open DB"
    */
   function getErrors () {
      return $this->errorArray;
   }

   /**
    * Resets the errorcodes and descriptions
    * @access public
    */
   function resetErrors () {
      $this->errorArray = array ();
   }
# =========================================================================== #



# Define static functions =================================================== #

# =========================================================================== #



# Define private functions ================================================== #
   /**
    * Sets the errorcode (internal)
    * @access  private
    * @param   integer $errcode    The code of the error
    * @param   string  $errstring  The description of the error
    * @param   integer $errline    The line
    * @param   string  $errfile    The file
    * @param   integer $errtype    Defines wheter the error is critical
    */
   function throwError ($errcode, $errstring, $errline = 0, $errfile = 0, 
			$errtype = ERROR_NORMAL) {
      if (!is_array ($this->errorArray))
	 $this->errorArray = array ();

      array_push ($this->errorArray, 
		  array ("code" => $errcode,
			 "string" => $errstring,
			 "file" => $errfile,
			 "line" => $errline,
			 "type" => $errtype)
		  );
      if ($errtype = ERROR_CRITICAL) {
	 /*
	 @mail ($this->getAuthorEmail (),
		"Critical error in Stud.IP",
		"Hello ".$this->getAuthorName ()."\n\n".
		"there is an error in file ".$errfile." ".
		"in line ".$errline.". \n".
		"The code is ".$errcode."\n".
		"Description: ".$errstring.".\n\n\n".
		"regards, *a Studip-Object*\n\n");
	 */
      }
   }

   /**
    * Sets the errorcode from other classes (internal)
    * @access  private
    * @param   object   $class   The class with the error
    */
   function throwErrorFromClass (&$class) {
      $this->errorArray = $class->getErrors ();
      $class->resetErrors ();
   }

   /**
    * An errorHandler for PHP-errors
    * @access  private
    * @param   int    $no   Errornumber
    * @param   string $str  Errordescription
    * @param   string $file Filename
    * @param   int    $line Linenumber
    * @param   array  $ctx  All variables
    */
   function errorHandler ($no, $str, $file, $line, $ctx) {
      if (!($no & error_reporting ())) return;
      $this->throwError ($no, $str, $line, $file, ERROR_CRITICAL);
      echo createErrorReport ($this, "Schwerer PHP-Laufzeitfehler");
      return;
   }
# =========================================================================== #

}
?>

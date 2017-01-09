<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

use Studip\Button, Studip\LinkButton;

/**
 * class to generate links to ILIAS 5.2
 *
 * This class contains methods to generate links to ILIAS 5.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias5ConnectedLink
 * @package    ELearning-Interface
 */
class Ilias5ConnectedLink extends Ilias4ConnectedLink
{
    /**
     * constructor
     *
     * init class.
     * @access
     * @param string $cms system-type
     */
    function __construct($cms)
    {
        parent::__construct($cms);
        $this->cms_link = "ilias5_referrer.php";
    }
}
?>
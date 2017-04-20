<?php

/**
 * @author  Elmar Ludwig <elmar.ludwig@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldTextmarkupEntry extends DataFieldTextareaEntry
{
    /**
     * Returns the input elements as html for this datafield
     *
     * @param String $name      Name prefix of the associated input
     * @param Array  $variables Additional variables
     * @return String containing the required html
     */
    public function getHTML($name = '', $variables = array())
    {
        $this->template = 'textmarkup.php';
        return parent::getHTML($name, $variables);
    }

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($submitted_value)
    {
        $this->setValue(Studip\Markup::purifyHtml($submitted_value));
    }

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        if ($entities) {
            return formatReady($this->getValue());
        }

        return $this->getValue();
    }
}

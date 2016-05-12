<?php
/**
 * Model for a select group element of the select widget.
 *
 * @author  Stefan Osterloh <s.osterloh@uni-oldenburg.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.5
 */
class SelectGroupElement extends WidgetElement
{
    protected $label;
    protected $elements;
    /**
     * Constructs the element with an id (value of the according option
     * element) and a label (text content of the according option
     * element).
     *
     * @param String $label  Label content of the element
     * @param Array $elements SelectElement-Objects for Optgroup
     */
    public function __construct($label, $elements = array())
    {
        $this->label  = $label;
        $this->elements = $elements;
    }

    /**
     * Sets the label/text content of the element.
     *
     * @param String $label Label/text content of the element
     */
    public function setLabel($label) 
    {
        $this->label = $label;
    }

    /**
     * Sets the SelectElement-Objects for this Element
     * 
     * @param Array $elements
     */
    public function setElements($elements) 
    {
        $this->elements = $elements;
    }
    
    /**
     * 
     * @return Array with SelectElements
     */
    public function getElements()
    {
        return $this->elements;
    }
    /**
     * adds a single element of type SelectElement
     * 
     * @param SelectElement $element
     */
    public function addElement(SelectElement $element)
    {
        $this->elements[] = $element;
    }
    
    /**
     * Returns the label/text content of the element. The label is stripped
     * of all leading whitespace.
     *
     * @return String Label/text content of the element
     * @see SelectElement::getIndentLevel
     */
    public function getLabel() 
    {
        return ltrim($this->label);
    }

    /**
     * Returns the indentation level of the element based on the number
     * of leading whitespace characters. This is used to indent the label
     * in the according option element.
     *
     * @return int Indentation level
     */
    public function getIndentLevel()
    {
        return strlen($this->label) - strlen(ltrim($this->label));
    }

    /**
     * Renders the element (well, not really - this returns it's label).
     *
     * @return String The label/text content of the element
     * @todo   What should this method actually do?
     */
    public function render()
    {
        return $this->getLabel();
    }
}
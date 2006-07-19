<?php
/**
 * An object for displaying just text. 
 * @author Dennis Reil, <Dennis.Reil@offis.de>
 * @version $Revision$
 */
class EvaluationText extends EvaluationGroup  {
	
	/**
    * Constructor
    * @access   public
    * @param    string   $objectID       The ID of an existing question
    * @param    object   $parentObject   The parent object if exists
    * @param    integer  $loadChildren   See const EVAL_LOAD_*_CHILDREN
    */
   function EvaluationText ($objectID = "", $parentObject = NULL, 
                                $loadChildren = EVAL_LOAD_NO_CHILDREN) {
   	 parent::EvaluationGroup($objectID,$parentObject,$loadChildren);
   	 $this->childType = "EvaluationText";
   }
   
   function setChildType($type){
   	  // do nothing
   }
	
}
?>
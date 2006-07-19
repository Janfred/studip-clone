<?
/**
 * Abstract implementation for the user mapping used by StudIPAuthCAS
 * @author Dennis Reil, <Dennis.Reil@offis.de>
 * @version $Revision$
 * $Id$
 */
class CASAbstractUserDataMapping {
	
	function getUserData($key,$username){
		// has to be implemented in derived class	
		return "";
	}
}
?>
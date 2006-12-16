<?
/**
* Adapter for using php5 ext:soap with Ilias3Soap
*
*
* @author	Andre Noack <noack@data-quest.de>
* @access	public
* @package	ELearning-Interface
*/
class StudipSoapClient
{
	var $soap_client;
	var $error;

	function StudipSoapClient($path)
	{
		try{
			$this->soap_client = new SoapClient(dirname(__FILE__) . '/ilias.wsdl', array('trace' => 0));
		} catch (SoapFault $fault){
			$this->error = "<b>Soap Constructor Error</b><br>" . $fault->faultcode . ": ".$fault->faultstring."<br><br>";
		}
	}

	function call($method, $params)
	{
		$this->faultstring = "";
		try{
			$result = $this->soap_client->__soapCall($method, $params);
		} catch  (SoapFault $fault){
			$this->faultstring = $fault->faultstring;
			if ($this->faultstring != "Session not valid")
				$this->error .= "<b>" . sprintf(_("Soap-Fehler, Funktion \"%s\":"), $method) . "</b> " . $fault->faultstring . " (" .  $fault->faultcode . ")<br>".print_r($params,1);
				echo $this->error;
				$this->soap_client->fault = true;
			return false;
		}
		if (is_object($result)) $result = (array)$result;
		if (is_array($result)){
			foreach($result as $index => $one){
				if (is_object($one)) $result[$index] = (array)$one;
				if (is_array($result[$index])){
					//hmmm
				} else {
					$result[$index] = utf8_decode($result[$index]);
				}
			}
		} else {
			$result = utf8_decode($result);
		}
		$this->soap_client->fault = false;
		return $result;
	}

	function getError()
	{
		 $error = $this->error;
		 $this->error = "";
		 if ($error != "")
			 return $error;
		else
			return false;
	}
}
?>
<?
class StudipSoapClient
{
	var $soap_client;
	var $error;
	var $log_queries = 1;
	var $log_file = "/tmp/studipqueries.sql";

	function StudipSoapClient($path)
	{
		global $ABSOLUTE_PATH_STUDIP, $RELATIVE_PATH_SOAP, $SOAP_ENABLE;
		require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_SOAP."/lib/nusoap.php");

		$this->soap_client = new soapclient($path, true);

		$err = $this->soap_client->getError();
		if ($err)
			$this->error = "<b>Soap Constructor Error</b><br>" . $err . "<br><br>";
	}

	function call($method, $params)
	{
<<<<<<< StudipSoapClient.class.php
		if ($this->log_queries) {
			$time = DB_Sql::getMsTime();
		}
		
		/*$cache_id = md5('#' . get_class($this) . ': ' . $method . ' ' .serialize($params));
		if (isset($_SESSION['soap_cache'][$cache_id])){
			return $_SESSION['soap_cache'][$cache_id];
		}
		*/
=======
		$this->faultstring = "";
>>>>>>> 1.3
		$result = $this->soap_client->call($method, $params);
		
		if ($this->log_queries) {
			$Query_String = '#' . get_class($this) . ': ' . $method . ' ' .serialize($params). ' ' . $this->soap_client->fault;
			$time = DB_Sql::getMsTime()-$time;
			if ($GLOBALS['SQL_LOG_SCRIPT_NAME'] != realpath($_SERVER['SCRIPT_FILENAME'])){
				$GLOBALS['SQL_LOG_SCRIPT_NAME'] = realpath($_SERVER['SCRIPT_FILENAME']);
			$fp = fopen($this->log_file,"w");
			fputs($fp,"# " . $GLOBALS['SQL_LOG_SCRIPT_NAME'] . "   " . strftime("%c",time()) . "\n");
			} else {
				$fp = fopen($this->log_file,"a");
			}
			fputs($fp,$Query_String."\nTime:".$time."\n");
			fclose($fp);
		}
	
		if ($this->soap_client->fault) 
		{
			$this->faultstring = $result["faultstring"];
			if ($this->faultstring != "Session not valid")
				$this->error .= "<b>" . sprintf(_("Soap-Fehler, Funktion \"%s\":"), $method) . "</b> " . $result["faultstring"] . " (" . $result["faultcode"] . ")<br>"; //.implode($params,"-");
		}
		else 
		{
			$err = $this->soap_client->getError();
			if ($err)
				$this->error .= "<b>" . sprintf(_("Soap-Error, Funktion \"%s\":"), $method) . "</b> " . $err . "<br>"; //.implode($params,"-") . htmlspecialchars($this->soap_client->response, ENT_QUOTES);
			else
				return ($_SESSION['soap_cache'][$cache_id] = $result);
		}
		echo $this->error;
		return false;
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

<?php
$config['title'] = "StudIP Messenger Sidebar";
$config['panelName'] = "StudIP Messenger";
$config['panelURL'] = "http://test.studip.de/sidebar_im.php";
$config['panelConfigURL'] = "http://www.studip.de";
$config['addPanelMsg'] = "StudIP Messenger als Sidebar hinzufügen";
?>
<?php
class sideBarAdd {
	var $panelName, $panelURL, $panelConfigURL, $addPanelMsg, $browser;
	
	function sideBarAdd ($panelName, $panelURL, $panelConfigURL, $addPanelMsg, $browser) {
		$this->panelName = $panelName;
		$this->panelURL = $panelURL;
		$this->panelConfigURL = $panelConfigURL;
		$this->addPanelMsg = $addPanelMsg;
		$this->browser = $browser;
	}
	
	function JS_String () {
		if ($this->browser == "mozcomp") {
			$js = "<script language=\"JavaScript\">\n   function addPanel() {\n";
			$js .= "      if ((typeof window.sidebar == \"object\") && (typeof window.sidebar.addPanel == \"function\"))\n      {\n";
			$js .= "         window.sidebar.addPanel (\"" . $this->panelName . "\",\n"; 
			$js .= "         \"" . $this->panelURL . "\",\"" . $this->panelConfigURL . "\");\n";
			$js .= "      }\n      else\n      {\n";
			$js .= "         var rv = window.confirm (\"Diese Seite ist für Mozilla, sowie Netscape ab Version ab 6 optimiert.\"\n";
			$js .= "            + \"Möchten die Mozilla jetzt downloaden?\");\n";
			$js .= "         if (rv)\n            document.location.href = \"http://www.mozilla.org/download/\";\n";
			$js .= "      }\n   }\n</script>\n";
			return $js;
		} else {
			return FALSE;
		}
	}
	
	function Add_String () {
		if ($this->browser == "mozcomp") {
			return "<a href=\"javascript:addPanel();\">" .  $this->addPanelMsg . "</a>\n";
		} elseif ($this->browser == "opera") {
			return "<a href=\"" .  $this->panelURL . "\" rel=\"sidebar\" title=\"" .  $this->panelname . "\">" .  $this->addPanelMsg . "</a>\n";
		} else {
			return FALSE;
		}
	}
	function getSet_panelURL ($panelURL) {
		if (!$panelURL) {
			return $this->panelURL;
		} else {
			$this->panelURL = $panelURL;
		}
	}

	function getSet_panelConfigURL ($panelConfigURL) {
		if (!$panelConfigURL) {
			return $this->panelConfigURL;
		} else {
			$this->panelConfigURL = $panelConfigURL;
		}
	}

	function getSet_addPanelMsg ($addPanelMsg) {
		if (!$addPanelMsg) {
			return $this->addPanelMsg;
		} else {
			$this->addPanelMsg = $addPanelMsg;
		}
	}

	function getSet_browser ($browser) {
		if (!$browser) {
			return $this->browser;
		} else {
			$this->browser = $browser;
		}
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php
echo "<title>" . $config['title'] . "</title>"
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php
$sidebar = new sideBarAdd ($config['panelName'], $config['panelURL'], $config['panelConfigURL'], $config['addPanelMsg'], "mozcomp");
echo $sidebar->JS_String();
?>
</head>
<body>
<?php
echo $sidebar->Add_String(). "<br>";
$sidebar->getSet_browser("opera");
echo $sidebar->Add_String();
?>
</body>
</html>

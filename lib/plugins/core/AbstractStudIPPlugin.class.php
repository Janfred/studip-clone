<?php
/**
 * Abstract class for a plugin in Stud.IP.
 * Don't use this as a base class for creating your own plugin. Look at
 * AbstractStudIPStandardPlugin, AbstractStudIPSystemPlugin or AbstractStudIPAdministrationPlugin
 * for creating a plugin.
 *
 * @author Dennis Reil, <Dennis.Reil@offis.de>
 * @version $Revision$
 * @see AbstractStudIPStandardPlugin, AbstractStudIPSystemPlugin, AbstractStudIPAdministrationPlugin
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPPlugin {
	var $pluginname;
	var $pluginid;
	var $pluginpath;
	var $basepluginpath; // the pluginpath without the plugins_directory


	var $pluginadmininfo;
	var $pluginiconname;
	var $user;
	var $helpinfo;

	var $navigation;
	var $activated;
	var $environment;
	var $enabled; // plugin available in system
	var $navposition; // the position in the navigation menü
	var $dependentonplugin; // this plugin depends on another plugin


	/**
	  Constructor
	*/
	function AbstractStudIPPlugin(){
		$this->pluginname = "";
		$this->pluginid = "-1";
		$this->pluginadmininfo = null;
		$this->pluginiconname = NULL;
		$this->helpinfo = null;
		$this->navigation = null;
		$this->activated = false;
		$this->user=new StudIPUser();
		$this->environment=null;
		$this->pluginpath = "";
		$this->basepluginpath = "";
		$this->enabled = false;
		$this->navposition = 99999; // a high value to put it at the end of the list
		$this->dependentonplugin = false;
	}

	function getPluginclassname(){
		return strtolower(get_class($this));
	}

	/**
	 * This function is called by the plugin engine directly before uninstallation.
	 * Normally a plugin would drop all tables created and used by the plugin.
	 *
	 */
	function prepareUninstallation(){
		$manifest = PluginEngine::getPluginManifest($this->environment->getBasepath() . "/" . $this->getPluginpath());
		if (is_array($manifest)){
			if (isset($manifest["uninstalldbscheme"])) {
				$schemafile = $this->getPluginpath() . "/" . $manifest["uninstalldbscheme"];
				$conn = PluginEngine::getPluginDatabaseConnection();
				// this should use file_get_contents() in PHP 5
				$statements = split(";[[:space:]]*\n", implode('', file($schemafile)));
				foreach ($statements as $statement) {
					$conn->execute($statement);
				}
			}
		}
	}

	/**
	* Shows a page describing the plugin's functionality, dependence on other plugins, ...
	*/
	function showDescriptionalPage(){

		if (is_object($this->user)){
		   $permission = $this->user->getPermission();
		   if (!$permission->hasAdminPermission()){
		   	  return;
		   }
		}
	   $plugininfos = PluginEngine::getPluginManifest($this->environment->getBasepath() . $this->pluginpath . "/");
	   $version = $plugininfos["version"];
	   $vendor = $plugininfos["origin"];
	   $origname = $plugininfos["pluginname"];
	   StudIPTemplateEngine::makeContentHeadline(_("Plugin-Details"),2);
		?>
				<tr>
					<td>Name:</td>
					<td align="left">&nbsp;<?= $this->pluginname ?></td>
				</tr>
				<tr>
					<td>Name (original):</td>
					<td align="left">&nbsp;<?= $origname ?></td>
				</tr>
				<tr>
					<td>Klasse:</td>
					<td align="left">&nbsp;<?= $this->getPluginclassname() ?></td>
				</tr>
				<tr>
					<td>Origin:</td>
					<td align="left">&nbsp;<?= $vendor ?></td>
				</tr>
				<tr>
					<td>Version:</td>
					<td align="left">&nbsp;<?= $version ?></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><a href="<?= PluginEngine::getLinkToAdministrationPlugin()?>"><?= makeButton("zurueck","img",_("zurück zur Plugin-Verwaltung"))?></a></td>
				</tr>
		<?php
	}

	function showAdministrationPage(){
		echo (_("Eine Administrationsseite ist für dieses Plugin nicht vorhanden"));
	}

	/**
	 * set the current user
	 *
	 * @param StudIPUser $newuser
	 */
	function setUser($newuser){
		if (is_a($newuser,'StudIPUser') || is_subclass_of($newuser,'StudIPUser')){
			$this->user = $newuser;
		}
	}

	/**
	 * Returns the current user
	 *
	 * @return StudIPUser
	 */
	function getUser(){
		return $this->user;
	}

	/**
	 * Sets the state of the plugin.
	 *
	 * @param boolean $value
	 * @param boolean $requestedbyuser - true if the user requested to change the status
	 */
	function setActivated($value=false,$requestedbyuser=false){
		$this->activated = $value;
	}

	function setEnabled($value=false){
		$this->enabled = $value;
	}

	function isEnabled(){
		return $this->enabled;
	}

	/**
	 * Diese Methode überprüft, ob alle Voraussetzungen für den Einsatz dieses
	 * Plugins erfüllt sind.
	 * @param die Stud.IP-Version von der dieses Plugin eingebunden wird
	 * @return false - Voraussetzungen für das Plugin sind nicht erfüllt, Plugin
	 *                 kann  nicht installiert werden.
	 * 			true - alles in Ordnung, Plugin kann installiert werden.
	 */
	function checkVersion($studipversion){
		return false;
	}

	/**
	 * Liefert die Administrationsinformationen zu diesem Plugin zurück
	 */
	function getPluginAdminInfo(){
		return $this->pluginadmininfo;
	}

	/**
	 * setzt neue Administrationsinformationen zu diesem Plugin
	 * @param AdminInfo
	 */
	function setPluginAdminInfo($admininfo){
		if (is_a($admininfo,'AdminInfo') || is_subclass_off($admininfo,'AdminInfo')){
			$this->pluginadmininfo = $admininfo;
		}
		else {
			echo "Incompatible Paramter type";
		}
	}

	/**
	 * Aktiviert das Plugin
	 * @return  true - Erfolg
	 * 			false	 - kein Erfolg
	 */
	function activatePlugin(){
		$this->activated = true;
		return false;
	}

	/**
	 * Dektiviert das Plugin
	 * @return  true - Erfolg
	 * 			false	 - kein Erfolg
	 */
	function deactivatePlugin(){
		$this->activated = false;
		return false;
	}

	/**
	 * Liefert den relativen Namen des Icons dieses Plugins zurück
	 * @return den relativen Namen des Icons
	 */
	function getPluginiconname(){
		return is_null($this->pluginiconname)
		       ? $GLOBALS['ASSETS_URL'].'images/leer.gif'
		       : $this->getPluginpath() . '/' . $this->pluginiconname;
	}

	/**
	 * Liefert die Persistenzschnittstelle zu diesem Plugin zurück.
	 * @return die Persisitenzschnittstelle vom Typ AbstractPluginPersistence
	 */

	/**
	 * Getter und Setter für die Attribute der Klasse
	 */
	function setPluginiconname($newicon){
		$this->pluginiconname = $newicon;
	}

	function getPluginname(){
		if ($this->pluginname == ""){
			$this->pluginname = strtolower(get_class($this));
		}
		return $this->pluginname;
	}

	function setPluginname($newname){
		$this->pluginname = $newname;
	}

	function setPluginid($newid){
		$this->pluginid = $newid;
	}

	function getPluginid(){
		return $this->pluginid;
	}

	function getHelpinfo(){
		return $this->helpinfo;
	}

	function setHelpInfo($newhelpinfo){
		if (is_a($newhelpinfo,'HelpInfo') || is_subclass_of($newhelpinfo,'HelpInfo')){
			$this->helpinfo = $newhelpinfo;
		}
	}

	function getNavigation(){
		return $this->navigation;
	}

	function setNavigation($newnavigation){
		if (is_a($newnavigation,'PluginNavigation') || is_subclass_of($newnavigation,'PluginNavigation')){
		    $this->navigation = $newnavigation;
		}
	}

	function hasNavigation(){
		if ($this->navigation != null){
		   return true;
		}
		else {
		   return false;
		}
	}

	function isActivated(){
		return $this->activated;
	}


	function setEnvironment($newenv){
		$this->environment = $newenv;
		$this->setPluginPath($newenv->getRelativepackagepath());
	}

	function getEnvironment(){
		return $this->environment;
	}

	function setPluginpath($newpath){
		$this->pluginpath = $newpath;
	}

	function getPluginpath(){
		return $this->pluginpath;
	}

	function setBasepluginpath($newpath){
		$this->basepluginpath = $newpath;
	}

	function getBasepluginpath(){
		return $this->basepluginpath;
	}

	/**
	* @param $subnavigationparam - set if a subnavigation item was clicked. The value is plugin dependent and specified by the plugins subnavigation link params.
	*/
	function show($subnavigationparam=null){
	}

	function getNavigationPosition(){
		return $this->navposition;
	}

	function setNavigationPosition($newpos){
		$this->navposition = $newpos;
	}

	/**
	 * Which text should be shown in certain titles
	 * @return string title
	 */
	function getDisplaytitle(){
		if ($this->hasNavigation()){
			return $this->navigation->getDisplayname();
		}
		else {
			return $this->getPluginname();
		}
	}

	/**
	 * Sets, if the plugin is main oder dependent on other plugins
	 * @param boolean $dependentplugin
	 */
	function setDependentOnOtherPlugin($dependentplugin=true){
		if (is_bool($dependentplugin)){
			$this->dependentonplugin = $dependentplugin;
		}
	}

	/**
	 * returns true, if this plugin depends on another plugin
	 *
	 */
	function isDependentOnOtherPlugin(){
		return $this->dependentonplugin;
	}

	function actionShow($param = null){
		return $this->show($param);
	}
	
	function actionShowDescriptionalPage(){
		return $this->showDescriptionalPage();
	}
	
	function actionShowAdministrationPage(){
		return $this->showAdministrationPage();
	}
}
?>

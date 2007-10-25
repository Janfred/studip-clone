<?php

/**
 * Abstract plugin for plugins shown on the homepage of a user
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPHomepagePlugin extends AbstractStudIPLegacyPlugin {

	var $requesteduser; // StudIPUser for which user the homepage should be shown
	var $status_showOverview; // Uebersichtsseite unterdruecken

	function AbstractStudIPHomepagePlugin(){
		parent::AbstractStudIPLegacyPlugin();
		$this->requesteduser = null;
		$this->status_showOverview = 1;
	}

	/**
	 * Used to show an overview on the homepage of a user.
	 *
	 */
	function showOverview(){
		// has to be implemented
	}
	
	/**
	 * true:  overviewpage is enabled
	 * false: overviewpage is disabled
	 */
	function getStatusShowOverviewPage(){
		return $this->status_showOverview;
	}
	function setStatusShowOverviewPage($status){
		$oldstatus = $this->status_showOverview;
		$this->status_showOverview = $status;
		return $oldstatus;
	}


	/**
	 * Set the user for which the homepage is rendered
	 *
	 * @param unknown_type $newuser
	 */
	function setRequestedUser($newuser){
		if (is_a($newuser,"StudIPUser") || is_subclass_of($newuser,"StudIPUser")){
			$this->requesteduser = $newuser;
		}
	}

	function getRequestedUser(){
		return $this->requesteduser;
	}


  /**
   * This abstract method sets everything up to perform the given action and
   * displays the results or anything you want to.
   *
   * @param  string the name of the action to accomplish
   *
   * @return void
   */
  function display($action) {

    include 'lib/include/html_head.inc.php';
    include 'lib/include/header.php';

    $pluginparams = $_GET["plugin_subnavi_params"];

    // show the admin-Tabs
    $hpusername = $_GET["requesteduser"];
    $admin_darf = FALSE;
    $db = new DB_Seminar();

    if (empty($hpusername)){
      $hpusername = $GLOBALS["auth"]->auth["uname"];
    }

    if ($GLOBALS["auth"]->auth["uname"] == $hpusername){
      $admin_darf = true;
    }

    $db->query("SELECT * FROM auth_user_md5  WHERE username ='$hpusername'");
    $db->next_record();
    if (!$db->nf()) {
      throw new Exception(_("Es wurde kein Nutzer unter dem angegebenen Nutzernamen gefunden!").
                          "<br />".
                          _(" Wenn Sie auf einen Link geklickt haben, kann es sein, dass sich der Username des gesuchten Nutzers ge&auml;ndert hat, oder der Nutzer gel&ouml;scht wurde."));
    } else{
      $user_id=$db->f("user_id");
    }

    $requser = new StudIPUser();
    $requser->setUserid($user_id);
    $this->setRequestedUser($requser);

    //Bin ich ein Inst_admin, und ist der user in meinem Inst Tutor oder Dozent?
    $db->query("SELECT b.inst_perms FROM user_inst AS a ".
               "LEFT JOIN user_inst AS b USING (Institut_id) ".
               "WHERE (b.user_id = '$user_id') AND ".
               "(b.inst_perms = 'autor' OR ".
               "b.inst_perms = 'tutor' OR ".
               "b.inst_perms = 'dozent') AND ".
               "(a.user_id = '$user->id') AND (a.inst_perms = 'admin')");
    if ($db->num_rows())
      $admin_darf = TRUE;

    if ($GLOBALS['perm']->is_fak_admin()){
      $db->query("SELECT c.user_id FROM user_inst a ".
                 "LEFT JOIN Institute b ON (a.Institut_id = b.fakultaets_id) ".
                 "LEFT JOIN user_inst c ON (b.Institut_id = c.Institut_id) ".
                 "WHERE a.user_id = '$user->id' AND a.inst_perms = 'admin' ".
                 "AND c.user_id='$user_id'");
      if ($db->next_record())
      $admin_darf = TRUE;
    }

    if ($GLOBALS['perm']->have_perm("root")) {
      $admin_darf=TRUE;
    }

    // Es werden die Editreiter angezeigt, wenn ich ändern darf
    if ($GLOBALS['perm']->have_perm("root") OR $admin_darf == TRUE) {
      // rights should be checked
      $username = $hpusername;
      include 'lib/include/links_about.inc.php';
    }

    $pluginnav = $this->getNavigation();
    StudIPTemplateEngine::makeHeadline($this->getDisplaytitle(), true,
                                       $this->getPluginiconname());

    StudIPTemplateEngine::startContentTable();
    $this->$action($pluginparams);
    StudIPTemplateEngine::endContentTable();

    // close the page
    include 'lib/include/html_end.inc.php';
    page_close();
  }
}
?>

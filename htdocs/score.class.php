<?
/*
score.class.php - Score class
Copyright (C) 2003 Ralf Stockmann <rstockm@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

class Score {
	var $score;	// Score of the user
	var $publik;	// whether or not the score is published
	var $ismyscore;	// wheter or not this is my own score
	var $title;	// Title that refers to the score
	var $myscore;	// my own Score
	var $mygender;

	
	// Konstruktor
	
	function Score ($user_id) {
		$this->ismyscore = $this->CheckOwner($user_id);
		$this->myscore = $this->GetMyScore();
		$this->mygender = $this->GetGender($user_id);
		$this->title = $this->gettitel($this->myscore, $this->mygender);
		$this->publik = $this->CheckScore($user_id);
	}

function CheckOwner ($user_id) {
	global $user;
	if ($user_id == $user->id)
		return TRUE;
	else
		return FALSE;	
}

function GetGender ($user_id) {
	$db=new DB_Seminar;
	$db->query("SELECT geschlecht AS gender FROM user_info WHERE user_id = '$user_id'");
	$db->next_record();
	return $db->f("gender");
}

function PublishScore () {
	global $user;
	$db=new DB_Seminar;
	$query = "UPDATE user_info "
		." SET score = '$this->myscore'"
		." WHERE user_id = '$user->id'";
	$db->query($query);
	$this->publik = $this->myscore;
}

function KillScore () {
	global $user;
	$db=new DB_Seminar;
	$query = "UPDATE user_info "
		." SET score = 0"
		." WHERE user_id = '$user->id'";
	$db->query($query);
	$this->publik = FALSE;
}

function IsMyScore () {
	return $this->ismyscore;	
}

function ReturnMyScore () {
	return $this->myscore;	
}

function ReturnMyTitle () {
	return $this->title;	
}

function ReturnPublik () {
	return $this->publik;	
}

function GetScore ($user_id) {
	$db=new DB_Seminar;
	$db->query("SELECT score FROM user_info WHERE user_id = '$user_id'");
	$db->next_record();
	return $db->f("score");
}

function CheckScore ($user_id) {
	$db=new DB_Seminar;
	$db->query("SELECT score FROM user_info WHERE user_id = '$user_id' AND score > 0");
	if ($db->next_record())
		return $db->f("score");
	else 
		return FALSE;
}

function GetScoreContent($user_id) {
	$username = get_username($user_id);
	$db=new DB_Seminar;
	$db->query("SELECT count(post_id) as guestcount FROM guestbook LEFT JOIN user_info ON(range_id=user_info.user_id) WHERE range_id = '$user_id' AND guestbook='1' GROUP BY range_id");
	if ($db->next_record()) {
		$gaeste = $db->f("guestcount");
		$content .= "<a href=\"about.php?username=$username\"><img src=\"pictures/nutzer.gif\" border=\"0\"".tooltip(_("Gästebuch aktiviert mit $gaeste Einträgen"))."></a>&nbsp;";
	}
	$db->query("SELECT * FROM news_range WHERE range_id = '$user_id'");
	if ($db->next_record()) {
		$news = $db->num_rows();
		$content .= "<a href=\"about.php?username=$username\"><img src=\"pictures/icon-news.gif\" border=\"0\"".tooltip(_("$news persönliche News"))."></a>&nbsp;";
	}
	$db->query("SELECT * FROM vote WHERE range_id = '$user_id'");
	if ($db->next_record()) {
		$vote = $db->num_rows();
		$content .= "<a href=\"about.php?username=$username\"><img src=\"pictures/icon-vote.gif\" border=\"0\"".tooltip(_("$vote Umfragen"))."></a>&nbsp;";
	}
	return $content;
}

/**
* Retrieves the titel for a given studip score
*
* @param		integer	a score value
* @param		integer	gender (0: male; 1:female)
* @return		string	the titel
*
*/
function gettitel($score, $gender=0) {

	if ($score)
		$logscore = floor(log10($score) / log10(2));
	else
		$logscore = 0;
		
	if ($logscore > 17)
		$logscore = 17;
		
	$titel[0]  =	array(0 => _("Unbeschriebenes Blatt"), 1 => _("Unbeschriebenes Blatt"));
	$titel[1]  =	array(0 => _("Unbeschriebenes Blatt"), 1 => _("Unbeschriebenes Blatt"));
	$titel[2]  =	array(0 => _("Unbeschriebenes Blatt"), 1 => _("Unbeschriebenes Blatt"));
	$titel[3]  =	array(0 => _("Neuling"), 1 => _("Neuling"));
	$titel[4]  =	array(0 => _("Greenhorn"), 1 => _("Greenhorn"));
	$titel[5]  =	array(0 => _("Anf&auml;nger"), 1 => _("Anf&auml;ngerin"));
	$titel[6]  =	array(0 => _("Einsteiger"), 1 => _("Einsteigerin"));
	$titel[7]  =	array(0 => _("Beginner"), 1 => _("Beginnerin"));
	$titel[8]  =	array(0 => _("Novize"), 1 => _("Novizin"));
	$titel[9]  =	array(0 => _("Fortgeschrittener"), 1 => _("Fortgeschrittene"));
	$titel[10] =	array(0 => _("Kenner"), 1 => _("Kennerin"));
	$titel[11] =	array(0 => _("K&ouml;nner"), 1 => _("K&ouml;nnerin"));
	$titel[12] =	array(0 => _("Experte"), 1 => _("Expertin"));
	$titel[13] =	array(0 => _("Meister"), 1 => _("Meisterin"));
	$titel[14] =	array(0 => _("Gro&szlig;meister"), 1 => _("Gro&szlig;meisterin"));
	$titel[15] =	array(0 => _("Guru"), 1 => _("Hohepriesterin"));
	$titel[16] =	array(0 => _("Lichtgestalt"), 1 => _("Lichtgestalt"));
	$titel[17] =	array(0 => _("Gott"), 1 => _("G&ouml;ttin"));

	return $titel[$logscore][$gender];
}

/**
* Retrieves the score for the current user
* 
* @return		integer	the score
*
*/
function GetMyScore() {
	global $user,$auth;

	$user_id=$user->id; //damit keiner schummelt...

	///// Werte holen...

	$db=new DB_Seminar;
	$db->query("SELECT count(*) as postings FROM px_topics WHERE user_id = '$user_id' ");
	$db->next_record();
	$postings=$db->f("postings");

	$db->query("SELECT count(*) as dokumente FROM dokumente WHERE user_id = '$user_id' ");
	$db->next_record();
	$dokumente=$db->f("dokumente");

	$db->query("SELECT count(*) as seminare FROM seminar_user WHERE user_id = '$user_id' ");
	$db->next_record();
	$seminare=$db->f("seminare");

	$db->query("SELECT count(*) as archiv FROM archiv_user WHERE user_id = '$user_id' ");
	$db->next_record();
	$archiv=$db->f("archiv");

	$db->query("SELECT count(*) as institut FROM user_inst WHERE user_id = '$user_id' ");
	$db->next_record();
	$institut=$db->f("institut");

	$db->query("SELECT count(*) as news FROM news WHERE user_id = '$user_id' ");
	$db->next_record();
	$news=$db->f("news");


///////////////////////// Die HOCHGEHEIME Formel:

	$score = (5*$postings) + (5*$news) + (20*$dokumente) + (5*$institut) + (5*($archiv+$seminare));
	if(file_exists("./user/".$user_id.".jpg"))
		$score *=10;

/// Schreiben wenn hoeher

	$query = "UPDATE user_info "
		." SET score = '$score'"
		." WHERE user_id = '$user_id' AND score > 0";
	$db->query($query);
	return $score;
}

}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
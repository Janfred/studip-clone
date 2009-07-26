<?php
$participate_link = '<a href="'. UrlHelper::getLink('sem_verify.php?id='. $studygroup->getId()) .'">%s</a>';
$mods_db = $studygroup->getMembers();
foreach ($mods_db as $data) :
	$mods[] = '<a href="'. UrlHelper::getLink('about.php?username='. $data['username']) .'">'. $data['fullname'] .'</a>';
endforeach;

/* * * * * * * * * * * * *
 * * * I N F O B O X * * *
 * * * * * * * * * * * * */
$infobox['picture'] = 'groups.jpg';
$infobox['content'] = array(
	array(
		'kategorie' => _("Information"), 
		'eintrag'   => array(
			array(
				'text' => _("Hier sehen Sie weitere Informationen zur Arbeitsgruppe. Außerdem können sie ihr beitreten/eine Mitgliedschaft beantragen."),
				'icon' => 'ausruf_small.gif'
			)
		)
	),
	array(
		'kategorie' => _("Aktionen"),
		'eintrag'   => array(
			array(
				'text' => sprintf( $participate_link, $studygroup->admission_prelim ? _("Mitliedschaft beantragen") : _("Arbeitsgruppe beitreten")),
				'icon' => 'link_intern.gif'
			),
		)
	)
);

$search = array(
	'text' => '<a href="'. UrlHelper::getLink($send_from_search_page) . '">'. _("zurück zur Suche") .'</a>',
	'icon' => 'link_intern.gif'
);

if ($send_from_search_page) {
	$infobox['content'][1]['eintrag'][] = $search;
}

/* * * * * * * * * * * *
 * * * O U T P U T * * * 
 * * * * * * * * * * * */
?>
<h1><?= $studygroup->getName() ?></h1>
<b><?= _("Moderiert von:") ?></b> <?= implode(',', $mods) ?><br>
<br>
<em><?= $studygroup->description ?></em>

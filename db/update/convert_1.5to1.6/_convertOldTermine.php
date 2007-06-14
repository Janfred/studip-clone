<?
set_time_limit(0); // weils so schoen lange laeuft...

/* converts from traditional metadate-style to the new style */
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

include ("lib/seminar_open.php"); // initialise Stud.IP-Sessio
require_once('lib/classes/Seminar.class.php');
require_once('lib/resources/lib/VeranstaltungResourcesAssign.class.php');
require_once('lib/raumzeit/QueryMeasure.class.php');

$perm->check("root");
//$_mquery = new QueryMeasure("SQL-Queries");

if (!$_REQUEST['start_at']) {
	$start_at = 0;
}

$step = 500;

$GLOBALS['FORCE_THROW_AWAY'] = TRUE;

$db = new DB_Seminar();
$db2 = new DB_Seminar();

if ($start_at == 0) {
    echo 'Umwandlung der Terminthemen in Terminbezogene Themen...<br/>';

    $db->query("SELECT termine.* FROM seminare LEFT JOIN termine ON (seminare.Seminar_id = termine.range_id) WHERE (content != '' OR description != '')");

    $i = 0;
    $schwelle = 0;
    $gesamt = $db->num_rows();

    while ($db->next_record()) {
        $i++;
        echo '['.$i.' von '.$gesamt.'] ';
        $new_issue_id = md5(uniqid("Issue"));
        echo date('d.m.Y, H:i', $db->f('date')).' - '.date('H:i', $db->f('end_time')).'<br/>';
        flush();
        $db2->query("INSERT INTO themen_termine (issue_id, termin_id) VALUES ('$new_issue_id', '".$db->f('termin_id')."')");
        $db2->query("INSERT INTO themen (issue_id, seminar_id, author_id, title, description, mkdate, chdate) VALUES ('$new_issue_id', '".$db->f('range_id')."', '".$db->f('author_id')."', '".mysql_escape_string($db->f('content'))."', '".mysql_escape_string($db->f('description'))."', '".$db->f('mkdate')."', '".$db->f('chdate')."')");
        $db2->query("UPDATE termine SET content = '', description = '' WHERE termin_id = '".$db->f('termin_id')."'");
	$db2->query("UPDATE folder SET range_id = '$new_issue_id' WHERE range_id = '".$db->f('termin_id')."'");
	if($db->f('topic_id')){
		$db2->query("UPDATE px_topics SET topic_id = '$new_issue_id' WHERE topic_id = '".$db->f('topic_id')."'");
		$db2->query("UPDATE px_topics SET root_id = '$new_issue_id'  WHERE root_id = '".$db->f('topic_id')."'");
		$db2->query("UPDATE px_topics SET parent_id = '$new_issue_id'  WHERE parent_id = '".$db->f('topic_id')."'");
	}
    }
}


if ($only_sem == '') {
	$db->query("SELECT Seminar_id, Name FROM seminare WHERE 1 LIMIT $start_at, $step");
} else {
	$db->query("SELECT Seminar_id, Name FROM seminare WHERE Name LIKE '%$only_sem%'");
}
$max = $db->num_rows();

if ($max == 0) {
    echo '<b>Konvertierung beendet.</b><br/>';
    die;
}

$i = 0;
$schwelle = 0;
if ($_REQUEST['start']) $schwelle = $_REQUEST['start'];
echo "<pre>";
$_CONVERT = TRUE;
echo '<b>Konvertierung der Veranstaltungen...</b><br/>';
while ($db->next_record()) {
	$i++;
	if ($i >= $schwelle) {
		$id = $db->f('Seminar_id');
		echo "(".($i+$start_at)." von ".($max+$start_at)." [".(date('H', time() -$cur) -1).date(':i:s', (time()-$cur))."]) (".$db->f('Seminar_id').") Konvertiere ".htmlReady($db->f('Name')).'<br/>';
		flush();
		unset($sem);
		$sem = new Seminar($db->f('Seminar_id'));
		foreach ($sem->metadate->cycles as $key => $val) {
			if ($val->resource_id) {
				$veranstAssign = new VeranstaltungResourcesAssign($sem->getId());
				$veranstAssign->deleteAssignedRooms();
			}
			$sem->getSingleDatesForCycle($key);
			$val->resource_id = '';
		}
		$sem->store();
		echo '<SCRIPT>scrollBy(0,50);</SCRIPT>';
	}
}

//echo $_mquery->showData();
echo "\n\n",'<b>Refreshing page for converting next '.$step.' seminars...</b>', "\n";
echo '<SCRIPT>scrollBy(0,100);</SCRIPT>';
flush();
sleep(1);
?>
<script>
	window.location = '<?=$PHP_SELF?>?start_at=<?=($start_at+$step)?>';
</script>

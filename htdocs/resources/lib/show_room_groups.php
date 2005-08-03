<?php
require_once $GLOBALS['ABSOLUTE_PATH_STUDIP'] . $GLOBALS['RELATIVE_PATH_RESOURCES'] . "/lib/ResourceObject.class.php";
require_once $GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/DbSnapshot.class.php";
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "user" => "Seminar_user" , "perm" => "Seminar_Perm"));
$perm->check("root");
echo "<pre>";
$res_obj =& ResourceObject::Factory();
$snap =& new DbSnapshot(new DB_Seminar("SELECT resource_id, parent_id FROM resources_objects INNER JOIN resources_categories USING(category_id) WHERE is_room = 1"));
foreach($snap->getGroupedResult('parent_id') as $parent_id => $rooms){
	if (is_array($rooms['resource_id'])){
		$res_obj->restore($parent_id);
		echo "//--------------------------------------------------------------------\n";
		echo "\$room_groups[\$c]['name'] = '" . $res_obj->getPathToString(true) . "';\n";
		foreach (array_keys($rooms['resource_id']) as $room_id){
			$res_obj->restore($room_id);
			echo "\$room_groups[\$c]['rooms'][] = '$room_id';  //" . $res_obj->getPathToString(true) . "\n";
		}		
	}
}
?>

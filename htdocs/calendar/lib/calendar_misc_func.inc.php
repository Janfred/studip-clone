<?

function cmp($a, $b){
	$start_a = date("Gi", $a->getStart());
	$start_b = date("Gi", $b->getStart());
	if($start_a == $start_b)
		return 0;
	if($start_a < $start_b)
		return -1;
	return 1;
}

function cmp_list($a, $b){
	$start_a = $a->getStart();
	$start_b = $b->getStart();
	if($start_a == $start_b)
		return 0;
	if($start_a < $start_b)
		return -1;
	return 1;
}

?>

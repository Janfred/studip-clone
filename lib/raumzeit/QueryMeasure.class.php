<?php
# Lifter002: TODO

/*
 * QueryMeasure.class.php - <short-description>
 *
 * Copyright (C) 2006 - Till Glöggler <tgloeggl@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


class QueryMeasure {
	var $count = 0;
	var $time = 0;
	var $time_zw = 0;
	var $info = '';
	var $queries = array();

	function QueryMeasure($info = '') {
		$this->count = 0;
		$this->time = 0;
		$this->info = $info;
	}

	function microtime_float() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	function start() {
		$this->time_zw = $this->microtime_float();
	}

	function end($query = '', $num_rows = 0, $num_fields = 0, $num_affected = 0) {
		$tend = $this->microtime_float();
		$this->time += $tend - $this->time_zw;
		$this->count++;
		$this->log($query, $tend - $this->time_zw, debug_backtrace(), $num_rows, $num_fields, $num_affected);
	}

	function log($query, $time, $debug, $rows, $fields, $affected) {
		$zw = array(
			'script' => $GLOBALS['PHP_SELF'],
			'query' => $query,
			'time' => $time,
			'user_id' => $GLOBALS['user']->id,
			'session_id' => $GLOBALS['sess']->id,
			'rows' => $rows,
			'fields' => $fields,
			'affected' => $affected
			//'debug' => base64_encode(serialize($debug))
		);

		$this->queries[] = $zw;

	}

	function store() {
		$sepp = chr(1);
		$filename = $GLOBALS['LOG_PATH'].'/query2.log';

		$file = fopen($filename, 'a');
		if ($file) {

			foreach ($this->queries as $val) {
				$csv = 
					time().$sepp.
					$val['script'].$sepp.
					$val['query'].$sepp.
					$val['time'].$sepp.
					$val['user_id'].$sepp.
					$val['session_id'].$sepp.
					$val['rows'].$sepp.
					$val['fields'].$sepp.
					$val['affected'].$sepp;
				//.$val['debug'];

				$csv = str_replace("\n", ' ', str_replace("\r", ' ', $csv));
				$csv = str_replace("\t", ' ', $csv);
				fputs($file, $csv."\n");
			}
			fclose($file);
		}
	}

	function showData() {
		return "<pre>\n-------------------------\n".
			"Queries: ".$this->info."\n".
			"Anzahl:  ".round($this->count, 6)."\n".
			"Zeit:    ".round($this->time, 6)."\n".
			print_r($this->queries, true)."\n";
	}

	function showDataCompact() {
		$ret  = '</td></tr></table></td></tr></table>';
		$ret .= '<pre style="{border:1px solid black;background-color:#FFFFDD;font-size:0.9em;text-align:left}">';
		$ret .= "<b>Anzahl Queries: ".round($this->count, 6)."</b>\n";
		$ret .= "<b>Gesamtzeit:     ".round($this->time, 6)."</b>\n";
		$ret .= "\nTime\tRows\tFields\tQuery\n";
		foreach ($this->queries as $query) {
			$zw_query = $query['query'];
			$ret .= round($query['time'], 4)."\t".$query['rows']."\t".$query['fields']."\t".$zw_query."\n";
			$ret .= '<hr>';
			//var_dump($query);
		}
		return $ret;
	}
}

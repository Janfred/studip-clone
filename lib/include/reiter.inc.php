<?
/*
reiter.php - 0.8.20020327
Klasse zum Erstellen des Reitersystems
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

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

require_once ('lib/visual.inc.php');

class reiter {


	/**
	 * Converts relative links to absolute ones.
	 *
	 * @access private
	 *
	 * @param  string     a link that will be converted if it is relative
	 *
	 * @return string     an absolute link
	 */
	function absolutizeLink($link) {
		if (!(preg_match('#^[a-z]+://#', $link) || $link[0] === '/')) {
			$link = $GLOBALS['ABSOLUTE_URI_STUDIP'].$link;
		}
		return $link;
	}


	/**
	 * Activates that element of the structure that corresponds to the given view
	 * argument.
	 *
	 * @access private
	 *
	 * @param  array      the link structure from lib/include/links_*.inc.php
	 * @param  string     the key of the link to activate
	 *
	 * @return void
	 */
	function activateStructure(&$structure, $view) {

		# view is empty, use the first item
		if (!$view) {
			reset($structure);
			$view = key($structure);
		}

		$structure[$view]["active"] = TRUE;

		# activate it's topKat
		if ($structure[$view]["topKat"]) {
			$structure[$structure[$view]["topKat"]]["active"] = TRUE;
		}

		# or the topKat itself
		else {
			foreach ($structure as $key => $value) {
				if ($structure[$key]["topKat"] == $view) {
					$structure[$key]["active"] = TRUE;
					break;
				}
			}
		}
	}


	/**
	 * Outputs the tabs.
	 *
	 * @param  array      an associative array describing the tabs' structure
	 * @param  string     the key of the single tab to activate
	 *
	 * @return void
	 */
	function create($structure, $view) {

		$noAktiveBottomkat = FALSE;

		if (preg_match('/^\((.*)\)$/', $view, $matches)) {
			$noAktiveBottomkat = TRUE;
			$view = $matches[1];
		}

		foreach ($structure as $key => $value) {
			if (isset($structure[$key]['link'])) {
				$structure[$key]['link'] =
				  self::absolutizeLink($structure[$key]['link']);
			}
		}

		$this->activateStructure($structure, $view);

		echo $GLOBALS['template_factory']->render('tabs',
			compact('structure', 'noAktiveBottomkat'));
	}
}

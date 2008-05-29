<?
# Lifter002: 
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// flash_proxy.php
//
//
// Copyright (c) 2008 Peter Thienel <thienel@data-quest.de>
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


if (ini_get('zlib.output_compression')){
	@ini_set('zlib.output_compression','0');
}
if (!ini_get('allow_url_fopen')){
	@ini_set('allow_url_fopen','1');
}

ob_start();
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
if (!($perm->have_perm('user'))) {
	page_close();
	exit;
}
require_once ('lib/datei.inc.php');
$url = urldecode($_GET['url']);

$headers = get_headers($url, 1);

header('Content-Disposition: attachment; filename="' . md5($url) . '.flv"');
if ($headers['Content-Length']) {
	header('Content-Length: ' . $headers['Content-Length']);
}
header("Content-Type: video/x-flv");
ob_end_flush();
ob_clean();
readfile_chunked($url);

page_close();
?>
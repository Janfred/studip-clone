<?php
/**
 * create vote result bar image in percentage-dependent color
 *
 * @author      mcohrs
 * @version     $Id$
 * @copyright   2003 Stud.IP-Project
 * @access      public
 * @package     vote
 * @modulegroup vote_modules
 */

$width  = 1;
$height = 10;

$image = imageCreate( $width, $height );

$redVal = 235;
if ( $_GET["percent"] < 50 )
     $otherVal = 220 - $_GET["percent"] * 4;

$borderCol  = imageColorAllocate( $image, 0, 0, 0 );
$contentCol = imageColorAllocate( $image, $redVal, $otherVal, $otherVal );

#imagefill( $image, 0, 0, $borderCol );
imageline( $image, 0, 1, 0, $height-2, $contentCol );

#imageInterlace( $image, 0 );
header( "Content-Type: image/png" );
imagepng( $image );

?>

<?php  
/*------------------------------------------------------------------------
* 
* CSS-JS-BOOSTER
* Copyright (C) 2010 Christian "Schepp" Schaefer
* http://twitter.com/derSchepp
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published 
* by the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public License
* along with this program. 
* If not, see <http://www.gnu.org/licenses/lgpl-3.0.txt>
* 
------------------------------------------------------------------------*/
// suppress PHP warnings
error_reporting(E_ERROR);

ini_set("display_errors", "0");
include('booster_inc.php');

((isset($_GET['dir'])) ? $source = str_replace('>','..',rtrim(preg_replace('/[^a-z0-9,\-_\.\/>]/i','',$_GET['dir']),'/')) : $source = 'css');
((isset($_GET['cachedir'])) ? $booster_cachedir = str_replace('>','..',rtrim(preg_replace('/[^a-z0-9,\-_\.\/>]/i','',$_GET['cachedir']),'/')) : $booster_cachedir = 'booster_cache');
((isset($_GET['totalparts'])) ? $totalparts = intval($_GET['totalparts']) : $totalparts = 1);
((isset($_GET['part'])) ? $part = intval($_GET['part']) : $part = 0);

$booster = new GDBooster();
if (isset($_GET['debug']) && $_GET['debug'] == 1)
    $booster->debug = TRUE;
if (isset($_GET['librarydebug']) && $_GET['librarydebug'] == 1)
    $booster->librarydebug = TRUE;
if (isset($_GET['css_hosted_minifier']) && $_GET['css_hosted_minifier'] == 1)
    $booster->css_hosted_minifier = TRUE;
$booster->booster_cachedir = $booster_cachedir;
$booster->css_mhtml_enabled_ie = TRUE;
$booster->css_source = $source;
$booster->css_totalparts = $totalparts;
$booster->css_part = $part;

// Check if file gets requested with an eTag, serve 304 if nothing changed
$etag = md5($source . intval($booster->debug) . intval($booster->librarydebug) . intval($booster->css_hosted_minifier) . $booster->filestime);

if (!empty($_SERVER) && isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
    header('HTTP/1.1 304 Not Modified');
    exit();
}

$css = $booster->css();

header("Cache-Control: max-age=2592000, public");
header("Expires: " . gmdate('D, d M Y H:i:s', mktime(date('h') + (24 * 35))) . " GMT");
header("Vary: Accept-Encoding"); 
header("Content-type: text/css"); 
header("ETag: " . $etag);

if (isset($booster_use_ob_gzhandler)) {
    $css_strlen = strlen($css);
    
    for ($i = 0; $i < $css_strlen; $i = ($i + 2048)) {
        echo substr($css, $i, 2048);
        
        if (ob_get_length()) {
            try {
                ob_flush('ob_gzhandler');
            } catch(Exception $e) {
                // Error
            }
            try {
                flush('ob_gzhandler');
            } catch(Exception $e) {
                // Error
            }
            try {
                ob_flush('ob_gzhandler');
            } catch(Exception $e) {
                // Error
            }
        }
    }
} else {
    echo $css;
}

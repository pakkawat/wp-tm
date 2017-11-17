<?php
/**
 * GB Booster Plugin
 *
 * @package geodir_gd_booster\uninstall
 * @copyright GeoDirectory. <http://wpgeodirectory.com>
 * @license GNU General Public License, version 3
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));

if(require(dirname(__FILE__).'/includes/wp-php53.php')) // TRUE if running PHP v5.3+.
	require_once dirname(__FILE__).'/uninstall.inc.php';
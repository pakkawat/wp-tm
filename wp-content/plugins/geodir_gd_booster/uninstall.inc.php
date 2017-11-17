<?php
/**
 * GB Booster Plugin
 *
 * @package geodir_gd_booster\uninstall
 * @copyright GeoDirectory. <http://wpgeodirectory.com>
 * @license GNU General Public License, version 3
 */
namespace geodir_gd_booster
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	$GLOBALS[__NAMESPACE__.'_uninstalling']    = TRUE;
	$GLOBALS[__NAMESPACE__.'_autoload_plugin'] = FALSE;

	require_once dirname(__FILE__).'/geodir-gd-booster.inc.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\uninstall'))
	{
		class uninstall // Uninstall handler.
		{
			/**
			 * @var plugin Primary plugin class instance.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * Uninstall constructor.
			 */
			public function __construct()
			{
				$GLOBALS[__NAMESPACE__] // Without hooks.
					= $this->plugin = new plugin(FALSE);

				$this->plugin->uninstall();
			}
		}
	}
	new uninstall(); // Run the uninstaller.
}
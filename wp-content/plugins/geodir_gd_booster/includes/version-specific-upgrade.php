<?php
namespace geodir_gd_booster // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\version_specific_upgrade'))
	{
		/**
		 * GD Booster (Upgrade Handlers)
		 *
		 * @package geodir_gd_booster\version_specific_upgrade
		 */
		class version_specific_upgrade // Version-specific upgrade handlers.
		{
			/**
			 * @var plugin Plugin reference.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var string Version they are upgrading from.
			 */
			protected $prev_version = ''; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * Reorganizing class members.
			 *
			 * @param string $prev_version Version they are upgrading from.
			 */
			public function __construct($prev_version)
			{
				$this->plugin       = plugin();
				$this->prev_version = (string)$prev_version;
				$this->run_handlers(); // Run upgrade(s).
			}
		}
	}
}
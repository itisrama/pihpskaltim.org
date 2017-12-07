#!/usr/bin/php
<?php
/**
 * An example command line application built on the Joomla Platform.
 *
 * To run this example, adjust the executable path above to suite your operating system,
 * make this file executable and run the file.
 *
 * Alternatively, run the file using:
 *
 * php -f run.php
 *
 * Note, this application requires configuration.php and the connection details
 * for the database may need to be changed to suit your local setup.
 *
 * @package    Joomla.Examples
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// We are a valid Joomla entry point.
define('_JEXEC', 1);

// Setup the base path related constant.
define('JPATH_BASE', dirname(dirname(__DIR__)));

// bootstrap the Joomla application
require_once JPATH_BASE . "/libraries/import.php";
// define necessary constants:
require_once JPATH_BASE . '/includes/defines.php';
// Import the configuration.
require_once JPATH_CONFIGURATION . '/configuration.php';

/**
 * An example command line application class.
 *
 * This application shows how to override the constructor
 * and connect to the database.
 *
 * @package  Joomla.Examples
 * @since    11.3
 */
class DatabaseApp extends JApplicationCli
{
	/**
	 * A database object for the application to use.
	 *
	 * @var    JDatabase
	 * @since  11.3
	 */
	protected $dbo = null;

	/**
	 * Class constructor.
	 *
	 * This constructor invokes the parent JApplicationCli class constructor,
	 * and then creates a connector to the database so that it is
	 * always available to the application when needed.
	 *
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	public function __construct()
	{
		jimport('joomla.database.database');

		// System configuration.
		$joomla_config = new JConfig;

		// this will throw a RuntimeException on failure.
		$this->dbo = JDatabase::getInstance(
			array(
				"[drive]" => $joomla_config->dbtype,
				"host" => $joomla_config->host,
				"user" => $joomla_config->user,
				"password" => $joomla_config->password,
				"database" => $joomla_config->db,
				"prefix" => $joomla_config->dbprefix,
			)
		);
	}

	public function getCommodities() {
		// Get a db connection.
		$db = $this->dbo;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id')));
		$query->select($db->quoteName('a.name'));
		
		$query->from($db->quoteName('#__gtpihps_national_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->category_id][$item->id] = $item->name;
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function execute()
	{
		echo 'Test';
		$commodities = $this->getCommodities();

		echo "<pre>"; print_r($commodities); echo "</pre>";
	}
}


JApplicationCli::getInstance('DatabaseApp')->execute();

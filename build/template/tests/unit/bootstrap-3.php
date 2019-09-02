<?php
/**
 * Prepares a minimalist framework for unit testing.
 *
 * Joomla is assumed to include the /unittest/ directory.
 * eg, /path/to/joomla/unittest/
 *
 * @package   Joomla.UnitTest
 *
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      http://www.phpunit.de/manual/current/en/installation.html
 */

define('_JEXEC', 1);

// Maximise error reporting.
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

/*
* Ensure that required path constants are defined. These can be overridden within the phpunit.xml file
* if you chose to create a custom version of that file.
*/
if (!defined('JPATH_TESTS'))
{
	define('JPATH_TESTS', realpath('tests/unit'));
}
if (!defined('JPATH_ROOT'))
{
	define('JPATH_ROOT', realpath('.'));
}
if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', realpath('joomla'));
}
if (!defined('JPATH_PLATFORM'))
{
	define('JPATH_PLATFORM', JPATH_BASE . '/libraries');
}
if (!defined('JPATH_LIBRARIES'))
{
	define('JPATH_LIBRARIES', JPATH_BASE . '/libraries');
}
if (!defined('JPATH_CACHE'))
{
	define('JPATH_CACHE', JPATH_BASE . '/cache');
}
if (!defined('JPATH_CONFIGURATION'))
{
	define('JPATH_CONFIGURATION', JPATH_BASE);
}
if (!defined('JPATH_SITE'))
{
	define('JPATH_SITE', JPATH_BASE);
}
if (!defined('JPATH_ADMINISTRATOR'))
{
	define('JPATH_ADMINISTRATOR', JPATH_BASE . '/administrator');
}
if (!defined('JPATH_INSTALLATION'))
{
	define('JPATH_INSTALLATION', JPATH_BASE . '/installation');
}
if (!defined('JPATH_MANIFESTS'))
{
	define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . '/manifests');
}
if (!defined('JPATH_PLUGINS'))
{
	define('JPATH_PLUGINS', JPATH_BASE . '/plugins');
}
if (!defined('JPATH_THEMES'))
{
	define('JPATH_THEMES', JPATH_BASE . '/templates');
}

// Ignore import attempts
function jimport()
{
}

require_once __DIR__ . '/autoload.php';

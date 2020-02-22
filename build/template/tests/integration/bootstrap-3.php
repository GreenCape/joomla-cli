<?php
class Bootstrap
{
	protected $application;
	protected $appDirectory;
	protected $cmsDirectory;

	public function __construct()
	{
		$this->application  = '@APPLICATION@';
		$this->appDirectory = $this->application == 'site' ? '' : '/' . $this->application;
		$this->cmsDirectory = '@CMS_ROOT@';
	}

	protected function initApp()
	{
		JFactory::getApplication($this->application);
	}

	public function init()
	{
		$this->assertPhpVersion('5.3.10');

		$this->setServerHttpVars();
		$this->getDefines();
		$this->getFramework();
		$this->getLoader();
		$this->getHelpers();

		$this->initApp();
	}

	protected function assertPhpVersion($version)
	{
		if (version_compare(PHP_VERSION, $version, '<'))
		{
			throw new ErrorException("Your host needs to use PHP $version or higher to run this version of Joomla!");
		}
	}

	protected function setServerHttpVars()
	{
		$_SERVER['HTTP_HOST']       = 'localhost';
		$_SERVER['HTTP_USER_AGENT'] = 'none';
	}

	protected function getDefines()
	{
		define('_JEXEC', 1);

		if (file_exists($this->cmsDirectory . "{$this->appDirectory}/defines.php"))
		{
			include_once $this->cmsDirectory . "{$this->appDirectory}/defines.php";
		}

		if (!defined('_JDEFINES'))
		{
			define('JPATH_BASE', $this->cmsDirectory . $this->appDirectory);
			require_once JPATH_BASE . "/includes/defines.php";
		}
	}

	protected function getLoader()
	{
		require_once $this->cmsDirectory . '/libraries/cms.php';
	}

	protected function getFramework()
	{
		require_once JPATH_BASE . "/includes/framework.php";
	}

	protected function getHelpers()
	{
		if ($this->application == 'administrator')
		{
			require_once JPATH_BASE . "/includes/helper.php";
			require_once JPATH_BASE . "/includes/toolbar.php";
		}
		if ($this->application == 'site')
		{
		}
	}
}

$bootstrap = new Bootstrap;
$bootstrap->init();

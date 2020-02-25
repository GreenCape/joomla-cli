<?php
class SeleniumConfig
{
	/** @var  string  The version adapter to use */
	public $versionAdapter = 'Version3Adapter';

	/** @var  string  The selenium server host */
	public $host = 'selenium';

	/** @var  int  The selenium server port */
	public $port = 4444;

	/** @var  string  The URL for the system under test */
	public $url = '@DOMAIN@';

	/** @var  string  The filesystem path to the system under test */
	public $folder = '@CMS_ROOT@';

	/** @var  string  The relative path to the system under test as seen from $url AND $folder */
	public $path = '/';

	/** @var string */
	public $browser = '@BROWSER@';

	/** @var string */
	public $coverageScript = 'phpunit_coverage.php';

	/** @var string */
	public $coverageScriptUrl = null;

	/** @var bool */
	public $captureScreenshotOnFailure = true;

	/** @var string */
	public $screenshotPath = '@CMS_ROOT@/build/screenshots';

	/** @var string */
	public $screenshotUrl = '@DOMAIN@/build/screenshots';

	/** @var string */
	public $username = 'superadmin';

	/** @var string */
	public $password = 'test';

	/** @var array */
	public $windowSize = array('width' => 1280, 'height' => 1024);

	/** @var bool */
	public $debug = false;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->baseURI = $this->folder . $this->path;
		if (!empty($this->coverageScript))
		{
			$this->coverageScriptUrl = $this->baseURI . $this->coverageScript;
		}
	}
}

<?php
/**
 * @package     JoomlaCLI
 * @subpackage  UnitTests
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   Copyright (C) 2013-2014 BSDS Braczek Software- und DatenSysteme. All rights reserved.
 */

class ApplicationTest extends PHPUnit_Framework_TestCase
{
	/** @var  \GreenCape\JoomlaCLI\Application */
	private $console;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->console = new \GreenCape\JoomlaCLI\Application();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	public function commandNameProvider()
	{
		return array(
			'install' => array('install'),
			'version' => array('version'),
		);
	}

	/**
	 * @dataProvider commandNameProvider
	 * @param string $command
	 */
	public function testCommandIsPresent($command)
	{
		$this->assertTrue($this->console->has($command));
	}
}

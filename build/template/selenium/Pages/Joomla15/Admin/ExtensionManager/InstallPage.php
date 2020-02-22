<?php
namespace Celtic\Testing\Joomla;

use \PHPUnit_Extensions_Selenium2TestCase_Element as Element;

class Joomla15AdminExtensionManagerInstallPage extends Joomla15AdminPage
{
	public function isCurrent()
	{
		try
		{
			$current = preg_match('~Extension Manager~', $this->headLine()->text());
			$current &= $this->getElement("xpath://ul[@id='submenu']/li/a[@class='active'][contains(., 'Install')");
		}
		catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e)
		{
			$current = false;
		}
		return $current;
	}

	/**
	 * Install an extension from a URL
	 *
	 * Navigates to the Installer view and installs the extension.
	 * After installation, the current page is the success view.
	 *
	 * Requires a valid session.
	 *
	 * @param   string  $packageUrl  The URL
	 *
	 * @return  $this
	 */
	public function installFromUrl($packageUrl)
	{
		$this->debug("Installing extension from URL {$packageUrl}.\n");

		$urlField = $this->getElement("id:install_url", 1000);
		$urlField->clear();
		$urlField->value($packageUrl);
		$this->getElement("xpath://input[contains(@onclick, 'submitbutton4()')]")->click();

		$this->driver->assertContains('Success', $this->message()->text(), "Installation from {$packageUrl} failed.");

		return $this;
	}

	/**
	 * Get the output area
	 *
	 * After installation, the output produced by the installer is rendered in this element.
	 *
	 * @return  Element
	 */
	public function output()
	{
		return $this->getElement("css selector:table.adminform");
	}
}

<?php
namespace Celtic\Testing\Joomla;

class Joomla15AdminPage extends AdminPage
{
	protected $userMenuSelector      = 'css selector:#header-box #module-status';
	protected $messageContainer      = "xpath://dl[@id='system-message']/dd/ul/li";
	protected $headLineSelector      = "css selector:#toolbar-box div.header";

	public function __construct($driver)
	{
		parent::__construct($driver);
		$this->menu = new Joomla15AdminMainMenu($driver);
		$this->toolbar = new Joomla15AdminToolbar($driver);
	}

	/**
	 * @return Joomla15AdminLoginPage
	 */
	public function logout()
	{
		$userMenu = $this->driver->getElement($this->userMenuSelector, 1000);
		$userMenu->byCssSelector('.logout a')->click();

		return new Joomla15AdminLoginPage($this->driver);
	}
}

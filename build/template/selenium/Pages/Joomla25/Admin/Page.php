<?php
namespace Celtic\Testing\Joomla;

class Joomla25AdminPage extends AdminPage
{
	protected $userMenuSelector      = 'css selector:#header-box #module-status';
	protected $messageContainer      = "id:system-message-container";
	protected $headLineSelector      = "css selector:div.page-title h2";

	public function __construct($driver)
	{
		parent::__construct($driver);
		$this->menu = new Joomla25AdminMainMenu($driver);
		$this->toolbar = new Joomla25AdminToolbar($driver);
	}

	/**
	 * @return Joomla25AdminLoginPage
	 */
	public function logout()
	{
		$userMenu = $this->driver->getElement($this->userMenuSelector);
		$userMenu->byCssSelector('.logout a')->click();

		return new Joomla25AdminLoginPage($this->driver);
	}
}

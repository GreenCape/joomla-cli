<?php
namespace Celtic\Testing\Joomla;

class Joomla25AdminMainMenu extends Menu
{
	protected $levelMap = array(
		array(
			'locator' => 'css selector:#header-box #menu',
			'click' => false
		),
		array(
			'locator' => 'xpath:parent::li/ul',
			'click' => false
		),
		array(
			'locator' => 'xpath:following-sibling::ul',
			'click' => false
		),
	);

	protected $pageMap = array(
		'Extension Manager' => array(
			'menu' => 'Extensions/Extension Manager',
			'page' => 'Celtic\\Testing\\Joomla\\Joomla25AdminExtensionManagerInstallPage',
		),
		'Control Panel' => array(
			'menu' => 'System/Control Panel',
			'page' => 'Celtic\\Testing\\Joomla\\Joomla25AdminCPanelPage',
		),
		'default' => array('page' => 'Celtic\\Testing\\Joomla\\Joomla25AdminPage')
	);
}

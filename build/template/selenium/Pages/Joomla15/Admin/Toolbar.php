<?php
namespace Celtic\Testing\Joomla;

class Joomla15AdminToolbar extends Toolbar
{
	protected $pageMap = array(
		'default' => array('page' => 'Celtic\\Testing\\Joomla\\Joomla15AdminPage')
	);

	protected $itemFormat = "xpath://table[@class='toolbar']//a[contains(., '%s')]";
}

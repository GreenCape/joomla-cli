<?php
namespace Celtic\Testing\Joomla;

class Joomla25AdminToolbar extends Toolbar
{
	protected $pageMap = array(
		'default' => array('page' => 'Celtic\\Testing\\Joomla\\Joomla25AdminPage')
	);

	protected $itemFormat = "xpath://div[@id='toolbar']//a[contains(., '%s')]";
}

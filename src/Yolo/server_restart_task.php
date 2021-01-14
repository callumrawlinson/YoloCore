<?php
namespace Yolo;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;
class starttimer extends PluginTask{
	

	private $time;
	public function __construct(Plugin $plugin, $time){
		parent::__construct($plugin);
		$this->time = $time;
	}
	public function onRun($currentTick){
		$this->getOwner()->starttimer($time);
	}
	
	
}
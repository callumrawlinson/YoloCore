<?php

namespace Yolo\task;

use Yolo\gui\TradeGui;
use pocketmine\scheduler\Task;

/**
 * @property TradeGui trade
 */
class TradeTask extends Task {

	/**
	 * TradeTask constructor.
	 * @param TradeGui $trade
	 */
	public function __construct(Core $trade)
	{
		$this->trade = $trade;
	}

	/**
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick)
	{
		$this->trade->startTrade($this->getTaskId());
	}

}
<?php

namespace Yolo\form;

use Yolo\gui\TradeGui;
use Yolo\Core;
use dktapps\pmforms\ModalForm;
use pocketmine\Player;

class TradeRequestForm extends ModalForm
{

	/**
	 * TradeRequestForm constructor.
	 * @param Player $sender
	 */
	public function __construct(Player $sender)
	{
		parent::__construct(
			"Trade Form",
			$sender->getName()." §7Do you accept trade offer from?",
			function (Player $player,bool $choice): void {
				if (isset(Core::getInstance()->tradeRequests[$player->getName()])){
					$sender = Core::getInstance()->tradeRequests[$player->getName()];
					$sender = $player->getServer()->getPlayer($sender);
					unset(Core::getInstance()->tradeRequests[$player->getName()]);
					if ($choice == true){
						if ($sender){
							$gui = new TradeGui($sender,$player);
							$gui->openTrade();
						}
						return;
					}else{
						$sender->sendMessage("§7» §e". $player->getName(). " §c declined your Trade offer");
						return;
					}
				}else{
					$player->sendMessage("§7» No trade offers available to you.");
				}
			},
			"§aAccept",
			"§cDecline"
		);
	}

}

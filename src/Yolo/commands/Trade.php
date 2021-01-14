<?php

namespace Yolo\commands;

use Yolo\compatibility\Command;
use Yolo\form\TradeRequestForm;
use Yolo\Core;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;

class Trade extends Command
{

    /**
     * @var Main
     */
    private $plugin;

    /**
     * Trade constructor.
     */
    public function __construct()
    {
        parent::__construct("trade", "/trade command", "/trade <player>", ["takas"]);
        $this->setParameter(new CommandParameter("player", AvailableCommandsPacket::ARG_TYPE_TARGET, false),0);
        $this->plugin = Core::getInstance();
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player){
            if (isset($args[0])){
                if (($player = $this->plugin->getServer()->getPlayer($args[0])) instanceof Player){
                    if ($player->getName() == $sender->getName()){
                    $sender->sendMessage("§7» §cYou cannot send a trade request to yourself.");
                    return false;
                    }else{
                    $this->plugin->tradeRequests[$player->getName()] = $sender->getName();
                    $sender->sendMessage("§7» §e" . $player->getName() . "§7 trade request was sent.");
                    $player->sendForm(new TradeRequestForm($sender));
                    }
                }else{
                    $sender->sendMessage("§7» §cNo players found.");
                }
            }else{
                $sender->sendMessage("Usage: /trade <player>");
            }
        }
        return true;
    }

}
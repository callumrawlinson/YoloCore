<?php

namespace Yolo\commands;

use Yolo\Manager;
use Yolo\translation\Translation;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class UnmuteIPCommand extends Command {
    
    public function __construct() {
        parent::__construct("unmute-ip");
        $this->description = "Allows the given IP address from sending public chat message.";
        $this->usageMessage = "/unmute-ip <address>";
        $this->setPermission("bs.cmd.unmuteip");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermission($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $muteList = Manager::getIPMutes();
            if (!$muteList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("ipNotMuted"));
                return false;
            }
            $muteList->remove($args[0]);
            $sender->getServer()->broadcastMessage(TextFormat::GREEN . "Address " . TextFormat::AQUA . $args[0] . TextFormat::GREEN . " has been unmuted.");
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

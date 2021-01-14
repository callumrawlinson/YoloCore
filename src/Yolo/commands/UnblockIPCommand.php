<?php

namespace Yolo\commands;

use Yolo\Manager;
use Yolo\translation\Translation;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class UnblockIPCommand extends Command {
    
    public function __construct() {
        parent::__construct("unblock-ip");
        $this->description = "Allows the given IP address from running server command.";
        $this->usageMessage = "/unblock-ip <address>";
        $this->setPermission("bs.cmd.unblockip");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $blockList = Manager::getIPBlocks();
            if (!$blockList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("ipNotBlocked"));
                return false;
            }
            $blockList->remove($args[0]);
            $sender->getServer()->broadcastMessage(TextFormat::GREEN . "Address " . TextFormat::AQUA . $args[0] . TextFormat::GREEN . " has been unblocked.");
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

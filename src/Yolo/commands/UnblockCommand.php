<?php

namespace Yolo\commands;

use Yolo\Manager;
use Yolo\translation\Translation;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class UnblockCommand extends Command {
    
    public function __construct() {
        parent::__construct("unblock");
        $this->description = "Allows the given player from running server commands.";
        $this->usageMessage = "/unblock <name>";
        $this->setPermission("bs.cmd.unblock");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $blockList = Manager::getNameBlocks();
            if (!$blockList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("playerNotBlocked"));
                return false;
            }
            $blockList->remove($args[0]);
            $sender->getServer()->broadcastMessage(TextFormat::AQUA . $args[0] . TextFormat::GREEN . " has been unblocked.");
        } else {
            
        }
    }
}

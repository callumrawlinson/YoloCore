<?php

namespace Yolo\commands;

use Yolo\Manager;
use Yolo\translation\Translation;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class BlockCommand extends Command {
    
    public function __construct() {
        parent::__construct("block");
        $this->description = "Prevents the given player from running server commands.";
        $this->usageMessage = "/block <name> [reason...]";
        $this->setPermission("bs.cmd.block");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $blockList = Manager::getNameBlocks();
            $player = $sender->getServer()->getPlayer($args[0]);
            if ($blockList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("playerAlreadyBlocked"));
                return false;
            }
            if (count($args) == 1) {
                if ($player != null) {
                    $blockList->addBan($player->getName(), null, null, $sender->getName());
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::DARK_AQUA . " has been blocked by $sender");
                    $player->sendMessage(TextFormat::DARK_AQUA . "You have been blocked by $sender.");
                } else {
                    $blockList->addBan($args[0], null, null, $sender->getName());
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $args[0] . TextFormat::DARK_AQUA . " has been blocked by $sender");
                }
            } else if (count($args) >= 2) {
                $reason = "";
                for ($i = 1; $i < count($args); $i++) {
                    $reason .= $args[$i];
                    $reason .= " ";
                }
                $reason = substr($reason, 0, strlen($reason) - 1);
                if ($player != null) {
                    $blockList->addBan($player->getName(), $reason, null, $sender->getName());
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::DARK_AQUA . " has been blocked by $sender Reason: " . TextFormat::AQUA . $reason . TextFormat::DARK_AQUA . ".");
                    $player->sendMessage(TextFormat::DARK_AQUA . "You have been blocked by $sender Reason: " . TextFormat::AQUA . $reason . TextFormat::DARK_AQUA . ".");
                } else {
                    $blockList->addBan($args[0], $reason, null, $sender->getName());
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $args[0] . TextFormat::DARK_AQUA . " has been blocked by $sender Reason: " . TextFormat::AQUA . $reason . TextFormat::DARK_AQUA . ".");
                }
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

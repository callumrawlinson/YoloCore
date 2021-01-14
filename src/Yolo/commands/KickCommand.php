<?php

namespace Yolo\commands;

use Yolo\translation\Translation;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class KickCommand extends Command {
    
    public function __construct() {
        parent::__construct("kick");
        $this->description = "Removes the given player.";
        $this->usageMessage = "/kick <player> [reason...]";
        $this->setPermission("bs.cmd.kick");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $player = $sender->getServer()->getPlayer($args[0]);
            $senderName = $sender->getName();
            if (count($args) == 1) {
                if ($player != null) {
                    $player->kick(TextFormat::AQUA . "You have been kicked from our network\§9kicked by: §b$senderName", false);
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::AQUA . " has been kicked from our network!\n§9Kicked by: §b$senderName");
                } else {
                    $sender->sendMessage(Translation::translate("playerNotFound"));
                }
            } else if (count($args) >= 2) {
                if ($player != null) {
                    $reason = "";
                    for ($i = 1; $i < count($args); $i++) {
                        $reason .= $args[$i];
                        $reason .= " ";
                    }
                    $reason = substr($reason, 0, strlen($reason) - 1);
                    $player->kick(TextFormat::AQUA . "You have been kicked from our network\n§9Kicked by: §b$senderName\n§5Reason: " . TextFormat::AQUA . $reason . TextFormat::AQUA . ".", false);
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::AQUA . " has been kicked from our network\n§9Kicked by: §b$senderName\n§5Reason: " . TextFormat::AQUA . $reason . TextFormat::AQUA . ".");
                } else {
                    $sender->sendMessage(Translation::translate("playerNotFound"));
                }
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

<?php

namespace Yolo\commands;

use Yolo\Manager;
use Yolo\translation\Translation;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class MuteCommand extends Command {
    
    public function __construct() {
        parent::__construct("mute");
        $this->description = "Prevents the given player from sending public chat message.";
        $this->usageMessage = "/mute <player> [reason...]";
        $this->setPermission("bs.cmd.mute");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $player = $sender->getServer()->getPlayer($args[0]);
            $senderName = $sender->getName();
            $muteList = Manager::getNameMutes();
            if ($muteList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("playerAlreadyMuted"));
                return false;
            }
            if (count($args) == 1) {
                if ($player != null) {
                    $muteList->addBan($player->getName(), null, null, $sender->getName());
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::AQUA . " has been muted by $senderName.");
                    $player->sendMessage(TextFormat::AQUA . "You have been muted from our network\n§9Muted by: §b$senderName");
                } else {
                    $muteList->addBan($args[0], null, null, $sender->getName());
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $args[0] . TextFormat::AQUA . " has been muted from our network!\n§9Muted by: §b$senderName.");
                }
            } else if (count($args) >= 2) {
                $reason = "";
                for ($i = 1; $i < count($args); $i++) {
                    $reason .= $args[$i];
                    $reason .= " ";
                }
                $reason = substr($reason, 0, strlen($reason) - 1);
                if ($player != null) {
                    $muteList->addBan($player->getName(), $reason, null, $sender->getName());
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::AQUA . " has been muted by $senderName Reason: " . TextFormat::AQUA . $reason . TextFormat::AQUA . ".");
                    $player->sendMessage(TextFormat::AQUA . "You have been muted from our network!\§9Muted by: §b$senderName\n§5Reason: " . TextFormat::AQUA . $reason . TextFormat::AQUA . ".");
                } else {
                    $muteList->addBan($args[0], $reason, null, $sender->getName());
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $args[0] . TextFormat::AQUA . " has been muted from our network\n§9Muted by: §b$senderName\n§5Reason: " . TextFormat::AQUA . $reason . TextFormat::AQUA . ".");
                }
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

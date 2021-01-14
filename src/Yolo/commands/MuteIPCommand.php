<?php

namespace Yolo\commands;

use Yolo\Manager;
use Yolo\translation\Translation;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class MuteIPCommand extends Command {
    
    public function __construct() {
        parent::__construct("mute-ip");
        $this->description = "Prevents the given IP address from sending public chat message.";
        $this->usageMessage = "/mute-ip <player> <address> [reason...]";
        $this->setPermission("bs.cmd.muteip");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermission($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $ip = filter_var($args[0], FILTER_VALIDATE_IP);
            $player = $sender->getServer()->getPlayer($args[0]);
            $muteList = Manager::getIPMutes();
            if ($muteList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("ipAlreadyMuted"));
                return false;
            }
            if (count($args) == 1) {
                if ($ip != null) {
                    $muteList->addBan($ip, null, null, $sender->getName());
                    foreach ($sender->getServer()->getOnlinePlayers() as $players) {
                        if ($player->getAddress() == $ip) {
                            $players->sendMessage(TextFormat::AQUA . "You have been IP muted by $sender.");
                        }
                    }
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . "Address " . TextFormat::AQUA . $ip . TextFormat::AQUA . " has been muted by $sender.");
                } else {
                    if ($player != null) {
                        $muteList->addBan($player->getAddress(), null, null, $sender->getName());
                        $player->sendMessage(TextFormat::AQUA . "You have been IP muted by $sender.");
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::AQUA . " has been IP muted by $sender.");
                    } else {
                        $sender->sendMessage(Translation::translate("playerNotFound"));
                    }
                }
            } else if (count($args) >= 2) {
                $reason = "";
                for ($i = 1; $i < count($args); $i++) {
                    $reason .= $args[$i];
                    $reason .= " ";
                }
                $reason = substr($reason, 0, strlen($reason) - 1);
                if ($ip != null) {
                    $muteList->addBan($ip, $reason, null, $sender->getName());
                    foreach ($sender->getServer()->getOnlinePlayers() as $players) {
                        if ($players->getAddress() == $ip) {
                            $players->sendMessage(TextFormat::AQUA . "You have been IP muted by $sender. " . TextFormat::AQUA . $reason . TextFormat::AQUA . ".");
                        }
                    }
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . "Address " . TextFormat::AQUA . $ip . TextFormat::AQUA . " has been muted by $sender Reason: " . TextFormat::AQUA . $reason . TextFormat::AQUA . ".");
                } else {
                    if ($player != null) {
                        $muteList->addBan($player->getAddress(), $reason, null, $sender->getName());
                        $player->sendMessage(TextFormat::AQUA . "You have been IP muted for " . TextFormat::AQUA . $reason . TextFormat::AQUA . ".");
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::AQUA . " has been IP muted by $sender Reason: " . TextFormat::AQUA . $reason . TextFormat::AQUA . ".");
                    } else {
                        $sender->sendMessage(Translation::translate("playerNotFound"));
                    }
                }
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

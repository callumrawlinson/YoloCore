<?php

namespace Yolo\commands;

use Yolo\translation\Translation;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class BanIPCommand extends Command {
    
    public function __construct() {
        parent::__construct("ban-ip");
        $this->description = "Prevents the given IP address to use this server.";
        $this->usageMessage  = "/ban-ip <player> <address> [reason...]";
        $this->setPermission("bs.cmd.banip");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $banList = $sender->getServer()->getIPBans();
            if ($banList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("ipAlreadyBanned"));
                return false;
            }
            $ip = filter_var($args[0], FILTER_VALIDATE_IP);
            $player = $sender->getServer()->getPlayer($args[0]);
            if (count($args) == 1) {
                if ($ip != null) {
                    $banList->addBan($ip, null, null, $sender->getName());
                    foreach ($sender->getServer()->getOnlinePlayers() as $onlinePlayers) {
                        if ($onlinePlayers->getAddress() == $ip) {
                            $onlinePlayers->kick(TextFormat::RED . "You have been IP banned from our server!\§3Banned by: §9Staff\n§3Banned by: §9Staff\n§9with no reason.", false);
                        }
                    }
                    $sender->getServer()->broadcastMessage(TextFormat::RED . "Address " . TextFormat::AQUA . $ip . TextFormat::RED . " has been permenantly IP banned from our server\n§3Banned by: §bStaff\n§5with no reason.");
                } else {
                    if ($player != null) {
                        $banList->addBan($player->getAddress(), null, null, $sender->getName());
                        $player->kick(TextFormat::RED . "You have been permenantly IP banned from our server\n§3Banned by: §9Staff\n§9with no reason.", false);
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::RED . " has been IP banned from our server\n§3Banned by: §bStaff\n§5with no reason.");
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
                    $sender->getServer()->getIPBans()->addBan($ip, $reason, null, $sender->getName());
                    foreach ($sender->getServer()->getOnlinePlayers() as $players) {
                        if ($players->getAddress() == $ip) {
                            $players->kick(TextFormat::RED . "You have been permenantly IP banned from our server!\n§3Banned by: §9Staff\n§9Reason: " . TextFormat::AQUA . $reason . TextFormat::RED . ".", false);
                        }
                    }
                    $sender->getServer()->broadcastMessage(TextFormat::RED . "Address " . TextFormat::AQUA . $ip . TextFormat::RED . " has been permenantly IP banned from our server\n§3Banned by: §9Staff\n§9Reason: " . TextFormat::AQUA . $reason . TextFormat::RED . ".");
                } else {
                    if ($player != null) {
                        $banList->addBan($player->getAddress(), $reason, null, $sender->getName());
                        $player->kick(TextFormat::RED . "You have been IP banned from §dWither §5Reason: " . TextFormat::AQUA . $reason . TextFormat::RED . ".", false);
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::RED . " has been IP banned from our server\n§3Banned by: §bStaff\n§5Reason: " . TextFormat::AQUA . $reason . TextFormat::RED . ".");  
                    } else {
                        $sender->sendMessage(Translation::translate("playerNotFound"));
                    }
                }
            } else {
                $sender->sendMessage(Translation::translate("noPermission"));
            }
        }
        return true;
    }
}

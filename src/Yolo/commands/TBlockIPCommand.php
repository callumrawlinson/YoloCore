<?php

namespace Yolo\commands;

use Yolo\Manager;
use Yolo\translation\Translation;
use Yolo\util\date\Countdown;
use DateTime;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class TBlockIPCommand extends Command {
    
    public function __construct() {
        parent::__construct("tlock-ip");
        $this->description = "Temporarily prevents the IP address from running server command.";
        $this->usageMessage = "/tblock-ip <player | address> <timeFormat> [reason...]";
        $this->setPermission("bs.cmd.tempblockip");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 1) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $ip = filter_var($args[0], FILTER_VALIDATE_IP);
            $player = $sender->getServer()->getPlayer($args[0]);
            $blockList = Manager::getIPBlocks();
            if ($blockList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("ipAlreadyBlocked"));
                return false;
            }
            try {
                $expiry = new Countdown($args[1]);
                $expiryToString = Countdown::expirationTimerToString($expiry->getDate(), new DateTime());
                if (count($args) == 2) {
                    if ($ip != null) {
                        $blockList->addBan($ip, null, $expiry->getDate(), $sender->getName());
                        foreach ($sender->getServer()->getOnlinePlayers() as $players) {
                            $players->sendMessage(TextFormat::RED . "You have been IP blocked until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                        }
                        $sender->getServer()->broadcastMessage(TextFormat::RED . "Address " . TextFormat::AQUA . $ip . TextFormat::RED . " has been IP blocked until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                    } else {
                        if ($player != null) {
                            $blockList->addBan($player->getAddress(), null, $expiry->getDate(), $sender->getName());
                            $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::RED . " has been IP blocked until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                            $players->sendMessage(TextFormat::RED . "You have been IP blocked until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                        } else {
                            $sender->sendMessage(Translation::translate("playerrNotFound"));
                        }
                    }
                } else if (count($args) >= 3) {
                    $reason = "";
                    for ($i = 2; $i < count($args); $i++) {
                        $reason .= $args[$i];
                        $reason .= " ";
                    }
                    $reason = substr($reason, 0, strlen($reason) - 1);
                    if ($ip != null) {
                        $blockList->addBan($ip, $reason, $expiry->getDate(), $sender->getName());
                        foreach ($sender->getServer()->getOnlinePlayers() as $players) {
                            $players->sendMessage(TextFormat::RED . "You have been IP blocked for " . TextFormat::AQUA . $reason . TextFormat::RED . " until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                        }
                        $sender->getServer()->broadcastMessage(TextFormat::RED . "Address " . TextFormat::AQUA . $ip . TextFormat::RED . " has been IP blocked for " . TextFormat::AQUA . $reason . TextFormat::RED . " until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                    } else {
                        if ($player != null) {
                            $blockList->addBan($player->getAddress(), $reason, $expiry->getDate(), $sender->getName());
                            $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::RED . " has been IP blocked for " . TextFormat::AQUA . $reason . TextFormat::RED . " until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                            $players->sendMessage(TextFormat::RED . "You have been IP blocked until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                        } else {
                            $sender->sendMessage(Translation::translate("playerrNotFound"));
                        }
                    }
                }
            } catch (InvalidArgumentException $ex) {
                $sender->sendMessage(TextFormat::RED . $ex->getMessage());
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

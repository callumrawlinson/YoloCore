<?php

namespace Yolo\commands;

use Yolo\translation\Translation;
use Yolo\util\date\Countdown;
use DateTime;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class TBanIPCommand extends Command {
    
    public function __construct() {
        parent::__construct("tban-ip");
        $this->description = "Temporarily prevents the given IP address from this server";
        $this->usageMessage = "/tban-ip <player | address> <timeFormat> [reason...]";
        $this->setPermission("bs.cmd.tempbanip");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 1) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $player = $sender->getServer()->getPlayer($args[0]);
            $banList = $sender->getServer()->getIPBans();
            if ($banList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("ipAlreadyBanned"));
                return false;
            }
            try {
                $expiry = new Countdown($args[1]);
                $expiryToString = Countdown::expirationTimerToString($expiry->getDate(), new DateTime());
                $ip = filter_var($args[0], FILTER_VALIDATE_IP);
                if (count($args) == 2) {
                    if ($ip != null) {
                        $banList->addBan($ip, null, $expiry->getDate(), $sender->getName());
                        foreach ($sender->getServer()->getOnlinePlayers() as $onlinePlayers) {
                            if ($onlinePlayers->getAddress() == $ip) {
                                $onlinePlayers->kick(TextFormat::AQUA . "You have been temporarily IP banned,"
                                        . " your IP ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".", false);
                            }
                        }
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . "Address " . TextFormat::AQUA . $ip . TextFormat::AQUA . " has been IP banned  until " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".");
                    } else {
                        if ($player != null) {
                            $player->kick(TextFormat::AQUA . "You have been temporarily IP banned,"
                                        . " your IP ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".", false);
                            $banList->addBan($player->getName(), null, $expiry->getDate(), $sender->getName());
                            $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::AQUA . " has been IP banned until " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".");
                        } else {
                            $sender->sendMessage(Translation::translate("playerNotFound"));
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
                        $banList->addBan($ip, $reason, $expiry->getDate(), $sender->getName());
                        foreach ($sender->getServer()->getOnlinePlayers() as $players) {
                            if ($players->getAddress() == $ip) {
                                $players->kick(TextFormat::AQUA . "You have been temporarily IP banned for " . TextFormat::AQUA . $reason . TextFormat::AQUA . ","
                                    . " your IP ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".", false);
                            }
                        }
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . "Address " . TextFormat::AQUA . $ip . TextFormat::AQUA . " has been IP banned for " . TextFormat::AQUA . $reason . TextFormat::AQUA . " until " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".");
                    } else {
                        if ($player != null) {
                            $banList->addBan($player->getAddress(), $reason, $expiry->getDate(), $sender->getName());
                            $player->kick(TextFormat::AQUA . "You have been temporarily IP banned for " . TextFormat::AQUA . $reason . TextFormat::AQUA . ","
                                    . " your IP ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".", false);
                            $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::AQUA . " has been IP banned for " . TextFormat::AQUA . $reason . TextFormat::AQUA . " until " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".");
                        } else {
                            $sender->sendMessage(Translation::translate("playerNotFound"));
                        }
                    }
                }
            } catch (InvalidArgumentException $e) {
                $sender->sendMessage(TextFormat::AQUA . $e->getMessage());
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

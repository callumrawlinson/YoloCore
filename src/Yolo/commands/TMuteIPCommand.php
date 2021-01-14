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

class TMuteIPCommand extends Command {
    
    public function __construct() {
        parent::__construct("tmute-ip");
        $this->description = "Temporarily prevents the given IP address from sending chat message.";
        $this->usageMessage = "/tmute-ip <player | address> <timeFormat> [reason...]";
        $this->setPermission("bs.cmd.tempmuteip");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermission($sender)) {
            if (count($args) <= 1) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $muteList = Manager::getIPMutes();
            if ($muteList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("ipAlreadyMuted"));
                return false;
            }
            try {
                $player = $sender->getServer()->getPlayer($args[0]);
                $ip = filter_var($args[0], FILTER_VALIDATE_IP);
                $expiry = new Countdown($args[1]);
                $expiryToString = Countdown::expirationTimerToString($expiry->getDate(), new DateTime());
                if (count($args) == 2) {
                    if ($ip != null) {
                        $muteList->addBan($ip, null, $expiry->getDate(), $sender->getName());
                        foreach ($sender->getServer()->getOnlinePlayers() as $players) {
                            if ($player->getAddress() == $ip) {
                                $player->sendMessage(TextFormat::RED . "You have been IP muted until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                            }
                        }
                        $sender->getServer()->broadcastMessage(TextFormat::RED . "Address " . TextFormat::AQUA . $ip . TextFormat::RED . " has been IP muted until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                    } else {
                        $muteList->addBan($player->getAddress(), null, $expiry->getDate(), $sender->getName());
                        $player->sendMessage(TextFormat::RED . "You have been IP muted until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::RED . " has been IP muted until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                    }
                } else if (count($args) >= 3) {
                    $reason = "";
                    for ($i = 2; $i < count($args); $i++) {
                        $reason .= $args[$i];
                        $reason .= " ";
                    }
                    $reason = substr($reason, 0, strlen($reason) - 1);
                    if ($ip != null) {
                        $muteList->addBan($ip, $reason, $expiry->getDate(), $sender->getName());
                        foreach ($sender->getServer()->getOnlinePlayers() as $players) {
                            if ($player->getAddress() == $ip) {
                                $player->sendMessage(TextFormat::RED . "You have been IP muted for " . TextFormat::AQUA . $reason . " until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                            }
                        }
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $ip . TextFormat::RED . " has been IP muted for " . TextFormat::AQUA . $reason . TextFormat::RED . " until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                    } else {
                        $muteList->addBan($player->getAddress(), $reason, $expiry->getDate(), $sender->getName());
                        $player->sendMessage(TextFormat::RED . "You have been IP muted for " . TextFormat::RED . $reason . TextFormat::RED . " until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::RED . " has been IP muted until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                    }
                }
            } catch (InvalidArgumentException $ex) {
                $sender->sendMessage(TextFormat::RED . $ex->getMessage());
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
    }
}

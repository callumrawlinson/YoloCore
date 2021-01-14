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

class TBlockCommand extends Command {
    
    public function __construct() {
        parent::__construct("tblock");
        $this->description = "Temporarily prevents the given player from running server command.";
        $this->usageMessage = "/tblock <name> <timeFormat> [reason...]";
        $this->setPermission("bs.cmd.tempblock");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 1) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $player = $sender->getServer()->getPlayer($args[0]);
            $blockList = Manager::getNameBlocks();
            if ($blockList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("playerAlreadyBlocked"));
                return false;
            }
            try {
                $expiry = new Countdown($args[1]);
                $expiryToString = Countdown::expirationTimerToString($expiry->getDate(), new DateTime());
                if (count($args) == 2) {
                    if ($player != null) {
                        $blockList->addBan($player->getName(), null, $expiry->getDate(), $sender->getName());
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::RED . " has been blocked until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                        $player->sendMessage(TextFormat::RED . "You have been blocked until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                    } else {
                        $blockList->addBan($args[0], null, $expiry->getDate(), $sender->getName());
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $args[0] . TextFormat::RED . " has been blocked until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                    }
                } else if (count($args) >= 3) {
                    $reason = "";
                    for ($i = 2; $i < count($args); $i++) {
                        $reason .= $args[$i];
                        $reason .= " ";
                    }
                    $reason = substr($reason, 0, strlen($reason) - 1);
                    if ($player != null) {
                        $blockList->addBan($player->getName(), $reason, $expiry->getDate(), $sender->getName());
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $player->getName() . TextFormat::RED . " has been blocked for " . TextFormat::AQUA . $reason . TextFormat::RED . " until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                        $player->sendMessage(TextFormat::RED . "You have been blocked for " . TextFormat::AQUA . $reason . TextFormat::RED . " until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                    } else {
                        $blockList->addBan($args[0], $reason, $expiry->getDate(), $sender->getName());
                        $sender->getServer()->broadcastMessage(TextFormat::AQUA . $args[0] . TextFormat::RED . " has been blocked for " . TextFormat::AQUA . $reason . TextFormat::RED . " until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
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

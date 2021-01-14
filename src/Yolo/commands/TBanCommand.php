<?php

namespace Yolo\commands;

use Yolo\translation\Translation;
use Yolo\util\date\Countdown;
use DateTime;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class TBanCommand extends Command {
    
    public function __construct() {
        parent::__construct("tban");
        $this->description = "Temporarily prevents an given player from using this server.";
        $this->usageMessage = "/tban <player> <timeFormat> [reason...]";
        $this->setPermission("bs.cmd.tempban");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 1) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $player = $sender->getServer()->getPlayer($args[0]);
            $playerName = $args[0];
            $senderName = $sender->getName();
            $banList = $sender->getServer()->getNameBans();
            try {
                if ($banList->isBanned($args[0])) {
                    $sender->sendMessage(Translation::translate("playerAlreadyBanned"));
                    return false;
                }
                $expiry = new Countdown($args[1]);
                $expiryToString = Countdown::expirationTimerToString($expiry->getDate(), new DateTime());
                if (count($args) == 2) {
                    if ($player != null) {
                        $playerName = $player->getName();
                        $banList->addBan($player->getName(), null, $expiry->getDate(), $sender->getName());
                        $player->kick(TextFormat::AQUA . "You have been temporarily suspended from our network\n§9Banned by: §b$senderName"
                                . " §3your ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".", false);
                    } else {
                        $banList->addBan($args[0], null, $expiry->getDate(), $sender->getName());
                    }
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $playerName
                            . TextFormat::AQUA . " has been temporarily banned from our network\n§9Banned by: §b$senderName §3Banned until " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".");
                    
                } else if (count($args) >= 3) {
                    $banReason = "";
                    for ($i = 2; $i < count($args); $i++) {
                        $banReason .= $args[$i];
                        $banReason .= " ";
                    }
                    $banReason = substr($banReason, 0, strlen($banReason) - 1);
                    if ($player != null) {
                        $banList->addBan($player->getName(), $banReason, $expiry->getDate(), $sender->getName());
                        $player->kick(TextFormat::AQUA . "You have been temporarily banned from our network!\n§9Banned by: §b$senderName\n§5Reason: " . TextFormat::AQUA . $banReason . TextFormat::AQUA . ","
                                . " §3Your ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".", false);
                    } else {
                        $banList->addBan($args[0], $banReason, $expiry->getDate(), $sender->getName());
                    }
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $playerName
                            . TextFormat::AQUA . " has been temporarily banned from our network\n§9Banned by: §b$senderName\n§5Reason: " . TextFormat::AQUA . $banReason . TextFormat::AQUA . " §3Your ban expires in " . TextFormat::AQUA . $expiryToString . TextFormat::AQUA . ".");
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

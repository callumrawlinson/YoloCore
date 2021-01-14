<?php

namespace Yolo\commands;

use Yolo\Manager;
use Yolo\translation\Translation;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class UnmuteCommand extends Command {
    
    public function __construct() {
        parent::__construct("unmute");
        $this->description = "Allows the player to send public chat message.";
        $this->usageMessage  = "/unmute <player>";
        $this->setPermission("bs.cmd.unmute");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $muteList = Manager::getNameMutes();
            if (!$muteList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("playerNotMuted"));
                return false;
            }
            $muteList->remove($args[0]);
            $sender->getServer()->broadcastMessage(TextFormat::AQUA . $args[0] . TextFormat::GREEN . " has been unmuted. ");
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

<?php

namespace Yolo\commands;

use Yolo\translation\Translation;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class UnbanIPCommand extends Command {
    
    public function __construct() {
        parent::__construct("unban-ip");
        $this->description = "Allows the given IP address to use this server.";
        $this->usageMessage = "/unban-ip <address>";
        $this->setPermission("bs.cmd.pardonip");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $banList = $sender->getServer()->getIPBans();
            if (!$banList->isBanned($args[0])) {
                $sender->sendMessage(Translation::translate("ipNotBanned"));
                return false;
            }
            $banList->remove($args[0]);
            $sender->getServer()->broadcastMessage(TextFormat::GREEN . "Address " . TextFormat::AQUA . $args[0] . TextFormat::GREEN . " has been unbanned.");
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

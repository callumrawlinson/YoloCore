<?php

namespace Yolo\commands;

use Yolo\translation\Translation;
use Yolo\util\ArrayPage;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class BanListCommand extends Command {
    
    public function __construct() {
        parent::__construct("banlist");
        $this->description = "Lists all the players/IP addresses banned from this server.";
        $this->usageMessage = "/banlist <name> | <ip> [page]";
        $this->setPermission("bs.cmd.banlist");
    }
    
    private function forEachLists(CommandSender $user, string $type) : array {
        $array = array();
        switch (strtolower($type)) {
            case "name":
                $nameBans = $user->getServer()->getNameBans();
                foreach ($nameBans->getEntries() as $nameEntry) {
                    $array[count($array)] = $nameEntry->getName();
                }
                break;
            case "ip":
                $ipBans = $user->getServer()->getIPBans();
                foreach ($ipBans->getEntries() as $entry) {
                    $array[count($array)] = $entry->getName();
                }
                break;
            default:
                throw new InvalidArgumentException("Invalid type.");
        }
        return $array;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            try {
                $page = 1;
                $names = $this->forEachLists($sender, strtolower($args[0]));
                $arrayPage = new ArrayPage($names, 5);
                if (count($args) >= 2) {
                    $pageString = $args[1];
                    if (is_numeric($pageString)) {
                        if (intval($pageString) > $arrayPage->getMaxPages() || intval($pageString) <= 0) {
                            $sender->sendMessage(TextFormat::GOLD . "Please enter a valid page number.");
                            return false;
                        }
                        $page = intval($pageString);
                    } else {
                        $sender->sendMessage(TextFormat::GOLD . "\"" . $args[1] . "\" is not a valid page number.");
                        return false;
                    }
                }
                $sender->sendMessage(TextFormat::DARK_GREEN . "--[" . TextFormat::GREEN . "There are " . strval(count($names)) . " " . (strtolower($args[0]) == "name" ? "players" : "IP address") . " banned from this server." . TextFormat::DARK_GREEN . "]--");
                if (count($names) >= 1) {
                    foreach ($arrayPage->yieldFromPage($page) as $nameValue) {
                        $sender->sendMessage(TextFormat::AQUA . $nameValue);
                    }
                } else {
                    $sender->sendMessage(TextFormat::GOLD . "There are nothing to display in this list.");
                }
                $sender->sendMessage(TextFormat::GREEN . "------------[Page (" . strval($page <= $arrayPage->getMaxPages() ? $page : "1") . " / " . strval($arrayPage->getMaxPages()) . ")]------------");
            } catch (InvalidArgumentException $ex) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

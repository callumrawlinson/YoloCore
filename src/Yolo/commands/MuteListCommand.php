<?php

namespace Yolo\commands;

use Yolo\Manager;
use Yolo\translation\Translation;
use Yolo\util\ArrayPage;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class MuteListCommand extends Command {
    
    public function __construct() {
        parent::__construct("mutelist");
        $this->description = "Lists all the players/IP addresses muted from this server.";
        $this->usageMessage = "/mutelist <name> | <ip> [page]";
        $this->setPermission("bs.cmd.mutelist");
    }
    
    private function forEachLists(string $type) : array {
        $array = array();
        switch ($type) {
            case "name":
                $nameMutes = Manager::getNameMutes();
                foreach ($nameMutes->getEntries() as $nameEntry) {
                    $array[count($array)] = $nameEntry->getName();
                }
                break;
            case "ip":
                $ipMutes = Manager::getIPMutes();
                foreach ($ipMutes->getEntries() as $ipEntry) {
                    $array[count($array)] = $ipEntry->getName();
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
                $names = $this->forEachLists(strtolower($args[0]));
                $arrayPage = new ArrayPage($names, 5);
                $page = 1;
                if (count($args) >= 2) {
                    if (is_numeric($args[1])) {
                        if (intval($args[1]) > $arrayPage->getMaxPages() || intval($args[1]) <= 0) {
                            $sender->sendMessage(TextFormat::GOLD . "Please enter a valid page number.");
                            return false;
                        }
                        $page = intval($args[1]);
                    } else {
                        $sender->sendMessage(TextFormat::GOLD . "\"" . $args[1] . "\" is not a valid page number.");
                        return false;
                    }
                }
                $sender->sendMessage(TextFormat::DARK_GREEN . "--[" . TextFormat::GREEN . "There are " . strval(count($names)) . " " . (strtolower($args[0]) == "name" ? "players" : "IP address") . " muted from this server." . TextFormat::DARK_GREEN . "]--");
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

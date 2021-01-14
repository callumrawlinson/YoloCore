<?php

namespace Yolo\commands;

use Yolo\Manager;
use Yolo\translation\Translation;
use Yolo\util\ArrayPage;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class BlockListCommand extends Command {
    
    public function __construct() {
        parent::__construct("blocklist");
        $this->description = "Views the players/IP addresses blocked from this server.";
        $this->usageMessage = "/blocklist <name> | <ip> [page]";
        $this->setPermission("bs.cmd.blocklist");
    }
    
    private function forEachLists(string $type) : array {
        $array = array();
        switch ($type) {
            case "name":
                $blockNames = Manager::getNameBlocks();
                foreach ($blockNames->getEntries() as $nameEntry) {
                    $array[] = $nameEntry->getName();
                }
                break;
            case "ip":
                $blockIps = Manager::getIPBlocks();
                foreach ($blockIps->getEntries() as $ipEntry) {
                    $array[] = $ipEntry->getName();
                }
                break;
            default:
                throw new InvalidArgumentException("Invalid type.");
        }
        return $array;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermission($sender)) {
            if (count($args) <= 0) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            try {
                $page = 1;
                $names = $this->forEachLists($args[0]);
                $arrayPage = new ArrayPage($names, 5);
                if (count($args) >= 2) {
                    if (intval($args[1]) < 0 || intval($args[1]) > $arrayPage->getMaxPages()) {
                        $sender->sendMessage(TextFormat::GOLD . "Please enter a valid page number.");
                        return false;
                    }
                    $page = intval($args[1]);
                }
                $sender->sendMessage(TextFormat::DARK_GREEN . "--[" . TextFormat::GREEN . "There are " . strval(count($names)) . " " . (strtolower($args[0]) == "name" ? "players" : "IP address") . " blocked from this server." . TextFormat::DARK_GREEN . "]--");
                if (count($names) >= 1) {
                    foreach ($arrayPage->yieldFromPage($page) as $nameValue) {
                        $sender->sendMessage(TextFormat::AQUA . $nameValue);
                    }
                } else {
                    $sender->sendMessage(TextFormat::GOLD . "There are nothing to display in this list.");
                }
                $sender->sendMessage(TextFormat::GREEN . "------------[Page (" . strval($page <= $arrayPage->getMaxPages() ? $page : "1") . " / " . strval($arrayPage->getMaxPages()) . ")]------------");
            } catch (InvalidArgumentException $e) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
    }
}

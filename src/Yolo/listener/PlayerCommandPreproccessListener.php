<?php

namespace Yolo\listener;

use Yolo\Manager;
use Yolo\util\date\Countdown;
use DateTime;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\utils\TextFormat;

class PlayerCommandPreproccessListener implements Listener {
    
    public function onPlayerCommandPreproccess(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
        $blockList = Manager::getNameBlocks();
        $str = str_split($event->getMessage());
        if ($str[0] != "/") {
            return;
        }
        if ($blockList->isBanned($player->getName())) {
            $blockMessage = "";
            $entries = $blockList->getEntries();
            $entry = $entries[strtolower($player->getName())];
            if ($entry->getExpires() == null) {
                $reason = $entry->getReason();
                if ($reason != null || $reason != "") {
                    $blockMessage = TextFormat::RED . "You're currently blocked for " . TextFormat::AQUA . $reason . TextFormat::RED . ".";
                } else {
                    $blockMessage = TextFormat::RED . "You're currently blocked.";
                }
            } else {
                $expiry = Countdown::expirationTimerToString($entry->getExpires(), new DateTime());
                if ($entry->hasExpired()) {
                    $blockList->remove($entry->getName());
                    return;
                }
                $blockReason = $entry->getReason();
                if ($blockReason != null || $blockReason != "") {
                    $blockReason = TextFormat::RED . "You're currently blocked for " . TextFormat::AQUA . $blockReason . TextFormat::RED . " until " . TextFormat::AQUA . $expiry . TextFormat::RED . ".";
                } else {
                    $blockReason = TextFormat::RED . "You're currently blocked until " . TextFormat::AQUA . $expiry . TextFormat::RED . ".";
                }
            }
            $event->setCancelled(true);
            $player->sendMessage($blockMessage);
        }
    }
    
    public function onPlayerCommandPreproccess2(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
        $blockList = Manager::getIPBlocks();
        $str = str_split($event->getMessage());
        if ($str[0] != "/") {
            return;
        }
        if ($blockList->isBanned($player->getAddress())) {
            $blockMessage = "";
            $entries = $blockList->getEntries();
            $entry = $entries[strtolower($player->getAddress())];
            if ($entry->getExpires() == null) {
                $reason = $entry->getReason();
                if ($reason != null || $reason != "") {
                    $blockMessage = TextFormat::RED . "You're currently IP blocked for " . TextFormat::AQUA . $reason . TextFormat::RED . ".";
                } else {
                    $blockMessage = TextFormat::RED . "You're currently IP blocked.";
                }
            } else {
                $expiry = Countdown::expirationTimerToString($entry->getExpires(), new DateTime());
                if ($entry->hasExpired()) {
                    $blockList->remove($entry->getName());
                    return;
                }
                $blockReason = $entry->getReason();
                if ($blockReason != null || $blockReason != "") {
                    $blockReason = TextFormat::RED . "You're currently IP blocked for " . TextFormat::AQUA . $blockReason . TextFormat::RED . " until " . TextFormat::AQUA . $expiry . TextFormat::RED . ".";
                } else {
                    $blockReason = TextFormat::RED . "You're currently IP blocked until " . TextFormat::AQUA . $expiry . TextFormat::RED . ".";
                }
            }
            $event->setCancelled(true);
            $player->sendMessage($blockMessage);
        }
    }
}

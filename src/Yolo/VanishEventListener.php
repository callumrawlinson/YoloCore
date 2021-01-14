<?php

namespace Yolo;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

use function array_search;
use function in_array;

class VanishEventListener implements Listener {

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        if(in_array($name, Core::$vanish)){
            unset(Core::$vanish[array_search($name, Core::$vanish)]);
        }
    }
}

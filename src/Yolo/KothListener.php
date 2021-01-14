<?php

namespace Yolo;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;

class KothListener implements Listener{

    private $plugin;

    public function __construct(Core $main)
    {
        $this->plugin = $main;
    }

    public function onRespawn(PlayerRespawnEvent $ev){


        if ($this->plugin->isRunning()){
            $p = $ev->getPlayer();
            $p->addTitle($this->plugin->getData("still_running_title"),$this->plugin->getData("still_running_sub"));
        }
    }

    public function onLeave(PlayerQuitEvent $ev){
        $this->plugin->removePlayer($ev->getPlayer());
    }

    public function onCommand(PlayerCommandPreprocessEvent $ev){
        $cmd = $ev->getMessage()[0];
        if ($cmd === "/spawn" || $cmd === "/hub" || $cmd === "/lobby"){
            $this->plugin->removePlayer($ev->getPlayer());
        }
    }

}

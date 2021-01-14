<?php

namespace Yolo;


use pocketmine\scheduler\Task;

class GameTimer extends Task{

    private $plugin;
    private $arena;

    private $time;

    public function __construct(Core $owner, KothArena $arena){
        $this->plugin = $owner;
        $this->arena = $arena;

        $this->time = $owner->getData("game_time") * 60;
    }

    public function onRun($currentTick){
        $time = $this->time--;
        if ($time < 1){
            $this->arena->endGame();
            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        $msg = $this->plugin->getData("game_bar");
        $msg = str_replace("{time}", gmdate("i:s", $time), $msg);
        $this->arena->sendPopup($msg);

        $this->arena->checkPlayers();
    }


}

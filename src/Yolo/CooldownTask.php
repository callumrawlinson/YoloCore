<?php

namespace Yolo;

use pocketmine\scheduler\Task;

class CooldownTask extends Task{

    private $plugin;

    public function __construct(Core $plugin)
    {
        $this->plugin = $plugin;
    }


    public function onRun($tick){
        $this->plugin->timer();
    }
}

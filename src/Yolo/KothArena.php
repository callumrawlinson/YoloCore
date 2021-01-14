<?php

namespace Yolo;


use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

class KothArena{

    private $running = false;

    private $players = [];

    private $spawns = [];

    private $p1;
    private $p2;

    public $plugin;

    private $timer = null;

    public function __construct(Core $main, $spawns, $capture)
    {
        $this->plugin = $main;
        $this->spawns = $spawns;
        $l = explode(":",$capture["p1"]);
        $this->p1 = new Position($l[0],$l[1],$l[2],$main->getServer()->getLevelByName($l[3]));
        $l = explode(":",$capture["p2"]);
        $this->p2 = new Position($l[0],$l[1],$l[2],$main->getServer()->getLevelByName($l[3]));
    }

    public function inCapture($player){
        $x = $player->getX();
        $z = $player->getZ();
        $y = $player->getY();
        $p1 = $this->p1;
        $p2= $this->p2;
        $minx = min($p1->getX(),$p2->getX());
        $maxx = max($p1->getX(),$p2->getX());
        $minz = min($p1->getZ(),$p2->getZ());
        $maxz = max($p1->getZ(),$p2->getZ());
        $miny = min($p1->getY(),$p2->getY());
        $maxy = max($p1->getY(),$p2->getY());

        // if($minx <= $x && $x <= $maxx && $minz <= $z && $z <= $maxz && $miny <= $y && $y <= $maxy){
        if($x >= $minx and $x <= $maxx and $y >= $miny and $y <= $maxy and $z >= $minz and $z <= $maxz){
            echo "C";
            return true;
        }else{
            echo "F";
            return false;
        }
    }

    public function preStart(){
        $task = new PreGameTimer($this->plugin,$this);
        $handler = $this->plugin->getScheduler()->scheduleRepeatingTask($task,20);
        $task->setHandler($handler);
        $this->timer = $task;
        $this->running = true;
    }

    public function startGame(){
        $task = new GameTimer($this->plugin,$this);
        $handler = $this->plugin->getScheduler()->scheduleRepeatingTask($task,20);
        $task->setHandler($handler);
        $this->timer = $task;
    }


    public function addPlayer(Player $player){
        $this->players[$player->getName()] = $this->plugin->getData("capture_time");
        $this->sendRandomSpot($player);
    }

    public function sendRandomSpot(Player $player){
        $rand = $this->spawns[array_rand($this->spawns)];
        $i = explode(":", $rand);
        $player->teleport(new Position((int)$i[0], (int)$i[1], (int)$i[2], $this->plugin->getServer()->getLevelByName((string)$i[3])));
    }

    public function resetAllPlayers(){
        foreach ($this->players as $player => $time){
            $p = $this->plugin->getServer()->getPlayer($player);
            if ($p instanceof Player){
                $p->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
            }
            unset($this->players[$player]);
        }
    }

    public function resetCapture(Player $player){
        if (isset($this->players[$player->getName()])){
            $this->players[$player->getName()] = $this->plugin->getData("capture_time");
        }
    }

    public function resetGame(){
        $this->resetAllPlayers();
        $this->players = [];
        $this->running = false;
        $timer = $this->timer;
        if ($timer instanceof PluginTask && !$timer->getHandler()->isCancelled()) $timer->getHandler()->cancel();
        $this->timer = null;
    }

    public function isRunning() : bool {
        return $this->running;
    }

    public function checkPlayers(){
        foreach ($this->players as $player => $time){
            $p = $this->plugin->getServer()->getPlayer($player);
            if ($p instanceof Player){
                if ($this->inCapture($p)){
                    $time = --$this->players[$player];
                    $this->sendProgress($p,$time);
                    if ($time < 1){
                        $this->won($p);
                    }
                }
            }else{
                unset($this->players[$player]);
            }
        }
    }

    public function won(Player $player){
        $prefix = $this->plugin->prefix();
        $msg = $this->plugin->getData("win");
        $msg = str_replace("{player}",$player->getName(),$msg);
        $msg = $prefix.$msg;
        $this->plugin->getServer()->broadcastMessage($msg);
        $this->giveRewards($player);
        $this->endGame();
    }

    public function removePlayer(Player $player){
        if (isset($this->players[$player->getName()])) unset($this->players[$player->getName()]);
    }

    public function sendProgress(Player $player, $time){
        $tip = $this->plugin->getData("progress");
        $max = $this->plugin->getData("capture_time");
        $time = $this->plugin->getData("capture_time") - $time;
        $percent = (($time / $max)*100).'%';
        $player->sendTip(str_replace("{percent}",$percent,$tip));
    }

    public function endGame(){
        foreach ($this->players as $player => $time){
            $p = $this->plugin->getServer()->getPlayer($player);
            if ($p instanceof Player){
                $p->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
                $p->sendMessage($this->plugin->getData("end_game"));
            }
            unset($this->players[$player]);
        }
        $this->resetGame();
    }

    public function sendPopup($msg){
        foreach ($this->players as $player => $time){
            $p = $this->plugin->getServer()->getPlayer($player);
            if ($p instanceof Player) $p->sendPopup($msg);
        }
    }

    public function giveRewards(Player $player){
        $rewards = $this->plugin->getRewards();
        $name = $player->getName();
        foreach ($rewards as $key => $reward){
            $reward = str_replace("{player}",$name,$reward);
            $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(),$reward);
        }
    }
}

<?php

namespace Yolo;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class KothCommand extends Command{

    private $plugin;

    public function __construct($name, Core $main)
    {
        parent::__construct($name, "");
        $this->plugin = $main;
    }

    public function execute(CommandSender $sender, $commandLabel, array $args){
        if ($sender instanceof Player){
            if (isset($args[0])){
                if (strtolower($args[0]) === "join"){
                    if ($this->plugin->sendToKoth($sender)){
                        $sender->sendMessage($this->plugin->getData("joined"));
                    }else{
                        $sender->sendMessage($this->plugin->getData("not_running"));
                    }
                    return true;
                } else if (strtolower($args[0]) === "setspawn"){
                    if (!$sender->hasPermission("koth.start")) return true;
                    $this->plugin->setPoint($sender,"spawn");
                    $sender->sendMessage("Successfully Added spawnpoint!");
                    return true;
                } else if (strtolower($args[0]) === "p1"){
                    if (!$sender->hasPermission("koth.start")) return true;
                    $this->plugin->setPoint($sender,"p1");
                    $sender->sendMessage("Successfully Added p1 point (make sure to set p2)");
                } else if (strtolower($args[0]) === "p2"){
                    if (!$sender->hasPermission("koth.start")) return true;
                    $this->plugin->setPoint($sender,"p2");
                    $sender->sendMessage("Successfully Added p2 point!");
                } else if (strtolower($args[0]) === "start"){
                    if (!$sender->hasPermission("koth.start")) return true;
                    if ($this->plugin->startArena()){
                        $sender->sendMessage("KOTH Event Started!");
                    }else{
                        $sender->sendMessage("No KOTH Arena fully setup...");
                    }
                } else if (strtolower($args[0]) === "stop"){
                    if (!$sender->hasPermission("koth.stop")) return true;
                    if ($this->plugin->forceStop()){
                        $sender->sendMessage("KOTH Event Force stopped");
                    }else{
                        $sender->sendMessage("No KOTH Arena fully setup...");
                    }

                } else{
                    if ($sender->isOp()) $this->sendHelp($sender);
                    if (!$sender->isOp()) $sender->sendMessage($this->plugin->prefix()."Join game with /koth join");
                }
            }else{
                if ($sender->isOp()) $this->sendHelp($sender);
                if (!$sender->isOp()) $sender->sendMessage($this->plugin->prefix()."Join game with /koth join");
            }

        }else{
            if (isset($args[0])){
                if (strtolower($args[0]) === "start"){
                    if ($this->plugin->startArena()){
                        $sender->sendMessage("KOTH Event Started!");
                    }else{
                        $sender->sendMessage("No KOTH Arena fully setup...");
                    }
                    return true;
                } else if (strtolower($args[0]) === "stop"){
                    if ($this->plugin->forceStop()){
                        $sender->sendMessage("KOTH Event Force stopped");
                    }else{
                        $sender->sendMessage("No KOTH Arena fully setup...");
                    }
                    return true;
                }
            }
            $sender->sendMessage("Error- Cant run that in console!");
        }

        return true;
    }

    public function sendHelp(CommandSender $sender){
	    $sender->sendMessage("            §8[§c+§8]§8[§c+§8]§8[§c+§8]§cKoth Help§8[§c+§8]§8[§c+§8]§8[§c+§8]");
        $sender->sendMessage("§8[§c+§8]§c Make sure to run first 3 commands to fully setup Arena§8[§c+§8]");
        $sender->sendMessage("§8[§c+§8]§c(1) /koth setspawn - set as many spawn points as your want!§8[§c+§8]");
        $sender->sendMessage("§8[§c+§8]§c(2) /koth p1 - set point 1 for capture area§8[§c+§8]");
        $sender->sendMessage("§8[§c+§8]§c(3) /koth p2 - set point 2 for capture area§8[§c+§8]");
        $sender->sendMessage("§8[§c+§8]§c /koth start - starts KOTH Match§8[§c+§8]");
        $sender->sendMessage("§8[§c+§8]§c /koth stop - force stop KOTH Math§8[§c+§8]");
        $sender->sendMessage("§8[§c+§8]§c Reload server or restart to setup Arena fully!§8[§c+§8]");
        $sender->sendMessage("            §8[§c+§8]§8[§c+§8]§8[§c+§8]§cKoth Help§8[§c+§8]§8[§c+§8]§8[§c+§8]");
    }


}

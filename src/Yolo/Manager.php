<?php

namespace Yolo;

use Yolo\permission\BlockList;
use Yolo\permission\MuteList;

class Manager {
    
    public static function getNameMutes() : MuteList {
        $muteList = new MuteList("muted-players.txt");
        $muteList->load();
        return $muteList;
    }
    
    public static function getIPMutes() : MuteList {
        $muteList = new MuteList("muted-ips.txt");
        $muteList->load();
        return $muteList;
    }
    
    public static function getNameBlocks() : BlockList {
        $blockList = new BlockList("blocked-players.txt");
        $blockList->load();
        return $blockList;
    }
    
    public static function getIPBlocks() : BlockList {
        $blockList = new BlockList("blocked-ips.txt");
        $blockList->load();
        return $blockList;
    }
}
<?php

namespace Yolo\permission;

use pocketmine\permission\BanEntry;

class MuteEntry extends BanEntry {
    
    public function __construct(string $name) {
        parent::__construct($name);
        $this->setReason("Muted by an operator.");
    }
}

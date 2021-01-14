<?php

namespace Yolo\permission;

use pocketmine\permission\BanEntry;

class BlockEntry extends BanEntry {
    
    public function __construct(string $name) {
        parent::__construct($name);
        $this->setReason("Blocked by an operator.");
    }
}

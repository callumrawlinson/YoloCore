<?php

namespace Yolo\permission;

use DateTime;
use InvalidArgumentException;
use pocketmine\permission\BanEntry;
use pocketmine\permission\BanList;

class MuteList extends BanList {
    
    public function add(BanEntry $entry) {
        if ($entry instanceof MuteEntry) {
            throw new InvalidArgumentException();
        }
        parent::add($entry);
    }
    
    /**
     * 
     * @param string $target
     * @param string $reason
     * @param \DateTime $expires
     * @param string $source
     */
    public function addBan(string $target, string $reason = null, \DateTime $expires = null, string $source = null) : BanEntry{
        $entry = new MuteEntry($target);
        $entry->setReason($reason ?? $entry->getReason());
        $entry->setExpires($expires);
        $entry->setSource($source ?? $entry->getSource());
        parent::addBan($entry->getName(), $entry->getReason(), $entry->getExpires(), $entry->getSource());
    return $entry;
    }
}

<?php

namespace Yolo\gui;

use Yolo\Core;
use Yolo\task\TradeTask;
use muqsit\invmenu\InvMenu;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\Player;

class TradeGui{

	/** @var InvMenu */
    public $menu;
	/** @var Player */
	private $player;
    /** @var Player */
    private $sender;
    /** @var int */
    private $starting = 0;

    /** @var array */
    private $a1 = [4,13,22,31,40,49];
	/** @var array */
    private $a2 = [0,1,2,3,9,10,11,12,18,19,20,21,27,28,29,30,36,37,38,39,46,47,48];
	/** @var array */
    private $a3 = [5,6,7,8,14,15,16,17,23,24,25,26,32,33,34,35,41,42,43,44,50,51,52];


	/**
	 * TradeGui constructor.
	 * @param Player $sender
	 * @param Player $player
	 */
	public function __construct(Player $sender, Player $player){
		$this->player = $player;
		$this->sender = $sender;
        $this->plugin = Main::getInstance();
        $sname = strlen($this->sender->getName()) > 8 ? substr($this->sender->getName(),0,8) . "..." : $this->sender->getName();
        $pname = strlen($this->player->getName()) > 8 ? substr($this->player->getName(),0,8) . "..." : $this->player->getName();
    	$this->menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST)->setName($sname . str_repeat(" ",11) . $pname)->setListener([$this, "inventoryEvent"])->setInventoryCloseListener([$this,"inventoryCloseEvent"]);
    }


	/**
	 *
	 */
	public function openTrade(){
        $this->menu->send($this->sender);
        $this->menu->send($this->player);
        $this->menu->getInventory()->setItem(45,Item::get(236,14,1)->setCustomName("§aAccept"));
        $this->menu->getInventory()->setItem(53,Item::get(236,14,1)->setCustomName("§aAccept"));
        $this->menu->getInventory()->setItem(4,Item::get(20,0,1)->setCustomName(" "));
        $this->menu->getInventory()->setItem(13,Item::get(20,0,1)->setCustomName(" "));
        $this->menu->getInventory()->setItem(22,Item::get(20,0,1)->setCustomName(" "));
        $this->menu->getInventory()->setItem(31,Item::get(20,0,1)->setCustomName(" "));
        $this->menu->getInventory()->setItem(40,Item::get(20,0,1)->setCustomName(" "));
        $this->menu->getInventory()->setItem(49,Item::get(20,0,1)->setCustomName(" "));
    }

	/**
	 * @param Player $player
	 * @param Item $item
	 * @param Item $width
	 * @param SlotChangeAction $action
	 * @return bool
	 */
	public function inventoryEvent(Player $player, Item $item, Item $width, SlotChangeAction $action):bool{
        if($action->getSlot() == 45 && $player->getName() == $this->sender->getName()){
            if($item->getDamage() == 14){
                $this->menu->getInventory()->setItem(45,Item::get(236,5,1)->setCustomName("§cCancel"));
                if($this->menu->getInventory()->getContents()[53]->getDamage() == 5){
                    if($this->starting === 0){
						$this->plugin->getScheduler()->scheduleRepeatingTask(new TradeTask($this),20);
                        return false;
                    }
                }
                return false;
            }
            $this->menu->getInventory()->setItem(45,Item::get(236,14,1)->setCustomName("§aAccept"));
            if($this->starting === 1){
                    $this->plugin->getScheduler()->cancelTask($this->task);
                        $this->cancelTrade();
                        return false;
                }
            return false;
        }
        elseif($action->getSlot() == 53 && $player->getName() == $this->player->getName()){
            if($item->getDamage() == 14){
                $this->menu->getInventory()->setItem(53,Item::get(236,5,1)->setCustomName("§cCancel"));
                if($this->menu->getInventory()->getContents()[45]->getDamage() == 5){
                    if($this->starting === 0){
                        $this->plugin->getScheduler()->scheduleRepeatingTask(new TradeTask($this),20);
                        return false;
                    }
                }
                return false;
            }
            $this->menu->getInventory()->setItem(53,Item::get(236,14,1)->setCustomName("§aAccept"));
            if($this->starting === 1){
                        $this->cancelTrade();
                        return false;
                }
            return false;
        }
        elseif(in_array($action->getSlot(),$this->a1)){
            return false;
        }elseif($player->getName() == $this->sender->getName() && in_array($action->getSlot(),$this->a2)){
            if($this->starting === 1){
                $this->cancelTrade();
            }
            return true;
        }elseif($player->getName() == $this->player->getName() && in_array($action->getSlot(),$this->a3)){
            if($this->starting === 1){
                $this->cancelTrade();
            }
            return true;
        }else{
            return false;
        }
    }

	/**
	 * @var int
	 */
	private $a = 5;

	/**
	 * @param $tid
	 */
	public function startTrade($tid){
        $this->task = $tid;
        $this->starting = 1;
        $this->menu->getInventory()->setItem(4,Item::get(241,5,$this->a)->setCustomName(" "));
        $this->menu->getInventory()->setItem(13,Item::get(241,5,$this->a)->setCustomName(" "));
        $this->menu->getInventory()->setItem(22,Item::get(241,5,$this->a)->setCustomName(" "));
        $this->menu->getInventory()->setItem(31,Item::get(241,5,$this->a)->setCustomName(" "));
        $this->menu->getInventory()->setItem(40,Item::get(241,5,$this->a)->setCustomName(" "));
        $this->menu->getInventory()->setItem(49,Item::get(241,5,$this->a)->setCustomName(" "));
        $this->a--;
        if($this->a < 0){
            $this->successfulTrade();
        }
       # $this->starting = 0;
    }

	/**
	 * @var int
	 */
	public $finish = 1;

	/**
	 *
	 */
	public function successfulTrade(){
        $this->plugin->getScheduler()->cancelTask($this->task);
        $items = $this->menu->getInventory()->getContents();
        foreach($items as $index => $item){
            if(in_array($index,$this->a2)){
                $this->player->getInventory()->addItem($item);
            }
            if(in_array($index,$this->a3)){
                    $this->sender->getInventory()->addItem($item);
                }
            }
            $this->sender->sendMessage("§aTrading Successfully!");
            $this->player->sendMessage("§aTrading Successfully!");
            $this->finish = 0;
            $this->sender->removeWindow($this->menu->getInventory($this->sender));
            $this->player->removeWindow($this->menu->getInventory($this->player));
    }

	/**
	 *
	 */
	public function cancelTrade(){
        $this->plugin->getScheduler()->cancelTask($this->task);
        $this->menu->getInventory()->setItem(45,Item::get(236,14,1)->setCustomName("§aAccept"));
        $this->menu->getInventory()->setItem(53,Item::get(236,14,1)->setCustomName("§aAccept"));
        $this->menu->getInventory()->setItem(4,Item::get(20,0,1)->setCustomName(" "));
        $this->menu->getInventory()->setItem(13,Item::get(20,0,1)->setCustomName(" "));
        $this->menu->getInventory()->setItem(22,Item::get(20,0,1)->setCustomName(" "));
        $this->menu->getInventory()->setItem(31,Item::get(20,0,1)->setCustomName(" "));
        $this->menu->getInventory()->setItem(40,Item::get(20,0,1)->setCustomName(" "));
        $this->menu->getInventory()->setItem(49,Item::get(20,0,1)->setCustomName(" "));
        $this->starting = 0;
        $this->a = 5;
    }

	/**
	 * @param Player $player
	 * @param $env
	 * @return bool
	 */
	public function inventoryCloseEvent(Player $player, $env):bool{
        $items = $env->getContents();
        if($this->finish == 1){
        foreach($items as $index => $item){
            if($player->getName() == $this->sender->getName()){
                if(in_array($index,$this->a2)){
                    $player->getInventory()->addItem($item);
                }
            }elseif($player->getName() == $this->player->getName()){
                if(in_array($index,$this->a3)){
                    $player->getInventory()->addItem($item);
                }
            }
        }
        if($player->getName() == $this->sender->getName()){
            $this->player->removeWindow($this->menu->getInventory($this->player));
            return true;
        }else{
            $this->sender->removeWindow($this->menu->getInventory($this->sender));
            return true;
        }
    }
    return true;
    }
}
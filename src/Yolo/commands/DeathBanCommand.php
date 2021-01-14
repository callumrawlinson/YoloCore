<?php
namespace Yolo\commands;
use Yolo\translation\Translation;
use Yolo\util\date\Countdown;
use DateTime;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
class DeathBanCommand extends Command {
    
    public function __construct() {
        parent::__construct("deathban");
        $this->description = "Temporarily prevents an given player from using this server.";
        $this->usageMessage = "/deathban <player> <timeFormat> [reason...]";
        $this->setPermission("deathban.command");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) <= 1) {
                $sender->sendMessage(Translation::translateParams("usage", array($this)));
                return false;
            }
            $player = $sender->getServer()->getPlayer($args[0]);
            $playerName = $args[0];
            $senderName = $sender->getName();
            $banList = $sender->getServer()->getNameBans();
            try {
                if ($banList->isBanned($args[0])) {
                    $sender->sendMessage(Translation::translate("playerAlreadyBanned"));
                    return false;
                }
                $expiry = new Countdown($args[1]);
                $expiryToString = Countdown::expirationTimerToString($expiry->getDate(), new DateTime());
                if (count($args) == 2) {
                    if ($player != null) {
                        $playerName = $player->getName();
                        $senderName = $sender->getName();
                        $banList->addBan($player->getName(), null, $expiry->getDate(), $sender->getName());
                        $player->kick(TextFormat::RED . "§7[§6Void§bDeath§cBan§7]\n§aYou are death banned because you died! §6You will be unbanned in " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".", false);
                    } else {
                        $banList->addBan($args[0], null, $expiry->getDate(), $sender->getName());
                    }
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $playerName
                            . TextFormat::RED . " §cdied and is now death banned until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                    
                } else if (count($args) >= 3) {
                    $banReason = "";
                    for ($i = 2; $i < count($args); $i++) {
                        $banReason .= $args[$i];
                        $banReason .= " ";
                    }
                    $banReason = substr($banReason, 0, strlen($banReason) - 1);
                    if ($player != null) {
                        $banList->addBan($player->getName(), $banReason, $expiry->getDate(), $sender->getName());
                        $player->kick(TextFormat::RED . "§7[§6Void§bDeath§cBan§7]\n§aYou are death banned because you died!\n§6You will be unbanned in " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".", false);
                    } else {
                        $banList->addBan($args[0], $banReason, $expiry->getDate(), $sender->getName());
                    }
                    $sender->getServer()->broadcastMessage(TextFormat::AQUA . $playerName
                            . TextFormat::RED . " §cdied and is now death banned until " . TextFormat::AQUA . $expiryToString . TextFormat::RED . ".");
                }
            } catch (InvalidArgumentException $e) {
                $sender->sendMessage(TextFormat::RED . $e->getMessage());
            }
        } else {
            $sender->sendMessage(Translation::translate("noPermission"));
        }
        return true;
    }
}

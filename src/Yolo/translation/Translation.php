<?php

namespace Yolo\translation;

use Yolo\exception\TranslationFailedException;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

class Translation {
    
    public static function translate(string $translation) : string {
        switch ($translation) {
            case "noPermission":
                return TextFormat::RED . "Not showing command information due to self-leak issues.";
            case "playerNotFound":
                return TextFormat::GOLD . "§2Player is not online.";
            case "playerAlreadyBanned":
                return TextFormat::GOLD . "§2Player is already banned.";
            case "ipAlreadyBanned":
                return TextFormat::GOLD . "§2Player is already IP banned.";
            case "ipNotBanned":
                return TextFormat::GOLD . "§2IP address is not banned.";
            case "ipAlreadyMuted":
                return TextFormat::GOLD . "§2IP address is already muted.";
            case "playerNotBanned":
                return TextFormat::GOLD . "§2Player is not banned.";
            case "playerAlreadyMuted":
                return TextFormat::GOLD . "§2Player is already muted.";
            case "playerNotMuted":
                return TextFormat::GOLD . "§2Player is not muted.";
            case "ipNotMuted":
                return TextFormat::GOLD . "§2IP address is not muted.";
            case "playerAlreadyBlocked":
                return TextFormat::GOLD . "§2Player is already blocked.";
            case "playerNotBlocked":
                return TextFormat::GOLD . "§2Player is not blocked.";
            case "ipAlreadyBlocked":
                return TextFormat::GOLD . "§2IP address is already blocked.";
            case "ipNotBlocked":
                return TextFormat::GOLD . "§2IP address is not blocked.";
            default:
                throw new TranslationFailedException("Failed to translate.");
        }
    }
    
    public static function translateParams(string $translation, array $parameters) : string {
        if (empty($parameters)) {
            throw new InvalidArgumentException("Parameter is empty.");
        }
        switch ($translation) {
            case "usage":
                $command = $parameters[0];
                if ($command instanceof Command) {
                    return TextFormat::DARK_GREEN . "§aPlease use: " . TextFormat::AQUA . $command->getUsage();
                } else {
                    throw new InvalidArgumentException("Parameter index 0 must be the type of Command.");
                }
        }
    }
}

<?php
declare(strict_types = 1);

/**
 *     _____                    _   _           _
 *    /  ___|                  | | | |         | |
 *    \ `--.  ___ ___  _ __ ___| |_| |_   _  __| |
 *     `--. \/ __/ _ \| '__/ _ \  _  | | | |/ _` |
 *    /\__/ / (_| (_) | | |  __/ | | | |_| | (_| |
 *    \____/ \___\___/|_|  \___\_| |_/\__,_|\__,_|
 *
 * scoreboard, a Scoreboard plugin for PocketMine-MP
 * Copyright (c) 2018 JackMD  < https://github.com/JackMD >
 *
 * Discord: JackMD#3717
 * Twitter: JackMTaylor_
 *
 * This software is distributed under "GNU General Public License v3.0".
 * This license allows you to use it and/or modify it but you are not at
 * all allowed to sell this plugin at any cost. If found doing so the
 * necessary action required would be taken.
 *
 * scoreboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License v3.0 for more details.
 *
 * You should have received a copy of the GNU General Public License v3.0
 * along with this program. If not, see
 * <https://opensource.org/licenses/GPL-3.0>.
 * ------------------------------------------------------------------------
 */

namespace Yolo\commands;

use Yolo\libs\JackMD\ScoreFactory\ScoreFactory;
use Yolo\Core;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

class ScoreHudCommand extends PluginCommand{

	/** @var scoreboard */
	private $plugin;

	/**
	 * scoreboardCommand constructor.
	 *
	 * @param scoreboard $plugin
	 */
	public function __construct(Core $plugin){
		parent::__construct("scoreboard", $plugin);
		$this->setDescription("Shows Scoreboard Commands");
		$this->setUsage("/scoreboard (on/off)");
		$this->setAliases(["sb"]);
		$this->setPermission("sh.command.sh");

		$this->plugin = $plugin;
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param array         $args
	 * @return bool|mixed
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(Core::PREFIX . "§cYou can only use this command in-game.");

			return false;
		}
		if(!isset($args[0])){
			$sender->sendMessage(Core::PREFIX . "§cUsage: /scoreboard <on|off|about|help>");

			return false;
		}
		switch($args[0]){
			case "helpmehelpyou":
				$sender->sendMessage(Core::PREFIX . "§6Score§eHud §av" . $this->plugin->getDescription()->getVersion() . "§a.Plugin by §dJackMD§a. Contact on §bTwitter: JackMTaylor_ §aor §bDiscord: JackMD#3717§a.");
				break;

			case "on":
				if(isset($this->plugin->disabledscoreboardPlayers[strtolower($sender->getName())])){
					unset($this->plugin->disabledscoreboardPlayers[strtolower($sender->getName())]);
					$sender->sendMessage(Core::PREFIX . "§aSuccessfully enabled scoreboard.");
				}else{
					$sender->sendMessage(Core::PREFIX . "§cscoreboard is already enabled for you.");
				}
				break;

			case "off":
				if(!isset($this->plugin->disabledscoreboardPlayers[strtolower($sender->getName())])){
					ScoreFactory::removeScore($sender);

					$this->plugin->disabledscoreboardPlayers[strtolower($sender->getName())] = 1;
					$sender->sendMessage(Core::PREFIX . "§cSuccessfully disabled scoreboard.");
				}else{
					$sender->sendMessage(Core::PREFIX . "§ascoreboard is already disabled for you.");
				}
				break;

			case "helpmehelpyou2":
			default:
				$sender->sendMessage(Core::PREFIX . "§cUsage: /scoreboard <on|off|about|help>");
				break;
		}

		return false;
	}
}

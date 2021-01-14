<?php
namespace Yolo;

use Yolo\task;
use Yolo\CooldownTask;
use Yolo\libs\JackMD\ConfigUpdater\ConfigUpdater;
use Yolo\libs\JackMD\ScoreFactory\ScoreFactory;
use Yolo\addon\AddonManager;
use Yolo\commands\ScoreHudCommand;
use Yolo\task\ScoreUpdateTask;
use Yolo\updater\AddonUpdater;
use Yolo\utils\Utils;
use Yolo\libs\Yolo\UpdateNotifier\UpdateNotifier;
use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use DaPigGuy\PiggyCustomEnchants\Main as CE;
use Yolo\commands\Trade;
use Yolo\commands\BanCommand;
use Yolo\commands\BanIPCommand;
use Yolo\commands\BanListCommand;
use Yolo\commands\BlockCommand;
use Yolo\commands\BlockIPCommand;
use Yolo\commands\BlockListCommand;
use Yolo\commands\KickCommand;
use Yolo\commands\MuteCommand;
use Yolo\commands\MuteIPCommand;
use Yolo\commands\MuteListCommand;
use Yolo\commands\PardonCommand;
use Yolo\commands\PardonIPCommand;
use Yolo\commands\TBanCommand;
use Yolo\commands\TBanIPCommand;
use Yolo\commands\TBlockCommand;
use Yolo\commands\TBlockIPCommand;
use Yolo\commands\TMuteCommand;
use Yolo\commands\TMuteIPCommand;
use Yolo\commands\UnbanCommand;
use Yolo\commands\UnbanIPCommand;
use Yolo\commands\UnblockCommand;
use Yolo\commands\UnblockIPCommand;
use Yolo\commands\UnmuteCommand;
use Yolo\commands\UnmuteIPCommand;
use Yolo\listener\PlayerChatListener;
use Yolo\listener\PlayerCommandPreproccessListener;
use Yolo\listener\PlayerPreLoginListener;
use Yolo\listener\MuteEvent;
use Yolo\commands\DeathBanCommand;
use pocketmine\permission\Permission;
use jojoe77777\FormAPI\SimpleForm;
use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Zombie;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\scheduler\CallbackTask;
    use pocketmine\level\Position;
    use pocketmine\level\Level;
use pocketmine\event\player\PlayerChatEvent;
 use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\math\Vector2;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\tile\Chest;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use muqsit\invmenu\inventories\BaseFakeInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\inventories\DoubleChestInventory;
use muqsit\invmenu\inventories\LargeChestInventory;
use muqsit\invmenu\tasks\DelayedFakeBlockDataNotifyTask;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\inventory\ChestInventory;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use function array_fill;
use function array_merge;
use function array_rand;
use function is_null;
use function mt_rand;
use function str_replace;
use function array_search;
use function in_array;
use function strtolower;
use function sendFullPlayerListData;
class Core extends PluginBase implements Listener{

	public $auction;
	public $auctionItems = [];
	public $clickItem = [];
	public $block1 = [];
	public $block2 = [];
	public $inventory = [];
	public $ahCounter = [];
	public $config;
    public static $vanish = [];
    public static $nametagg = [];
    public $pk;
    protected static $main;
	private $settings;
	private $messageManager = null;
	private $listener = null;
	public $taggedPlayers = [];
	const SETTINGS_FILE = "Settings.yml";
	private $piggyCE;
    public $myConfig;
    public $noVoid;
    private $maskManager;
    private $fly = array();
    private $god = array();
	private $intial = 0;
	private $msgs;
	private $logged;
    private $msg;
    private $c;
    private $arena = null;
    private $fac;
	public const PREFIX = "§8[§4Scoreboards§8]§r ";
	private const CONFIG_VERSION = 8;
	private const SCOREHUD_VERSION = 1;
	public static $addonPath = "";
	private static $instance = null;
	private $addonUpdater;
	private $addonManager;
	public $disabledScoreHudPlayers = [];
	private $scoreHudConfig;
	private $scoreboards = [];
	private $scorelines = [];
	public $tradeRequests = [];
 
    private function removeCommand(string $command) {
        $commandMap = $this->getServer()->getCommandMap();
        $cmd = $commandMap->getCommand($command);
        if ($cmd == null) {
            return;
        }
        $cmd->setLabel("");
        $cmd->unregister($commandMap);
    }
    
    private function initializeCommands() {
        $commands = array("ban", "banlist", "pardon", "pardon-ip", "ban-ip", "kick", "mute");
        for ($i = 0; $i < count($commands); $i++) {
            $this->removeCommand($commands[$i]);
        }
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->registerAll("Core", array(
            new BanCommand(),
            new BanIPCommand(),
            new BanListCommand(),
            new BlockCommand(),
            new BlockIPCommand(),
            new BlockListCommand(),
            new KickCommand(),
            new MuteCommand(),
            new MuteIPCommand(),
            new MuteListCommand(),
            new PardonCommand(),
            new PardonIPCommand(),
            new TBanCommand(),
            new TBanIPCommand(),
            new TBlockCommand(),
            new TBlockIPCommand(),
            new TMuteCommand(),
            new TMuteIPCommand(),
            new UnbanCommand(),
            new UnbanIPCommand(),
            new UnblockCommand(),
            new UnblockIPCommand(),
            new UnmuteCommand(),
            new UnmuteIPCommand(),
            new DeathBanCommand()
        ));
    }
    
    /**
     * @param Permission[] $permissions
     */
    protected function addPermissions(array $permissions) {
        foreach ($permissions as $permission) {
            $this->getServer()->getPluginManager()->addPermission($permission);
        }
    }
    /**
     * 
     * @param Plugin $plugin
     * @param Listener[] $listeners
     */
    protected function registerListeners(Core $plugin, array $listeners) {
        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $plugin);
        }
    }
    
    private function initializeListeners() {
        $this->registerListeners($this, array(
            new PlayerChatListener(),
            new PlayerCommandPreproccessListener(),
            new MuteEvent(),
            new PlayerPreLoginListener()
        ));
    }
    private function initializeFiles() {
        @mkdir($this->getDataFolder());
        if (!(file_exists("muted-players.txt") && is_file("muted-players.txt"))) {
            @fopen("muted-players.txt", "w+");
        }
        if (!(file_exists("muted-ips.txt") && is_file("muted-ips.txt"))) {
            @fopen("muted-ips.txt", "w+");
        }
        if (!(file_exists("blocked-players.txt") && is_file("blocked-players.txt"))) {
            @fopen("blocked-players.txt", "w+");
        }
        if (!(file_exists("blocked-ips.txt") && is_file("blocked-ips.txt"))) {
            @fopen("blocked-ips.txt", "w+");
        }
    }
    
    private function initializePermissions() {
        $this->addPermissions(array(
            new Permission("bs.cmd.ban", "Allows the player to prevent the given player to use this server.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.banip", "Allows the player to prevent the given IP address to use this server.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.banlist", "Allows the player to view the players/IP addresses banned on this server.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.blocklist", "Allows the player to view all the players/IP addresses banned from this server."),
            new Permission("bs.cmd.kick", "Allows the player to remove the given player.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.mute", "Allows the player to prevent the given player from sending public chat message.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.muteip", "Allows the player to prevent the given IP address from sending public chat message.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.mutelist", "Allows the player to view all the players muted from this server.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.pardon", "Allows the player to allow the given player to use this server.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.pardonip", "Allows the player to allow the given IP address to use this server.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.tban", "Allows the player to temporarily prevent the given player to use this server.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.tbanip", "Allows the player to temporarily prevent the given IP address to use this server.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.tmute", "Allows the player to temporarily prevents the given player to send public chat message.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.tmuteip", "Allows the player to prevents the given IP address to send public chat message.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.unban", "Allows the player to allow the given player to use this server.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.unbanip", "Allows the player to allow the given IP address to use this server.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.unmute", "Allows the player to allow the given player to send public chat message.", Permission::DEFAULT_OP),
            new Permission("bs.cmd.unmuteip", "Allows the player to allow the given IP address to send public chat message.", Permission::DEFAULT_OP),
            new Permission("deathban.command", "Allows the player to temporarily prevent the given player to use this server.")
          
        ));
    }
    
    private function removeBanExpired() {
        $this->getServer()->getNameBans()->removeExpired();
        $this->getServer()->getIPBans()->removeExpired();
        Manager::getNameMutes()->removeExpired();
        Manager::getIPMutes()->removeExpired();
        Manager::getNameBlocks()->removeExpired();
        Manager::getIPBlocks()->removeExpired();
    }
	/**
	 * @return ScoreHud|null
	 */
	public static function getInstance(): ?Core{
		return self::$instance;
	}

	public function onLoad(){
		self::$instance = $this;
		self::$addonPath = realpath($this->getDataFolder() . "addons") . DIRECTORY_SEPARATOR;

		Utils::checkVirions();

		$this->checkConfigs();
		$this->initScoreboards();
	}

	/**
	 * Check if the configs is up-to-date.
	 */
	private function checkConfigs(): void{
		$this->saveDefaultConfig();

		$this->saveResource("addons" . DIRECTORY_SEPARATOR . "README.txt");
		$this->saveResource("scorehud.yml");
		$this->scoreHudConfig = new Config($this->getDataFolder() . "scorehud.yml", Config::YAML);

		ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION);
		ConfigUpdater::checkUpdate($this, $this->scoreHudConfig, "scorehud-version", self::SCOREHUD_VERSION);
	}
	
	private function initScoreboards(): void{
		foreach($this->scoreHudConfig->getNested("scoreboards") as $world => $data){
			$world = strtolower($world);

			$this->scoreboards[$world] = $data;
			$this->scorelines[$world] = $data["lines"];
		}
	}
    
	public function onEnable(){
    if(!InvMenuHandler::isRegistered()){
	    InvMenuHandler::register($this);
   }
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->addonUpdater = new AddonUpdater($this);
		$this->addonManager = new AddonManager($this);

		$this->getServer()->getCommandMap()->register("scorehud", new ScoreHudCommand($this));
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		$this->getScheduler()->scheduleRepeatingTask(new ScoreUpdateTask($this), (int) $this->getConfig()->get("update-interval") * 20);
        @mkdir($this->getDataFolder());
        $this->saveResource("tags.yml");
        $this->myConfig = new Config($this->getDataFolder() . "tags.yml", Config::YAML);
        @mkdir($this->getDataFolder());
        $this->msg = new Config($this->getDataFolder()."koth.yml",Config::YAML,[
            "capture_time" => 100,
            "game_time" => 10,
            "reset_capture_progress" => true,
            "prefix" => "[KOTH] ",
            "starting" => "Game starting in {sec}. Join Game now! (/koth join)",
            "begin" => "KOTH Started! (/koth join)",
            "joined" => "Joined game successfully!, Be the first to capture the area now!",
            "win" => "{faction} | {player} has captured the area and won the event!",
            "end" => "Event has ended!",
            "not_running" => "There is no KOTH event running at the moment!",
            "still_running_title" => "KOTH Running!",
            "still_running_sub" => "Join now with /koth join !",
            "progress" => "Capturing... {percent}%",
            "end_game" => "Game Ended!",
            "game_bar" => "KOTH Time Left: {time}",
            "rewards" => [
                "givemoney {player} 1000",
                "give {player} diamond 2"
        ]
        ]);

        $this->c = new Config($this->getDataFolder()."arena.yml", Config::YAML);

        $all = $this->c->getAll();
        if (isset($all["spawns"]) && $all["p1"] && $all["p2"]){
            $this->arena = new KothArena($this,$all["spawns"],["p1" => $all["p1"], "p2" => $all["p2"]]);
            $this->getLogger()->info("KOTH Arena Loaded Successfully");
        }else{
            $this->getLogger()->alert("No arena setup! Please set one up!");
        }


        //Register Listener
        $this->getServer()->getPluginManager()->registerEvents(new KothListener($this),$this);

        //Register Command
        $this->getServer()->getCommandMap()->register("koth", new KothCommand("koth",$this));
        //Register Combat logger
		$this->loadConfigs();
		$this->setMessageManager();
		$this->setListener();
		$this->startHeartbeat();
		//Register Vanish2
        self::$main = $this;
        $this->getServer()->getPluginManager()->registerEvents(new VanishEventListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new VanishV2Task(), 20);
        //Register TradeGUI
		$this->getServer()->getCommandMap()->register("trade",new Trade());
	   //Register AuctionHouseGUI
        $this->getScheduler()->scheduleRepeatingTask(new CooldownTask($this, 25), 25);
        $this->config = new Config($this->getDataFolder(). "auctionconfig.yml", Config::YAML, array(
            "cooldown-seconds" => 15,
            "has-cooldown-message" => "§c[§7CoolDown§c] {TIME} secondes !"
        ));
        $this->cooldown = new Config($this->getDataFolder(). "cooldowns.yml", Config::YAML);
        @mkdir($this->getDataFolder());
		if (!file_exists($this->getDataFolder() . "AuctionLog.txt")) {
			fopen($this->getDataFolder() . "AuctionLog.txt", "w");
		}
		$oldFile = file_get_contents($this->getDataFolder() . "AuctionLog.txt", FILE_USE_INCLUDE_PATH);
		$newFile = $oldFile . "\n\nAUCTION HOUSE LOG";
		file_put_contents($this->getDataFolder() . "AuctionLog.txt", $newFile);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->auction = new Config($this->getDataFolder() . "Auction.yml", Config::YAML);
		$this->refreshAuction();
		$this->settings = (new Config($this->getDataFolder() . "Settings.yml", Config::YAML, array(
			"Limit" => 5,
			"MinimumPrice" => 5,
			"MaximumPrice" => 10000000,
			"ExpirationInMinutes" => 5,
			"ExpirationInHours" => 0,
			"ExpirationInDays" => 1
		)))->getAll();
		$this->auctionSettings = (new Config($this->getDataFolder() . "AuctionSettings.yml", Config::YAML, array(
			"AuctionName" => "          §l§bAuction §eHouse",
			"ItemDisplay" => "§aItem: §b{itemName} \n\n§r§aSeller: §b{seller}\n§r§aPrice: §b{price}§r\n\n§r§aExpiration: §b{expiration}",
			"MyAuction" => "§l§aYour Auction\n§rYou currently have §b{myauction}§c/{auctionlimit} §aauctions",
			"Bin" => "§l§aCollection Bin\n§rClick here to view and collect all of your\nitems that expired from auction",
			"LeftArrow" => "§l§e<< Previous Page",
			"Refresh" => "§l§dREFRESH\n§rClick here to refresh the list",
			"RightArrow" => "§l§eNext Page >>",
			"HowToSell" => "§l§eHow to sell\n§rHold an item and type §a/ah sell <price>",
			"Guide" => "§l§aGuide\n§rAuctionHouse is a place where\nyou can trade your items to earn money",
			"AuctionBinName" => "          §l§bExpired §eItems",
			"BackToAuction" => "§l§aBack to Auction",
			"ClaimAll" => "§l§bClaim All"
		)))->getAll();
		$this->message = (new Config($this->getDataFolder() . "Message.yml", Config::YAML, array(
			"Prefix" => "§l§a[§bAH§a] §r",
			"HoldItem" => "§cPlease hold item in your hand§r",
			"SurvivalOnly" => "§cPlease switch to survival",
			"DontMove" => "§cPlease don't move while opening Auction",
			"AddAuctionSuccess" => "§fYou successfully add §b{itemName} §fx§b{itemCount} §ffor §b{price} §fon §aAuction§r",
			"ReachAuctionLimit" => "§cYou've reached auction limit§r",
			"InvalidPriceRange" => "§cOnly §b{minimumPrice} §cto §b{maximumPrice} §cprice are allowed§r",
			"InvalidPriceValue" => "§cPlease put a valid number§r",
			"InvalidItem" => "§cItem is not allowed",
			"ReceivedMoney" => "§fYou received §b{money} §ffrom §aauction§r",
			"PurchaseSuccess" => "§fYou purchased §b{itemName} §fx§b{itemCount} §ffor §b{price}§r",
			"NotEnoughMoney" => "§cYou do not have enough money§r",
			"FailedToOpen" => "§cFailed to open. Please try again later",
			"MoveUp" => "§cFailed to open. Please move 5 blocks up",
			"NoDupe" => "§cDupe glitch is not allowed!",
			"NotAvailable" => "§cItem is not available. Try to refresh§r"
		)))->getAll();
		$this->blockItems = (new Config($this->getDataFolder() . "BlockItems.yml", Config::YAML, array(
			"ItemID" => [
				1,
				0,
				0
			],
		)))->getAll();
		$this->db = new \SQLite3($this->getDataFolder() . "Auction.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS limits(player TEXT PRIMARY KEY, total INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS pending(player TEXT PRIMARY KEY, money INT);");
		//Bansystem
        $this->initializeCommands();
        $this->initializeListeners();
        $this->initializePermissions();
        $this->initializeFiles();
        $this->removeBanExpired();
	}

    public static function getMain(): self{
        return self::$main;
    }

	public function loadConfigs() {
		$this->saveResource(self::SETTINGS_FILE);
		$this->settings = new Config($this->getDataFolder() . self::SETTINGS_FILE, Config::YAML);
	}

	/**
	 * @return Config
	 */
	public function getScoreHudConfig(): Config{
		return $this->scoreHudConfig;
	}

	/**
	 * @return array|null
	 */
	public function getScoreboards(): ?array{
		return $this->scoreboards;
	}

	/**
	 * @param string $world
	 * @return array|null
	 */
	public function getScoreboardData(string $world): ?array{
		return !isset($this->scoreboards[$world]) ? null : $this->scoreboards[$world];
	}

	/**
	 * @return array|null
	 */
	public function getScoreWorlds(): ?array{
		return is_null($this->scoreboards) ? null : array_keys($this->scoreboards);
	}

	/**
	 * @param Player $player
	 * @param string $title
	 */
	public function addScore(Player $player, string $title): void{
		if(!$player->isOnline()){
			return;
		}

		if(isset($this->disabledScoreHudPlayers[strtolower($player->getName())])){
			return;
		}

		ScoreFactory::setScore($player, $title);
		$this->updateScore($player);
	}

	/**
	 * @param Player $player
	 */
	public function updateScore(Player $player): void{
		if($this->getConfig()->get("per-world-scoreboards")){
			if(!$player->isOnline()){
				return;
			}

			$levelName = strtolower($player->getLevel()->getFolderName());

			if(!is_null($lines = $this->getScorelines($levelName))){
				if(empty($lines)){
					$this->getLogger()->error("Please set lines key for $levelName correctly for scoreboards in scorehud.yml.");
					$this->getServer()->getPluginManager()->disablePlugin($this);

					return;
				}

				$i = 0;

				foreach($lines as $line){
					$i++;

					if($i <= 15){
						ScoreFactory::setScoreLine($player, $i, $this->process($player, $line));
					}
				}
			}elseif($this->getConfig()->get("use-default-score-lines")){
				$this->displayDefaultScoreboard($player);
			}else{
				ScoreFactory::removeScore($player);
			}
		}else{
			$this->displayDefaultScoreboard($player);
		}
	}

	/**
	 * @param string $world
	 * @return array|null
	 */
	public function getScorelines(string $world): ?array{
		return !isset($this->scorelines[$world]) ? null : $this->scorelines[$world];
	}

	/**
	 * @param Player $player
	 * @param string $string
	 * @return string
	 */
	public function process(Player $player, string $string): string{
		$tags = [];

		foreach($this->addonManager->getAddons() as $addon){
			foreach($addon->getProcessedTags($player) as $identifier => $processedTag){
				$tags[$identifier] = $processedTag;
			}
		}

		$formattedString = str_replace(
			array_keys($tags),
			array_values($tags),
			$string
		);

		return $formattedString;
	}

	/**
	 * @param Player $player
	 */
	public function displayDefaultScoreboard(Player $player): void{
		$dataConfig = $this->scoreHudConfig;

		$lines = $dataConfig->get("score-lines");

		if(empty($lines)){
			$this->getLogger()->error("Please set score-lines in scorehud.yml properly.");
			$this->getServer()->getPluginManager()->disablePlugin($this);

			return;
		}

		$i = 0;

		foreach($lines as $line){
			$i++;

			if($i <= 15){
				ScoreFactory::setScoreLine($player, $i, $this->process($player, $line));
			}
		}
	}

	/**
	 * @return AddonUpdater
	 */
	public function getAddonUpdater(): AddonUpdater{
		return $this->addonUpdater;
	}

	/**
	 * @return AddonManager
	 */
	public function getAddonManager(): AddonManager{
		return $this->addonManager;
	}

    public function getFaction(Player $player){
        return $this->fac == null ? "" : $this->fac->getPlayerFaction($player->getName());
    }

    public function onDisable(){
    	$this->getLogger()->info('Disabled');
        $arena = $this->arena;
        if ($arena instanceof KothArena){
            $arena->resetGame();
        }
		$this->taggedPlayers = [];
		
        $this->config->save();
        $this->cooldown->save();
    }
    
    public function convertSeconds(int $seconds) : string {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;   
        return "$hours:$minutes:$seconds";
    }
   public function formatMessage(string $message, $player) : string {
        $message = str_replace("{TIME}", $this->getCooldownTime($player), $message);
        $message = str_replace("{NAME}", $player->getName(), $message);
        return $message;
    }
    
public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch ($command->getName()){
			case "nick":
				if ($sender instanceof Player){
					if ($sender->hasPermission("yolo.cmd.nick")){
						$this->Nick($sender);
						$sender->sendMessage("§7Opening...");
			} else {
				$sender->sendMessage("§8[§cYolo§8]§b Do Not have permission for this command");
			}
		}
			break;
			case "unnick":
			$name = $sender->getName();
				if ($sender instanceof Player){
					if ($sender->hasPermission("yolo.cmd.nick")){
						$sender->setNameTag($name);
						$sender->setDisplayName($name);
			} else {
				$sender->sendMessage("§8[§cYolo§8]§b Do Not have permission for this command");
			}
		}
			break;
			case "fly":
                    if($sender->hasPermission("yolo.cmd.fly")){
                        if($sender->getGamemode() === 1){
                            $sender->sendMessage(TextFormat::RED."§8(§l§eYolo§r§8)§7 You can't use this command in creative mode!");
                            break;
                        }else{
                            if(!in_array($sender->getName(), $this->fly)){
                                $sender->setAllowFlight(true);
                                $sender->setFlying(true);
                                $this->fly[] = $sender->getName();
                                $sender->sendMessage(TextFormat::GREEN."§8(§l§eYolo§r§8)§7 Enabled flying.");
                                break;
                            }elseif(in_array($sender->getName(), $this->fly)){
                                $sender->setAllowFlight(false);
                                $sender->setFlying(false);
                                unset($this->fly[array_search($sender->getName(), $this->fly)]);
                                $sender->sendMessage(TextFormat::GREEN."§8(§l§eYolo§r§8)§7 Disabled flying.");
                                break;
                            }
                        }
                    }else{
                        $sender->sendMessage(TextFormat::RED."§8(§l§eYolo§r§8)§7 Do Not have permission to use this command!");
                        break;
                    }
			break;
			case "god":
                    if($sender->hasPermission("yolo.cmd.god")){
                        if(!in_array($sender->getName(), $this->god)){
                            $this->god[] = $sender->getName();
                            $sender->sendMessage(TextFormat::GREEN."§8(§l§eYolo§r§8)§7 Enabled god mode.");
                            break;
                        }elseif(in_array($sender->getName(), $this->god)){
                            unset($this->god[array_search($sender->getName(), $this->god)]);
                            $sender->sendMessage(TextFormat::GREEN."§8(§l§eYolo§r§8)§7 Disabled god mode.");
                            break;
                        }
                    }else{
                        $sender->sendMessage(TextFormat::RED."§8(§l§eYolo§r§8)§7 Do Not have permission to use this command!");
                        break;
                    }
			break;
			case "day":
				if ($sender->hasPermission("yolo.cmd.day")){
				if ($sender instanceof Player){
					$level = $sender->getLevel();
					$level->setTime(0);
					$sender->sendMessage("§8(§l§eYolo§r§8)§7 Time Set to Day!");
					return true;
				}
				$level = $this->getServer()->getDefaultLevel();
				$level->setTime(0);
				$sender->sendMessage("§8(§l§eYolo§r§8)§7 Time Set to Day!");
				return true;
				}
				$sender->sendMessage("§8(§l§eYolo§r§8)§7 Do Not have permission for this command");
			break;
			case "night":
				if ($sender->hasPermission("yolo.cmd.night")){
				if ($sender instanceof Player){
					$level = $sender->getLevel();
					$level->setTime(14000);
					$sender->sendMessage("§8(§l§eYolo§r§8)§7 Time Set to Night!");
					return true;
				}
				$level = $this->getServer()->getDefaultLevel();
				$level->setTime(14000);
				$sender->sendMessage("§8(§l§eYolo§r§8)§7 Time Set to Night!");
				return true;
				}
				$sender->sendMessage("§8(§l§eYolo§r§8)§7 Do Not have permission for this command");
			break;
			case "vanish":
			$name = $sender->getName();
	        if(!$sender instanceof Player){
                $sender->sendMessage("§8(§l§eYolo§r§8) §7Use this command InGame");
                return false;
            }

	        if(!$sender->hasPermission("yolo.cmd.vanish")){
		        $sender->sendMessage("§8(§l§eYolo§r§8) §7You do not have permission to use this command");
                return false;
            }

            if(!in_array($name, self::$vanish)){
                self::$vanish[] = $name;
		        $sender->sendMessage("§8(§l§eYolo§r§8) §7You are now vanished.");
		        $nameTag = $sender->getNameTag();
		        self::$nametagg[$name] = $nameTag;
		        $sender->setNameTag(" $nameTag");
            }else{
                unset(self::$vanish[array_search($name, self::$vanish)]);
                foreach($this->getServer()->getOnlinePlayers() as $players){
                    $players->showPlayer($sender);
                    $nameTag = self::$nametagg[$name];
                    $sender->setNameTag("$nameTag");
                }
             $pk = new PlayerListPacket();
             $pk->type = PlayerListPacket::TYPE_ADD;
                 $pk->entries[] = PlayerListEntry::createAdditionEntry($sender->getUniqueId(), $sender->getId(), $sender->getDisplayName(), SkinAdapterSingleton::get()->toSkinData($sender->getSkin()), $sender->getXuid());
             foreach($this->getServer()->getOnlinePlayers() as $p)
             $p->sendDataPacket($pk);
                $sender->sendMessage("§8(§l§eYolo§r§8) §7You are no longer vanished!");
            }
			break;
			case "heal":
				if (isset($args[0])){
					if ($sender->hasPermission("yolo.cmd.heal.other")){
						$oplayer = $this->getServer()->getPlayer($args[0]);
						if ($oplayer instanceof Player){
							$oplayer->setHealth(20);
							$oplayer->sendMessage("§8(§l§eYolo§r§8)§7 You have been healed!");
							$sender->sendMessage("§8(§l§eYolo§r§8)§7 You have been successfully healed ".$oplayer->getName());
							return true;
						}
						$sender->sendMessage("§8(§l§eYolo§r§8)§7 Player Does not exsist USGAE: /heal [name]"); return true;
					}$sender->sendMessage("§8(§l§eYolo§r§8)§7 Do Not have permission to heal other players"); return true;}
				if ($sender instanceof Player){
					if ($sender->hasPermission("yolo.cmd.heal")){
					$sender->setHealth(20);
					$sender->sendMessage("§8(§l§eYolo§r§8)§7 You have been healed!");
					return true;
				}$sender->sendMessage("§8(§l§eYolo§r§8)§7 Do Not have permission for this command"); return true;}

			break;
			case "feed":
				if ($sender instanceof Player){
					if (isset($args[0])){
						if ($sender->hasPermission("yolo.cmd.feed.other")){
							$oplayer = $this->getServer()->getPlayer($args[0]);
							if ($oplayer instanceof Player){
								$oplayer->setFood(20);
								$oplayer->sendMessage("§8(§l§eYolo§r§8)§7 You have been fed!");
								$sender->sendMessage("§8(§l§eYolo§r§8)§7 You have been successfully fed ".$oplayer->getName());
								return true;
							}
							$sender->sendMessage("§8(§l§eYolo§r§8)§7 Player Does not exsist USGAE: /feed [name]"); return true;
						}$sender->sendMessage("§8(§l§eYolo§r§8)§7 Do Not have permission to feed other players"); return true;}
					if ($sender->hasPermission("yolo.cmd.feed")){
						$sender->setFood(20);
						$sender->sendMessage("§8(§l§eYolo§r§8)§7 You have been fed!");
						return true;
					}$sender->sendMessage("§8(§l§eYolo§r§8)§7 Do Not have permission for this command"); return true;}

			break;
			case "gmc":
				if ($sender instanceof Player){
					if ($sender->hasPermission("yolo.cmd.gmc")){
					$sender->setGamemode(1);
					$sender->sendMessage("§8(§l§eYolo§r§8)§7 Your gamemode has been changed to Creative Mode!");
						return true;
				}}
			break;
			case "gms":
				if ($sender instanceof Player){
					if ($sender->hasPermission("yolo.cmd.gms")){
					$sender->setGamemode(0);
					$sender->sendMessage("§8(§l§eYolo§r§8)§7 Your gamemode has been changed to Survival Mode");
						return true;
				}}
			break;
			case "gma":
				if ($sender instanceof Player){
					if ($sender->hasPermission("yolo.cmd.gma")){
					$sender->setGamemode(2);
					$sender->sendMessage("§8(§l§eYolo§r§8)§7 Your gamemode has been changed to Adventure Mode");
						return true;
				}}
			break;
			case "gmspc":
				if ($sender instanceof Player){
					if ($sender->hasPermission("yolo.cmd.gmspc")){
					$sender->setGamemode(3);
					$sender->sendMessage("§8(§l§eYolo§r§8)§7 Your gamemode has been changed to Spectator Mode");
						return true;
				}}
			break;
			case "ci":
				if ($sender instanceof Player){
					if ($sender->hasPermission("yolo.cmd.ci")){
					$sender->getInventory()->clearAll();
					$sender->sendMessage("§8(§l§eYolo§r§8)§7 You cleared your inventory, idiot.. "); return true;
					}
					$sender->sendMessage("§8(§l§eYolo§r§8)§7 Do Not have permission for this command, That means dont try again..."); return true;
				}
			break;
			case "effects":
   if($sender instanceof Player) {
		if($sender->hasPermission("yolo.cmd.effects")){
			$this->Effects($sender);
			} else {
				$sender->sendMessage("§8[§cYolo§8]§b Do Not have permission for this command");
			}
		}
	        break;
			case "broadcast":
				if ($sender->hasPermission("yolo.cmd.broadcast")){
				 	if (isset($args[0])){
				 		$msg = implode(" ", $args);
				 		$this->broadcastMsg("§8(§l§eYolo§r§8) §7". $msg);
				 	}else $sender->sendMessage("§8(§l§eYolo§r§8)§7 USAGE: /broadcast [msg]");
						return true;
				}else $sender->sendMessage("§8[§cYolo§8]§b Do Not have permission for this command");
			break;
              case "tags":
				if ($sender->hasPermission("yolo.cmd.tags")){
					$this->Tags($sender);
					} else {
             	$sender->sendMessage("§8(§l§eYolo§r§8) §7You Do Not Have Permission To Use This Command!");
					}
              break;
              case "bansystem":
				if ($sender->hasPermission("yolo.cmd.bansystem")){
					$this->BanSystem($sender);
					} else {
             	$sender->sendMessage("§8(§l§eYolo§r§8) §7You Do Not Have Permission To Use This Command!");
					}
              break;
              case "ranksystem":
				if ($sender->hasPermission("yolo.cmd.ranksystem")){
					$this->RankSystem($sender);
					} else {
             	$sender->sendMessage("§8(§l§eYolo§r§8) §7You Do Not Have Permission To Use This Command!");
					}
              break;
              case "spawner":
              $sender->sendMessage("§8(§l§eYolo§r§8) §7Opening..");
					$this->Spawner($sender);
              break;
			  case "ah":

if($this->hasCooldown($sender)){
$sender->sendPopup($this->formatMessage($this->config->get("has-cooldown-message"), $sender));

}else if (!$this->hasCooldown($sender)){
				if($sender instanceof Player){
					if(count($args) === 0) {
						if($sender->getGamemode() == 0){
							if($sender->y <= 5){
								$sender->sendMessage($this->message["Prefix"] . $this->message["MoveUp"]);
								return true;
							}
							$this->writeLog($sender->getName() . " open Auction House");
			             $this->addCooldown($sender);
		$this->openAuctionHouse($sender);
						} else {
							$sender->sendMessage($this->message["Prefix"] . $this->message["SurvivalOnly"]);
						}
					}
					if(count($args) === 2) {
						if($args[0] == "sell"){
							if($sender->getGamemode() == 0){
								$item = $sender->getInventory()->getItemInHand();
								if($item->getId() === 0){
									$sender->sendMessage($this->message["Prefix"] . $this->message["HoldItem"]);
								} else {
									foreach($this->blockItems["ItemID"] as $itemID){
										if($item->getId() == $itemID){
											$sender->sendMessage($this->message["Prefix"] . $this->message["InvalidItem"]);
											return true;
										}
									}
									if(is_numeric($args[1])) {
										if($args[1] >= $this->settings["MinimumPrice"] && $args[1] <= $this->settings["MaximumPrice"]) {
											$name = $sender->getName();
											$result = $this->db->query("SELECT * FROM limits WHERE player = '$name';");
											$array = $result->fetchArray(SQLITE3_ASSOC);
											if($array['total'] < $this->settings["Limit"]) {
												$this->db->query("UPDATE limits SET total = total + 1 WHERE player = '$name'");
												$sender->getInventory()->setItemInHand(Item::get(0, 0, 0));
												$key = rand();
												if(isset($this->auctionItems[$key])){
													while(isset($this->auctionItems[$key])){
														$key = rand();
													}
												}
												if($item->hasCustomName() == 1){
													$customName = $item->getCustomName();
												} else {
													$customName = $item->getName();
												}
												$itemLore = [];
												foreach($item->getLore() as $lore){
													$itemLore[] = $lore;
												}
												if($itemLore == null){
													$itemLore[] = 99999;
												}
												$encId = [];
												if($item->hasEnchantments() == 1){
													foreach($item->getEnchantments() as $info){
														$enchants[] = $info->getId() . ":" . $info->getLevel();
													}
												} else {
													$enchants[] = "99999:99999";
												}
												$time = time();
												$day = ($this->settings["ExpirationInDays"] * 86400);
												$hour = ($this->settings["ExpirationInHours"] * 3600);
												$mins = ($this->settings["ExpirationInMinutes"] * 60);
												$expiration = $time + $day + $hour + $mins;
												$this->auction->set(
													$key, array(
														$sender->getName(),
														$item->getId(),
														$item->getDamage(),
														$item->getCount(),
														$customName,
														$itemLore,
														$enchants,
														$args[1],
														$expiration,
														$key
													)
												);
												$this->auction->save();
												$this->writeLog($sender->getName() . " sell an item in Auction House");
												$sender->sendMessage($this->message["Prefix"] . str_replace(["{itemName}", "{itemCount}", "{price}"], [$customName, $item->getCount(), $args[1]], $this->message["AddAuctionSuccess"]));
											} else {
												$sender->sendMessage($this->message["Prefix"] . $this->message["ReachAuctionLimit"]);
											}
										} else {
											$sender->sendMessage($this->message["Prefix"] . str_replace(["{minimumPrice}", "{maximumPrice}"], [$this->settings["MinimumPrice"], $this->settings["MaximumPrice"]], $this->message["InvalidPriceRange"]));
										}
									} else {
										$sender->sendMessage($this->message["Prefix"] . $this->message["InvalidPriceValue"]);
									}
								}
							} else {
								$sender->sendMessage($this->message["Prefix"] . $this->message["SurvivalOnly"]);
							}
						}
					}
				}
			}
			  break;
              case "item":
$item = $sender->getInventory()->getItemInHand();
$pname = $sender->getName();
$count = $item->getCount();
$name = $item->getName();     
                if ($sender->hasPermission("yolo.cmd.item")){
                	$this->broadcastMsg("§l§8[§6BRAG§8]§r §6".$pname." §7is bragging §8[§c".$name." §f".$count."§7x§8]");
                }else{     
                     $sender->sendMessage(TextFormat::RED . "You dont have permission!");
                     return true;
                }    
              break;
		}
		return true;
	}

	/**
	 * Set the message manager
	 */
	public function setMessageManager() {
		$this->messageManager = new MessageManager($this->getSettingsProperty("messages", []));
	}

	/**
	 * Set the event listener
	 */
	public function setListener() {
		$this->listener = new CombatEventListener($this);
	}

	/**
	 * @return MessageManager
	 */
	public function getMessageManager() {
		return $this->messageManager;
	}

	/**
	 * @return EventListener
	 */
	public function getListener() {
		return $this->listener;
	}

	/**
	 * Start the heartbeat task
	 */
	public function startHeartbeat() {
		$this->getScheduler()->scheduleRepeatingTask(new TaggedHeartbeatTask($this), 20);
	}

	/**
	 * @param string $nested
	 * @param array $default
	 *
	 * @return mixed
	 */
	public function getSettingsProperty(string $nested, $default = []) {
		return $this->settings->getNested($nested, $default);
	}

	/**
	 * @param Player|string $player
	 * @param bool $value
	 * @param int $time
	 */
	public function setTagged($player, $value = true, int $time = 10) {
		if($player instanceof Player) $player = $player->getName();
		if($value) {
			$this->taggedPlayers[$player] = $time;
		} else {
			unset($this->taggedPlayers[$player]);
		}
	}

	/**
	 * @param Player|string $player
	 *
	 * @return bool
	 */
	public function isTagged($player) {
		if($player instanceof Player) $player = $player->getName();
		return isset($this->taggedPlayers[$player]);
	}

	/**
	 * @param Player|string $player
	 *
	 * @return int
	 */
	public function getTagDuration($player) {
		if($player instanceof Player) $player = $player->getName();
		return ($this->isTagged($player) ? $this->taggedPlayers[$player] : 0);
	}

    public function setPoint(Player $player, $type){
        $save = (int)$player->getX().":".(int)$player->getY().":".(int)$player->getZ().":".$player->getLevel()->getName();
        $all = $this->c->getAll();
        if ($type === "spawn"){
            $all["spawns"][] = $save;
        }else{
            $all[$type] = $save;
        }
        $this->c->setAll($all);
        $this->c->save();
    }

    public function startArena() : bool {
        $arena = $this->arena;
        if ($arena instanceof KothArena) {
            $arena->preStart();
            return true;
        }
        return false;
    }

    public function forceStop() : bool {
        $arena = $this->arena;
        if ($arena instanceof KothArena) {
            $arena->resetGame();
            return true;
        }
        return false;
    }

    public function isRunning() : bool {
        $arena = $this->arena;
        if ($arena instanceof KothArena) {
            if ($arena->isRunning()) return true;
        }
        return false;
    }

    public function sendToKoth(Player $player) : bool {
        $arena = $this->arena;
        if ($arena instanceof KothArena) {
            if ($arena->isRunning()){
                $arena->addPlayer($player);
                return true;
            }
        }
        return false;
    }

    public function prefix() : string {
        $all = $this->msg->getAll();
        return isset($all["prefix"]) ? $all["prefix"] : "[KOTH] ";
    }

    public function removePlayer(Player $player){
        $arena = $this->arena;
        if ($arena instanceof KothArena) {
            $arena->removePlayer($player);
        }
        return false;
    }

    public function getData($type) : string {
        $all = $this->msg->getAll();
        return isset($all[$type]) ? $all[$type] : "";
    }

	public function onJoin(PlayerJoinEvent $ev){
		$player = $ev->getPlayer();
		$name = $player->getName();
		$result = $this->db->query("SELECT * FROM pending WHERE player = '$name';");
		$array = $result->fetchArray(SQLITE3_ASSOC);	
		if (empty($array)) {
			$pending = $this->db->prepare("INSERT INTO pending(player, money) VALUES (:player, :money);");
			$pending->bindValue(":player", $name);
			$pending->bindValue(":money", 0);
			$pending->execute();
		} else {
			$money = $array['money'];
			if($money >= 1){
				EconomyAPI::getInstance()->addMoney($player, $money);
				$this->db->query("UPDATE pending SET money = 0 WHERE player = '$name'");
				$player->sendMessage($this->message["Prefix"] . str_replace(["{money}"], [$money], $this->message["ReceivedMoney"]));
			}
		}
		$result = $this->db->query("SELECT * FROM limits WHERE player = '$name';");
		$array = $result->fetchArray(SQLITE3_ASSOC);	
		if (empty($array)) {
			$limit = $this->db->prepare("INSERT INTO limits(player, total) VALUES (:player, :total);");
			$limit->bindValue(":player", $name);
			$limit->bindValue(":total", 0);
			$limit->execute();
		}
            $ev->setJoinMessage(Textformat::DARK_GRAY . "[" . TextFormat::GREEN . "+" . TextFormat::DARK_GRAY . "] " . TextFormat::GREEN . $name);
	}

	public function onCloseWindow(InventoryCloseEvent $event){
		$player = $event->getPlayer();
		$this->sendRealBlock($player);
	}
	
	public function openAuctionHouse($player){
		$this->writeLog($player->getName() . " opening Auction House");
		$b1 = $player->getLevel()->getBlockAt((int)$player->x, (int)$player->y - 4, (int)$player->z);
		$b2 = $player->getLevel()->getBlockAt((int)$player->x + 1, (int)$player->y - 4, (int)$player->z);
		if($b1->getId() == 54){
			$player->sendMessage($this->message["Prefix"] . $this->message["MoveUp"]);
			return true;
		}
		if($b2->getId() == 54){
			$player->sendMessage($this->message["Prefix"] . $this->message["MoveUp"]);
			return true;
		}
		$this->block1[$player->getName()] = $player->getLevel()->getBlockAt((int)$player->x, (int)$player->y - 4, (int)$player->z);
		$this->block2[$player->getName()] = $player->getLevel()->getBlockAt((int)$player->x + 1, (int)$player->y - 4, (int)$player->z);
		$nbt = new CompoundTag(" ", [
			new StringTag("id", Tile::CHEST),
			new StringTag("CustomName", $this->auctionSettings["AuctionName"]),
			new IntTag("x", (int)$player->x),
			new IntTag("y", (int)$player->y - 4),
			new IntTag("z", (int)$player->z)
		]);
		$leftChest = Tile::createTile("Chest", $player->getLevel(), $nbt);
		$nbt = new CompoundTag(" ", [
			new StringTag("id", Tile::CHEST),
			new StringTag("CustomName", $this->auctionSettings["AuctionName"]),
			new IntTag("x", (int)$player->x + 1),
			new IntTag("y", (int)$player->y - 4),
			new IntTag("z", (int)$player->z)
		]);
		$rightChest = Tile::createTile("Chest", $player->getLevel(), $nbt);
		$leftChest->pairWith($rightChest);
		$rightChest->pairWith($leftChest);
		$block = Block::get(Block::CHEST)->setComponents($leftChest->x, $leftChest->y, $leftChest->z);
		$block2 = Block::get(Block::CHEST)->setComponents($rightChest->x, $rightChest->y, $rightChest->z);
		$player->getLevel()->sendBlocks([$player], [$block, $block2]);
		$this->inventory[$player->getName()] = $leftChest->getInventory();
		$this->addAuctionItems($player, $this->inventory[$player->getName()]);
		$this->getScheduler()->scheduleDelayedTask(new Task\AHWindow($this, $player, $this->inventory[$player->getName()]), 15);
	}

	public function openBin($player){
		$this->writeLog($player->getName() . " opening Bin");
		$b1 = $player->getLevel()->getBlockAt((int)$player->x, (int)$player->y - 4, (int)$player->z);
		$b2 = $player->getLevel()->getBlockAt((int)$player->x + 1, (int)$player->y - 4, (int)$player->z);
		if($b1->getId() == 54){
			$player->sendMessage($this->message["Prefix"] . $this->message["MoveUp"]);
			return true;
		}
		if($b2->getId() == 54){
			$player->sendMessage($this->message["Prefix"] . $this->message["MoveUp"]);
			return true;
		}
		$this->block1[$player->getName()] = $player->getLevel()->getBlockAt((int)$player->x, (int)$player->y - 4, (int)$player->z);
		$this->block2[$player->getName()] = $player->getLevel()->getBlockAt((int)$player->x + 1, (int)$player->y - 4, (int)$player->z);
		$nbt = new CompoundTag(" ", [
			new StringTag("id", Tile::CHEST),
			new StringTag("CustomName", $this->auctionSettings["AuctionBinName"]),
			new IntTag("x", (int)$player->x),
			new IntTag("y", (int)$player->y - 4),
			new IntTag("z", (int)$player->z)
		]);
		$leftChest = Tile::createTile("Chest", $player->getLevel(), $nbt);
		$nbt = new CompoundTag(" ", [
			new StringTag("id", Tile::CHEST),
			new StringTag("CustomName", $this->auctionSettings["AuctionBinName"]),
			new IntTag("x", (int)$player->x + 1),
			new IntTag("y",  (int)$player->y - 4),
			new IntTag("z", (int)$player->z)
		]);
		$rightChest = Tile::createTile("Chest", $player->getLevel(), $nbt);
		$leftChest->pairWith($rightChest);
		$rightChest->pairWith($leftChest);
		$block = Block::get(Block::CHEST)->setComponents($leftChest->x, $leftChest->y, $leftChest->z);
		$block2 = Block::get(Block::CHEST)->setComponents($rightChest->x, $rightChest->y, $rightChest->z);
		$player->getLevel()->sendBlocks([$player], [$block, $block2]);
		$this->inventory[$player->getName()] = $leftChest->getInventory();
		$this->addBinItems($player, $this->inventory[$player->getName()]);
		$this->getScheduler()->scheduleDelayedTask(new Task\AHWindow($this, $player, $this->inventory[$player->getName()]), 15);
	}

	public function addBinItems($player, $inventory, int $page = 0){
		$this->writeLog($player->getName() . " adding Bin Items");
		$this->refreshAuction();
		if($inventory->getDefaultSize() == 54){
			$inventory->clearAll();
			if(!empty($this->auctionItems)) {
				$auction = yaml_parse_file($this->getDataFolder() . "Auction.yml");
				$ahBinItems = [];
				foreach($auction as $key => $val) $ahBinItems[$key] = $val;
				foreach($ahBinItems as $data){
					$timeNow = time();
					if($data[8] < $timeNow){
						if($data[0] != $player->getName()){
							unset($ahBinItems[$data[9]]);
						}
					}
				}
				if(!empty($ahBinItems)) {
					$chunked = array_chunk($ahBinItems, 44, true);
					if($page < 0){
						$page = count($chunked) - 1;
					}
					$page = isset($chunked[$page]) ? $page : 0;
					foreach($chunked[$page] as $data){
						$timeNow = time();
						if($data[8] < $timeNow){
							if($data[0] == $player->getName()){
								$item = Item::get($data[1], $data[2], $data[3]);
								$item->setCustomName($data[4]);
								$item->setNamedTagEntry(new StringTag("AHUBinMenus", "contents")); // MENUS
								foreach($data[5] as $lore){
									if($lore != 99999){
										$item->setLore($data[5]);
									}
								}
								foreach($data[6] as $enchant) {
									$enchant = explode(':', $enchant);
									$encId = $enchant[0];
									$encLvl = $enchant[1];
									if($encId != 99999 && $encLvl != 99999){
										$enchantment = Enchantment::getEnchantment($encId);
										if($enchantment != null){
											$enchInstance = new EnchantmentInstance($enchantment, $encLvl);
											$item->addEnchantment($enchInstance);
										}
									}
								}
								$inventory->addItem($item);
							}
						}
					}
				}
			}
			$item = Item::get(264, 0, 1);
			$item->setCustomName($this->auctionSettings["BackToAuction"]);
			$item->setNamedTagEntry(new StringTag("AHUBinMenus", "backtoauction"));
			$inventory->setItem(45, $item);
			$item = Item::get(339, 0, 1);
			$item->setCustomName($this->auctionSettings["LeftArrow"]);
			$item->setNamedTagEntry(new IntArrayTag('binturner', [0, $page]));
			$inventory->setItem(48, $item);
			$item = Item::get(54, 0, 1);
			$item->setCustomName($this->auctionSettings["ClaimAll"]);
			$item->setNamedTagEntry(new StringTag("AHUBinMenus", "claimall"));
			$inventory->setItem(49, $item);
			$item = Item::get(339, 0, 1);
			$item->setCustomName($this->auctionSettings["RightArrow"]);
			$item->setNamedTagEntry(new IntArrayTag('binturner', [1, $page]));
			$inventory->setItem(50, $item);
			$item = Item::get(340, 0, 1);
			$item->setCustomName($this->auctionSettings["Guide"]);
			$item->setNamedTagEntry(new StringTag("AHUBinMenus", "guide"));
			$inventory->setItem(53, $item);
		} else {
			$player->sendMessage($this->message["Prefix"] . $this->message["FailedToOpen"]);
		}
	}
	
	public function getBinItems($player, $inventory, int $page = 0){
		$this->refreshAuction();
		if(!empty($this->auctionItems)) {
			$auction = yaml_parse_file($this->getDataFolder() . "Auction.yml");
			$ahBinItems = [];
			foreach($auction as $key => $val) $ahBinItems[$key] = $val;
			foreach($ahBinItems as $data){
				$timeNow = time();
				if($data[8] < $timeNow){
					if($data[0] != $player->getName()){
						unset($ahBinItems[$data[9]]);
					}
				}
			}
			if(!empty($ahBinItems)) {
				$chunked = array_chunk($ahBinItems, 44, true);
				if($page < 0){
					$page = count($chunked) - 1;
				}
				$page = isset($chunked[$page]) ? $page : 0;
				foreach($chunked[$page] as $data){
					$timeNow = time();
					if($data[8] < $timeNow){
						if($data[0] == $player->getName()){
							$item = Item::get($data[1], $data[2], $data[3]);
							$item->setCustomName($data[4]);
							$item->setNamedTagEntry(new StringTag("AHUBinMenus", "contents"));
							foreach($data[5] as $lore){
								if($lore != 99999){
									$item->setLore($data[5]);
								}
							}
							foreach($data[6] as $enchant) {
								$enchant = explode(':', $enchant);
								$encId = $enchant[0];
								$encLvl = $enchant[1];
								if($encId != 99999 && $encLvl != 99999){
									$enchantment = Enchantment::getEnchantment($encId);
									if($enchantment != null){
										$enchInstance = new EnchantmentInstance($enchantment, $encLvl);
										$item->addEnchantment($enchInstance);
									}
								}
							}
							$player->getInventory()->addItem($item);
							$name = $player->getName();
							$this->db->query("UPDATE limits SET total = total - 1 WHERE player = '$name'");
							$this->auction->remove($data[9]);
							$this->auction->save();
							$this->addBinItems($player, $inventory);
						}
					}
				}
			}
		}
	}

	public function sendRealBlock($player){
		if(isset($this->block1[$player->getName()]) && isset($this->block2[$player->getName()]) && isset($this->inventory[$player->getName()])){
			$this->inventory[$player->getName()]->clearAll();
			$player->getLevel()->sendBlocks([$player], [$this->block1[$player->getName()], $this->block2[$player->getName()]]);
			if($this->block1[$player->getName()]->getId() == 54){
				$player->getLevel()->setBlock(new Vector3($this->block1[$player->getName()]->x, $this->block1[$player->getName()]->y, $this->block1[$player->getName()]->z), new Block(0, 0), true);
			}
			if($this->block2[$player->getName()]->getId() == 54){
				$player->getLevel()->setBlock(new Vector3($this->block2[$player->getName()]->x, $this->block2[$player->getName()]->y, $this->block2[$player->getName()]->z), new Block(0, 0), true);
			}
			unset($this->block1[$player->getName()]);
			unset($this->block2[$player->getName()]);
			unset($this->inventory[$player->getName()]);
		}
	}
	
	public function refreshAuction(){
		$auction = yaml_parse_file($this->getDataFolder() . "Auction.yml");
		$this->auctionItems = [];
		if(!empty($auction)) foreach($auction as $key => $val) $this->auctionItems[$key] = $val;
	}

	public function addAuctionItems($player, $inventory, int $page = 0){
		$this->writeLog($player->getName() . " adding Auction Items");
		$this->refreshAuction();
		if($inventory->getDefaultSize() == 54){
			$inventory->clearAll();
			if(!empty($this->auctionItems)) {
				$auction = yaml_parse_file($this->getDataFolder() . "Auction.yml");
				$ahItems = [];
				foreach($auction as $key => $val) $ahItems[$key] = $val;
				foreach($ahItems as $data){
					$timeNow = time();
					if($data[8] < $timeNow){
						unset($ahItems[$data[9]]);
					}
				}
				if(!empty($ahItems)) {
					$chunked = array_chunk($ahItems, 44, true);
					if($page < 0){
						$page = count($chunked) - 1;
					}
					$page = isset($chunked[$page]) ? $page : 0;
					foreach($chunked[$page] as $data){
						$timeNow = time();
						if($data[8] > $timeNow){
							$item = Item::get($data[1], $data[2], $data[3]);
							$remainingTime = $data[8] - $timeNow;
							$day = floor($remainingTime / 86400);
							$hourSeconds = $remainingTime % 86400;
							$hour = floor($hourSeconds / 3600);
							$minuteSec = $hourSeconds % 3600;
							$minute = floor($minuteSec / 60);
							$remainingSec = $minuteSec % 60;
							$second = ceil($remainingSec);
							if($day >= 1){
								$expiration = $day . " day(s)";
							} else {
								if($hour >= 1){
									$expiration = $hour . " hour(s) & " . $minute . " minute(s)";
								} else {
									if($minute >= 1){
										$expiration = $minute . " minute(s) & " . $second . " second(s)";
									} else {
										$expiration = $second . " second(s)";
									}
								}
							}
							$item->setNamedTagEntry(new IntArrayTag("AHUcontents", [$data[7], $data[9]]));
							$item->setCustomName(str_replace(["{itemName}", "{seller}", "{price}", "{expiration}"], [$data[4], $data[0], $data[7], $expiration], $this->auctionSettings["ItemDisplay"]));
							foreach($data[5] as $lore){
								if($lore != 99999){
									$item->setLore($data[5]);
								}
							}
							foreach($data[6] as $enchant) {
								$enchant = explode(':', $enchant);
								$encId = $enchant[0];
								$encLvl = $enchant[1];
								if($encId != 99999 && $encLvl != 99999){
									$enchantment = Enchantment::getEnchantment($encId);
									if($enchantment != null){
										$enchInstance = new EnchantmentInstance($enchantment, $encLvl);
										$item->addEnchantment($enchInstance);
									}
								}
							}
							$inventory->addItem($item);
						}
					}
				}
			}
			$name = $player->getName();
			$result = $this->db->query("SELECT * FROM limits WHERE player = '$name';");
			$array = $result->fetchArray(SQLITE3_ASSOC);	
			if (empty($array)) {
				$limit = $this->db->prepare("INSERT INTO limits(player, total) VALUES (:player, :total);");
				$limit->bindValue(":player", $name);
				$limit->bindValue(":total", 0);
				$limit->execute();
			}
			$item = Item::get(264, 0, 1);
			$item->setCustomName(str_replace(["{myauction}", "{auctionlimit}"], [$array['total'], $this->settings["Limit"]], $this->auctionSettings["MyAuction"]));
			$item->setNamedTagEntry(new StringTag("AHUmenus", "myauction"));
			$inventory->setItem(45, $item);
			$item = Item::get(130, 0, 1);
			$item->setCustomName($this->auctionSettings["Bin"]);
			$item->setNamedTagEntry(new StringTag("AHUmenus", "bin"));
			$inventory->setItem(46, $item);
			$item = Item::get(339, 0, 1);
			$item->setCustomName($this->auctionSettings["LeftArrow"]);
			$item->setNamedTagEntry(new IntArrayTag('turner', [0, $page]));
			$inventory->setItem(48, $item);
			$item = Item::get(54, 0, 1);
			$item->setCustomName($this->auctionSettings["Refresh"]);
			$item->setNamedTagEntry(new StringTag("AHUmenus", "refresh"));
			$inventory->setItem(49, $item);
			$item = Item::get(339, 0, 1);
			$item->setCustomName($this->auctionSettings["RightArrow"]);
			$item->setNamedTagEntry(new IntArrayTag('turner', [1, $page]));
			$inventory->setItem(50, $item);
			$item = Item::get(266, 0, 1);
			$item->setCustomName($this->auctionSettings["HowToSell"]);
			$item->setNamedTagEntry(new StringTag("AHUmenus", "howtosell"));
			$inventory->setItem(52, $item);
			$item = Item::get(340, 0, 1);
			$item->setCustomName($this->auctionSettings["Guide"]);
			$item->setNamedTagEntry(new StringTag("AHUmenus", "guide"));
			$inventory->setItem(53, $item);
		} else {
			$player->sendMessage($this->message["Prefix"] . $this->message["FailedToOpen"]);
		}
	}

	public function onTransaction(InventoryTransactionEvent $event){
		$transactions = $event->getTransaction()->getActions();
		$player = null;
		$chestinv = null;
		$action = null;
		foreach($transactions as $transaction){
			if($transaction instanceof SlotChangeAction) {
				/*
				if(($inv = $transaction->getInventory()) instanceof PlayerInventory){
					$player = $transaction->getInventory()->getHolder();
					$action = $transaction;
					$item = $action->getSourceItem();
					if($item->getNamedTag()->hasTag("AHUmenus")){
						$event->setCancelled(true);
						$player->getInventory()->clearAll();
						$player->sendMessage($this->message["Prefix"] . $this->message["NoDupe"]);
					}
					if($item->getNamedTag()->hasTag("turner")){
						$event->setCancelled(true);
						$player->getInventory()->clearAll();
						$player->sendMessage($this->message["Prefix"] . $this->message["NoDupe"]);
					}
					if($item->getNamedTag()->hasTag("AHUcontents")){
						$event->setCancelled(true);
						$player->getInventory()->clearAll();
						$player->sendMessage($this->message["Prefix"] . $this->message["NoDupe"]);
					}
					if($item->getNamedTag()->hasTag("AHUBinMenus")){
						$event->setCancelled(true);
						$player->getInventory()->clearAll();
						$player->sendMessage($this->message["Prefix"] . $this->message["NoDupe"]);
					}
					if($item->getNamedTag()->hasTag("binturner")){
						$event->setCancelled(true);
						$player->getInventory()->clearAll();
						$player->sendMessage($this->message["Prefix"] . $this->message["NoDupe"]);
					}
				}
				*/
				if(($inv = $transaction->getInventory()) instanceof ChestInventory){
					foreach($inv->getViewers() as $assumed){
						if($assumed instanceof Player){
							$player = $assumed;
							$chestinv = $inv;
							$action = $transaction;
							if(($player ?? $chestinv ?? $action) === null){
								return;
							}
							if($player->getGamemode() == 0){
								$item = $action->getSourceItem();
								if($item->getId() === Item::AIR){
									$this->writeLog($player->getName() . " Try to click Air Item in Auction House");
									return;
								}
								if($item->getNamedTag()->hasTag("AHUmenus")){
									$event->setCancelled(true);
									$menu = $item->getNamedTag()->getString("AHUmenus");
									if($menu == "bin"){
										$this->writeLog($player->getName() . " click Bin");
										$this->sendRealBlock($player);
										$this->getScheduler()->scheduleDelayedTask(new Task\AHBin($this, $player), 15);
									}
									if($menu == "refresh"){
										$this->writeLog($player->getName() . " click Refresh");
										$this->addAuctionItems($player, $chestinv);
									}
								}
								if($item->getNamedTag()->hasTag("turner")){
									$event->setCancelled(true);
									$this->writeLog($player->getName() . " click Page");
									$pagedata = $item->getNamedTag()->getIntArray("turner");
									$page = $pagedata[0] === 0 ? --$pagedata[1] : ++$pagedata[1];
									$this->addAuctionItems($player, $chestinv, $page);
								}
								if($item->getNamedTag()->hasTag("AHUcontents")){
									$event->setCancelled(true);
									$data = $item->getNamedTag()->getIntArray("AHUcontents");
									if(!isset($this->clickItem[$player->getName()])){
										$this->clickItem[$player->getName()] = $data[1];
									} else {
										if($this->clickItem[$player->getName()] != $data[1]){
											unset($this->clickItem[$player->getName()]);
										} else {
											unset($this->clickItem[$player->getName()]);
											$this->refreshAuction();
											if(isset($this->auctionItems[$data[1]])){
												if(EconomyAPI::getInstance()->myMoney($player) >= $data[0]){
													$itemData = $this->auctionItems[$data[1]] ?? null;
													if($itemData !== null){
														$this->writeLog($player->getName() . " bought an item with ID: " . $data[1]);
														$item = Item::get($itemData[1], $itemData[2], $itemData[3]);
														$item->setCustomName($itemData[4]);
														foreach($itemData[5] as $lore){
															if($lore != 99999){
																$item->setLore($itemData[5]);
															}
														}
														foreach($itemData[6] as $enchant) {
															$enchant = explode(':', $enchant);
															$encId = $enchant[0];
															$encLvl = $enchant[1];
															if($encId != 99999 && $encLvl != 99999){
																$enchantment = Enchantment::getEnchantment($encId);
																$enchInstance = new EnchantmentInstance($enchantment, $encLvl);
																$item->addEnchantment($enchInstance);
															}
														}
														$player->getInventory()->addItem($item);
														$seller = $this->getServer()->getPlayer($itemData[0]);
														if($seller instanceof Player){
															EconomyAPI::getInstance()->addMoney($seller, $itemData[7]);
															$seller->sendMessage($this->message["Prefix"] . str_replace(["{money}"], [$itemData[7]], $this->message["ReceivedMoney"]));
														} else {
															$sellerName = $itemData[0];
															$money = $itemData[7];
															$this->db->query("UPDATE pending SET money = money + '$money' WHERE player = '$sellerName'");
														}
														$sellerName = $itemData[0];
														$this->db->query("UPDATE limits SET total = total - 1 WHERE player = '$sellerName'");
														EconomyAPI::getInstance()->reduceMoney($player, $itemData[7]);
														$player->sendMessage($this->message["Prefix"] . str_replace(["{itemName}", "{itemCount}", "{price}"], [$itemData[4], $itemData[3], $itemData[7]], $this->message["PurchaseSuccess"]));
														$this->auction->remove($data[1]);
														$this->auction->save();
														$this->addAuctionItems($player, $chestinv);
														$this->writeLog($player->getName() . " received the item");
													}
												} else {
													$player->sendMessage($this->message["Prefix"] . $this->message["NotEnoughMoney"]);
												}
											} else {
												$player->sendMessage($this->message["Prefix"] . $this->message["NotAvailable"]);
											}
										}
									}
								}
								if($item->getNamedTag()->hasTag("AHUBinMenus")){
									$event->setCancelled(true);
									$menu = $item->getNamedTag()->getString("AHUBinMenus");
									if($menu == "backtoauction"){
										$this->writeLog($player->getName() . " click Back To Auction");
										$this->sendRealBlock($player);
										$this->getScheduler()->scheduleDelayedTask(new Task\AHAuction($this, $player), 15);
									}
									if($menu == "claimall"){
										$this->writeLog($player->getName() . " click Claim All");
										$this->getBinItems($player, $chestinv);
									}
								}
								if($item->getNamedTag()->hasTag("binturner")){
									$event->setCancelled(true);
									$this->writeLog($player->getName() . " click Bin Page");
									$pagedata = $item->getNamedTag()->getIntArray("binturner");
									$page = $pagedata[0] === 0 ? --$pagedata[1] : ++$pagedata[1];
									$this->addBinItems($player, $chestinv, $page);
								}
							}
						}
					}
				}
			}
		}
	}
	
	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		foreach($this->block1 as $block1){
			if($block->x == $block1->x && $block->y == $block1->y && $block->z == $block1->z){
				$event->setCancelled(true);
			}
		}
		foreach($this->block2 as $block2){
			if($block->x == $block2->x && $block->y == $block2->y && $block->z == $block2->z){
				$event->setCancelled(true);
			}
		}
	}
	
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		foreach($this->block1 as $block1){
			if($block->x == $block1->x && $block->y == $block1->y && $block->z == $block1->z){
				$event->setCancelled(true);
			}
		}
		foreach($this->block2 as $block2){
			if($block->x == $block2->x && $block->y == $block2->y && $block->z == $block2->z){
				$event->setCancelled(true);
			}
		}
	}

	public function onPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		foreach($this->block1 as $block1){
			if($block->x == $block1->x && $block->y == $block1->y && $block->z == $block1->z){
				$event->setCancelled(true);
			}
		}
		foreach($this->block2 as $block2){
			if($block->x == $block2->x && $block->y == $block2->y && $block->z == $block2->z){
				$event->setCancelled(true);
			}
		}
	}
	
	public function writeLog($log){
		$oldFile = file_get_contents($this->getDataFolder() . "AuctionLog.txt", FILE_USE_INCLUDE_PATH);
		$date = date("M-d-Y H:i:s");
		$newFile = $oldFile . "\n" . $date . " - " . $log;
		file_put_contents($this->getDataFolder() . "AuctionLog.txt", $newFile);
	}

    public function timer(){
        foreach($this->cooldown->getAll() as $player => $time){
		    $time--;
		    $this->cooldown->set($player, $time);
		    $this->cooldown->save();
		    if($time == 0){
		        $this->cooldown->remove($player);
			    $this->cooldown->save();
            }
        }
    }

    public function hasCooldown($player){
        return $this->cooldown->exists($player->getLowerCaseName());
    }

    public function getCooldownSeconds($player){
        return $this->cooldown->get($player->getLowerCaseName());
    }

    public function getCooldownTime($player){
        return $this->convertSeconds($this->getCooldownSeconds($player));
    }

    public function addCooldown($player){
        $this->cooldown->set($player->getLowerCaseName(), $this->config->get("cooldown-seconds"));
        $this->cooldown->save();
    }

	public function onDamage(EntityDamageEvent $event) : void{
		$entity = $event->getEntity();
			if($event instanceof EntityDamageByEntityEvent){
				if($entity instanceof Player){
					$damager = $event->getDamager();
					if(!$damager instanceof Player) return;
					if($damager->isCreative()) return;
					if($damager->getAllowFlight() === true){
						$damager->sendMessage(TextFormat::DARK_RED . "Flight mode disabled due to combat");
						$damager->setAllowFlight(false);
						$damager->setFlying(false);
					}
				}
			}
		}
	
	public function onQuit(PlayerQuitEvent $ev){
		$player = $ev->getPlayer();
		$name = $player->getName();
            $ev->setQuitMessage(Textformat::DARK_GRAY . "[" . TextFormat::RED . "-" . TextFormat::DARK_GRAY . "] " . TextFormat::RED . $name);
	}


	public function onPlayerKick(PlayerKickEvent $event){
	if($event->getReason() === "Server Full"){
		if ($event->getPlayer()->hasPermission("yolo.bypass")){
			$event->setCancelled(true);
		}
		$event->setQuitMessage("§8(§l§eYolo§r§8)§7 ".TextFormat::GOLD."Server Full \n Get a Donar Rank to join full servers\n YoloNetwork.buycraft.net");

	}

}
	 public function onPreLogin(PlayerPreLoginEvent $ev){
	 	$player = $ev->getPlayer();
	 	if (($this->getServer()->getMaxPlayers()- 19) <= count($this->getServer()->getOnlinePlayers())){
	 		echo "full server";
	 		if ($player->hasPermission("yolo.bypass")){
	 				return true;
	 		}
	 		$ev->setCancelled(true);
	 		$ev->setKickMessage("§8(§l§eYolo§r§8)§7 Server Full \n Get a Donator Rank to join full servers\n");
	
	 	}
	 }

 	public function onQuery(QueryRegenerateEvent $ev){
 		$ev->setMaxPlayerCount($this->getServer()->getMaxPlayers() - 40);
 	}

	 public function onMove(PlayerMoveEvent $ev){
		 $player = $ev->getPlayer();
		 if ($player instanceof Player){
			 if(!$this->inBorder($player)){
				 switch($player->getDirection()){
              	  case 0: //south
            	        $player->knockBack($player, 0, -10, 0, 0.6);
                     break;
          	      case 1: //west
        	             $player->knockBack($player, 0, 0, -10, 0.6);
      	              break;
                 case 2: //north
                     $player->knockBack($player, 0, 10, 0, 0.6);
                     break;
                 case 3: //east
                     $player->knockBack($player, 0, 0, 10, 0.6);
                     break;
        	    }
			    $player->sendMessage("§8(§l§eYolo§r§8)§7 You have reached the world-border you can not move any further");
			 }
		 }
	 }

	public function onDeath(PlayerDeathEvent $ev){
		$cause = $ev->getEntity()->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent) {
			$player = $ev->getEntity();
			$killer = $cause->getDamager();
			$p = $ev->getEntity();
			if($killer instanceof Player){
				$player->sendMessage("§8(§l§eYolo§r§8)§7 ".$killer->getName()." Killed you with " .TextFormat::LIGHT_PURPLE.$killer->getHealth()." hearts §rleft and while using ".TextFormat::BLUE.$killer->getInventory()->getItemInHand()->getName()."§r!");
				$killer->sendMessage("§8(§l§eYolo§r§8)§7 You Killed ".$player->getName()." !");
			}
		}
	}

public function checkVoid(PlayerMoveEvent $event){
    $player = $event->getPlayer();
    $x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorX();
    $y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorY();
    $z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorZ();
    $level = $this->getServer()->getDefaultLevel();
        if($event->getTo()->getFloorY() < -3){
            switch(mt_rand(1, 2) == 1){
              case 1:
              $player->teleport(new Position($x, $y, $z, $level));
              $player->setHealth($player->getHealth(20));
              $player->sendMessage("§d§l» §r§aYou Were Saved From The §eVoid §d§l«");
              break;
              case 2:
              break;
             }
         }
      }

	public function inBorder(Player $player){
		$spawn = $player->getSpawn();
		$toCheck = new Vector3($player->getX(),$player->getY(),$player->getZ());
		$first = new Vector3($spawn->getX()+20000,$spawn->getY(),$spawn->getZ()+20000);
		$second = new Vector3($spawn->getX()-20000,$spawn->getY(),$spawn->getZ()-20000);
		$isInside = (min($first->getX(),$second->getX()) <= $toCheck->getX()) && (max($first->getX(),$second->getX()) >= $toCheck->getX()) && (min($first->getZ(),$second->getZ()) <= $toCheck->getZ()) && (max($first->getZ(),$second->getZ()) >= $toCheck->getZ());
		return $isInside;
	}

	public function broadcastMsg($msg){
		$this->getServer()->broadcastMessage($msg);
	}
	
	public function translateColors($message) 
	{
	$message = str_replace("&0", TextFormat::BLACK, $message);
	$message = str_replace("&1", TextFormat::DARK_BLUE, $message);
	$message = str_replace("&2", TextFormat::DARK_GREEN, $message);
	$message = str_replace("&3", TextFormat::DARK_AQUA, $message);
	$message = str_replace("&4", TextFormat::DARK_RED, $message);
	$message = str_replace("&5", TextFormat::DARK_PURPLE, $message);
	$message = str_replace("&6", TextFormat::GOLD, $message);
	$message = str_replace("&7", TextFormat::GRAY, $message);
	$message = str_replace("&8", TextFormat::DARK_GRAY, $message);
	$message = str_replace("&9", TextFormat::BLUE, $message);
	$message = str_replace("&a", TextFormat::GREEN, $message);
	$message = str_replace("&b", TextFormat::AQUA, $message);
	$message = str_replace("&c", TextFormat::RED, $message);
	$message = str_replace("&d", TextFormat::LIGHT_PURPLE, $message);
	$message = str_replace("&e", TextFormat::YELLOW, $message);
	$message = str_replace("&f", TextFormat::WHITE, $message);
	$message = str_replace("&k", TextFormat::OBFUSCATED, $message);
	$message = str_replace("&l", TextFormat::BOLD, $message);
	$message = str_replace("&m", TextFormat::STRIKETHROUGH, $message);
	$message = str_replace("&n", TextFormat::UNDERLINE, $message);
	$message = str_replace("&o", TextFormat::ITALIC, $message);
	$message = str_replace("&r", TextFormat::RESET, $message);
	return $message;
	}
	
    public function Tags($sender) {
        $form = new SimpleForm(function (Player $sender, int $data = null) {
            if($data !== null) {
                $streeng = "$data";
                $conf = $this->myConfig->get($streeng);
                $permis = $conf[0];
                $tag = $conf[1];

                if ($sender->hasPermission($permis))
                {
                    $prefix = $this->getServer()->getPluginManager()->getPlugin("PureChat");
                    $prefix->setPrefix($tag, $sender);
                    $sender->sendMessage(TextFormat::GREEN . "Tag changed to $conf[1]");
                }
                else{
                    $sender->sendMessage(TextFormat::RED . "You don't have permission to use $conf[1]" . TextFormat::RED . " Tag");
                }

            }
        });
		$title = "§l§8[§bTags§8]";
		$content = "§8Choose your tag";
        $form->setTitle($this->translateColors($title));
        $form->setContent($this->translateColors($content));
        $conf = $this->myConfig->getAll();
        $lock = TextFormat::RED . '§l§cLOCKED';
        $avaible = TextFormat::GREEN . '§l§aAVAILABLE';
        foreach ($conf as $id => $tag)
        {
            if ($sender->hasPermission($tag[0]))
            {
                $form->addButton($this->translateColors("$tag[1]") . "\n" . $avaible);
            }
            else {
                $form->addButton($tag[1] . "\n" . $lock);
            }
        }
        $form->sendToPlayer($sender);
    }
	
	public function Nick($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
						$sender->setDisplayName("$data[0]");
						$sender->setNameTag("$data[0]");
						$sender->sendMessage("§8(§l§eYolo§r§8) §7Successfully Changed Your Nick To $data[0]");
		});
		$form->setTitle("§8[§cYolo§8]");
		$form->addInput("§dNick","§7Ex: Jackson");
		$form->sendToPlayer($sender);
	}
	
    public function Effects($sender){ 
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, int $data = null) { 
            $result = $data;
            if($result === null){
                return true;
            }             
            switch($result){
                case 0:
				if($sender->hasEffect(Effect::SPEED)) {
				$sender->sendMessage(TextFormat::RED . " Wait till the effect runs out");
				} else {
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "effect " . $sender->getName() . " speed 900 1 true");
						}
                break;
                case 1:
				if($sender->hasEffect(Effect::JUMP_BOOST)) {
				$sender->sendMessage(TextFormat::RED . " Wait till the effect runs out");
				} else {
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "effect " . $sender->getName() . " jump_boost 900 1 true");
						}
                break;
                case 2:
				if($sender->hasEffect(Effect::REGENERATION)) {
				$sender->sendMessage(TextFormat::RED . " Wait till the effect runs out");
				} else {
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "effect " . $sender->getName() . " regeneration 900 1 true");
						}
                break;
                case 3:
				if($sender->hasEffect(Effect::HEALTH_BOOST)) {
				$sender->sendMessage(TextFormat::RED . " Wait till the effect runs out");
				} else {
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "effect " . $sender->getName() . " health_boost 900 1 true");
						}
                break;
                case 4:
				if($sender->hasEffect(Effect::NIGHT_VISION)) {
				$sender->sendMessage(TextFormat::RED . " Wait till the effect runs out");
				} else {
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "effect " . $sender->getName() . " night_vision 900 1 true");
						}
                break;
                case 5:
				if($sender->hasEffect(Effect::STRENGTH)) {
				$sender->sendMessage(TextFormat::RED . " Wait till the effect runs out");
				} else {
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "effect " . $sender->getName() . " strength 900 1 true");
						}
                break;
                case 6:
				if($sender->hasEffect(Effect::INVISIBILITY)) {
				$sender->sendMessage(TextFormat::RED . " Wait till the effect runs out");
				} else {
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "effect " . $sender->getName() . " invisibility 900 1 true");
						}
                break;
            }
            
            
            });
            $form->setTitle("§8(§l§eYolo§r§8)");
            $form->addButton("§bSPEED II\nduration: 15min");
            $form->addButton("§aJUMPBOOST II\nduration: 15min");
            $form->addButton("§dREGENERATION II\nduration: 15min");
            $form->addButton("§eHEALTHBOOST II\nduration: 15min");
            $form->addButton("§9NIGHT_VISION II\nduration: 15min");
            $form->addButton("§cSTRENGTH II\nduration: 15min");
            $form->addButton("§7INVISIBILITY II\nduration: 15min");
                        
            $form->sendToPlayer($sender);
    }
    
    public function RankSystem($sender){ 
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, int $data = null) { 
            $result = $data;
            if($result === null){
                return true;
            }             
            switch($result){
                case 0:
                $this->SetGroup($sender);
                break;
                case 1:
                $this->AddGroup($sender);
                break;
                case 2:
                $this->DelGroup($sender);
                break;
                case 3:
                $this->AddGroupPermission($sender);
                break;
                case 4:
                $this->DelGroupPermission($sender);
                break;
                case 5:
                $this->AddUserPermission($sender);
                break;
                case 6:
                $this->DelUserPermission($sender);
                break;
                case 7:
                $this->SetFormat($sender);
                break;
                case 8:
                $this->SetNameTag($sender);
                break;
            }
            
            
            });
            $form->setTitle("§8(§l§eYolo§r§8)");
            $form->addButton("§8SetGroup\n§8Set a player's group");
            $form->addButton("§8AddGroup\n§8Add a group");
            $form->addButton("§8DelGroup\n§8Delete a group");
            $form->addButton("§8AddGroupPermission\n§8Add a group permission");
            $form->addButton("§8DelGroupPermission\n§8Delete a group permission");
            $form->addButton("§8AddUserPermission\n§8Add a user permission");
            $form->addButton("§8DelUserPermission\n§8Delete a user permission");
            $form->addButton("§8SetFormat\n§8Set a rank format");
            $form->addButton("§8SetNameTag\n§8Set a rank nametag");
                        
            $form->sendToPlayer($sender);
    }
    
	public function SetGroup($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "setgroup $data[0] $data[1] $data[2]";
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), $cmd);
						$sender->sendMessage("§8(§bRankSystem§8) §7Changed §b$data[0] §7Rank to §b$data[1]");
		});
		$form->setTitle("§b§lSetGroup");
		$form->addInput("§bPlayer Name");
		$form->addInput("§bRank Name");
		$form->addInput("§bWorld §7(optional)");
		$form->sendToPlayer($sender);
	}
	
	public function AddGroup($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "addgroup $data[0]";
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), $cmd);
						$sender->sendMessage("§8(§bRankSystem§8) §b$data[0] §7Has been created");
		});
		$form->setTitle("§b§lAddGroup");
		$form->addInput("§bRank Name");
		$form->sendToPlayer($sender);
	}
	public function DelGroup($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "delgroup $data[0]";
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), $cmd);
						$sender->sendMessage("§8(§bRankSystem§8) §b$data[0] §7Has been deleted");
		});
		$form->setTitle("§b§lDelGroup");
		$form->addInput("§bRank Name");
		$form->sendToPlayer($sender);
	}
	
	public function AddGroupPermission($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "setgperm $data[0] $data[1] $data[2]";
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), $cmd);
						$sender->sendMessage("§8(§bRankSystem§8) §b$data[1] §7has been added to §b$data[0]");
		});
		$form->setTitle("§b§lAddGroupPermission");
		$form->addInput("§bRank Name");
		$form->addInput("§bPermission Node");
		$form->addInput("§bWorld §7(optional)");
		$form->sendToPlayer($sender);
	}
	
	public function DelGroupPermission($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "unsetgperm $data[0] $data[1]";
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), $cmd);
						$sender->sendMessage("§8(§bRankSystem§8) §b$data[1] §7has been removed from §b$data[0]");
		});
		$form->setTitle("§b§lDelGroupPerm");
		$form->addInput("§bRank Name");
		$form->addInput("§bPermission Node");
		$form->sendToPlayer($sender);
	}
	
	public function AddUserPermission($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "setuperm $data[0] $data[1] $data[2]";
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), $cmd);
						$sender->sendMessage("§8(§bRankSystem§8) §b$data[1] §7has been added to §b$data[0]");
		});
		$form->setTitle("§b§lAddUserPerm");
		$form->addInput("§bPlayer Name");
		$form->addInput("§bPermission Node");
		$form->addInput("§bWorld §7(optional)");
		$form->sendToPlayer($sender);
	}
	
	public function DelUserPermission($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "unsetuperm $data[0] $data[1]";
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), $cmd);
						$sender->sendMessage("§8(§bRankSystem§8) §b$data[1] §7has been removed from §b$data[0]");
		});
		$form->setTitle("§b§lDelUserPerm");
		$form->addInput("§bPlayer Name");
		$form->addInput("§bPermission Node");
		$form->sendToPlayer($sender);
	}
	
	public function SetFormat($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "setformat $data[0] global $data[1]";
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), $cmd);
						$sender->sendMessage("§8(§bRankSystem§8) §b$data[0]'s §7New format is $data[1]");
		});
		$form->setTitle("§b§lSetFormat");
		$form->setLabel("§2Available PlaceHolders:\§b{prefix}:Shows The prefix\n§b{suffix}:Shows The Suffix\n§b{display_name}:Shows The PlayerName\n§b{fac_name}:Shows The FactionName\n§b{fac_rank}:Shows The FactionRank\n§b{msg}:Shows The Player Msg");
		$form->addInput("§bRank Name");
		$form->addInput("§bRank Format");
		$form->sendToPlayer($sender);
	}
	
	public function SetNameTag($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "setnametag $data[0] global $data[1]";
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), $cmd);
						$sender->sendMessage("§8(§bRankSystem§8) §b$data[0]'s §7New NameTag is $data[1]");
		});
		$form->setTitle("§b§lSetNameTag");
		$form->setLabel("§2Available PlaceHolders:\§b{prefix}:Shows The prefix\n§b{suffix}:Shows The Suffix\n§b{display_name}:Shows The PlayerName\n§b{fac_name}:Shows The FactionName\n§b{fac_rank}:Shows The FactionRank\n§b{msg}:Shows The Player Msg");
		$form->addInput("§bRank Name");
		$form->addInput("§bRank NameTag");
		$form->sendToPlayer($sender);
	}
    
    public function BanSystem($sender){ 
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, int $data = null) { 
            $result = $data;
            if($result === null){
                return true;
            }             
            switch($result){
                case 0:
                $this->Ban($sender);
                break;
                case 1:
                $this->BanIP($sender);
                break;
                case 2:
                $this->TempBan($sender);
                break;
                case 3:
                $this->TempIPBan($sender);
                break;
                case 4:
                $this->Mute($sender);
                break;
                case 5:
                $this->MuteIP($sender);
                break;
                case 6:
                $this->TempMute($sender);
                break;
                case 7:
                $this->TempIPMute($sender);
                break;
                case 8:
                $this->UnBan($sender);
                break;
                case 9:
                $this->UnBanIP($sender);
                break;
                case 10:
                $this->UnMute($sender);
                break;
                case 11:
                $this->UnMuteIP($sender);
                break;
            }
            
            
            });
            $form->setTitle("§8(§l§eYolo§r§8)");
            $form->addButton("§8Ban\n§8Ban a player");
            $form->addButton("§8Ban-IP\n§8Ban a player's IP");
            $form->addButton("§8TempBan\n§8TempBan a player");
            $form->addButton("§8TempBan-IP\n§8TempBan a player's IP");
            $form->addButton("§8Mute\n§8Mute a player");
            $form->addButton("§8Mute-IP\n§8Mute a player's IP");
            $form->addButton("§8TempMute\n§8TempMute a player");
            $form->addButton("§8TempMute-IP\n§8TempMute a player's IP");
            $form->addButton("§8UnBan\n§8UnBan a player");
            $form->addButton("§8UnBan-IP\n§8UnBan a player's IP");
            $form->addButton("§8UnMute\n§8UnMute a player");
            $form->addButton("§8UnMute-IP\n§8UnMute a player's IP");
                        
            $form->sendToPlayer($sender);
    }
    
	public function Ban($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "ban $data[0] $data[1]";
			$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
		});
		$form->setTitle("§b§lBan");
		$form->addInput("§bPlayerName");
		$form->addInput("§bReason");
		$form->sendToPlayer($sender);
	}
	
	public function BanIP($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "ban-ip $data[0] $data[1]";
			$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
		});
		$form->setTitle("§b§lIPBan");
		$form->addInput("§bPlayerName");
		$form->addInput("§bReason");
		$form->sendToPlayer($sender);
	}
	
	public function TempBan($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "tban $data[0] $data[1] $data[2]";
			$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
		});
		$form->setTitle("§b§lTempBan");
		$form->addInput("§bPlayerName");
		$form->addInput("§bTimeLimit");
		$form->addInput("§bReason");
		$form->sendToPlayer($sender);
	}
	
	public function TempIPBan($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "tban-ip $data[0] $data[1] $data[2]";
			$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
		});
		$form->setTitle("§b§lTempIPBan");
		$form->addInput("§bPlayerName");
		$form->addInput("§bTimeLimit");
		$form->addInput("§bReason");
		$form->sendToPlayer($sender);
	}
	
	public function TempMute($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "tmute $data[0] $data[1] $data[2]";
			$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
		});
		$form->setTitle("§b§lTempMute");
		$form->addInput("§bPlayerName");
		$form->addInput("§bTimeLimit");
		$form->addInput("§bReason");
		$form->sendToPlayer($sender);
	}
	
	public function TempMuteIP($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "tmute-ip $data[0] $data[1] $data[2]";
			$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
		});
		$form->setTitle("§b§lTempMuteIP");
		$form->addInput("§bPlayerName");
		$form->addInput("§bTimeLimit");
		$form->addInput("§bReason");
		$form->sendToPlayer($sender);
	}
	
	public function UnBan($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "unban $data[0]";
			$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
		});
		$form->setTitle("§b§lUnBan");
		$form->addInput("§bPlayerName");
		$form->sendToPlayer($sender);
	}
	
	public function UnBanIP($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "unban-ip $data[0]";
			$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
		});
		$form->setTitle("§b§lUnBan-IP");
		$form->addInput("§bAddress");
		$form->sendToPlayer($sender);
	}
	
	public function UnMute($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "unmute $data[0]";
			$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
		});
		$form->setTitle("§b§lUnMute");
		$form->addInput("§bPlayerName");
		$form->sendToPlayer($sender);
	}
	
	public function UnMuteIP($sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createCustomForm(function(Player $sender, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			$cmd = "unmute-ip $data[0]";
			$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
		});
		$form->setTitle("§b§lUnMute-IP");
		$form->addInput("§bAddress");
		$form->sendToPlayer($sender);
	}

    public function Spawner($sender){

              $spawnermenu = InvMenu::create(InvMenu::TYPE_CHEST);
		            $spawnermenu->readonly();
		            $spawnermenu->setName("§l§8§oSpawnerShop");
		            $spawnermenu->setListener([$this, "handleSpawnerMenu"]);
		            $inventory = $spawnermenu->getInventory();	       
		            $inventory->setItem(0,Item::get(Item::IRON_INGOT)->setCustomName("§b§oIronGolem §7Spawner")->setLore(["\n§cCost: $2000000\n§7Click To Buy!"]));
		            $inventory->setItem(1,Item::get(Item::BLAZE_ROD)->setCustomName("§n§oBlaze §7Spawner")->setLore(["\n§cCost: $390000\n§7Click To Buy!"]));
		            $inventory->setItem(2,Item::get(Item::ROTTEN_FLESH)->setCustomName("§b§oZombie §7Spawner")->setLore(["\n§cCost: $390000\n§7Click To Buy!"]));
		            $inventory->setItem(3,Item::get(Item::BONE)->setCustomName("§b§oSkeleton §7Spawner")->setLore(["\n§cCost: $390000\n§7Click To Buy!"]));
		            $inventory->setItem(4,Item::get(Item::RAW_BEEF)->setCustomName("§b§oCow §7Spawner")->setLore(["\n§cCost: $100000\n§7Click To Buy!"]));
		            $inventory->setItem(5,Item::get(Item::RAW_PORKCHOP)->setCustomName("§b§oPig §7Spawner")->setLore(["\n§cCost: $75000\n§7Click To Buy!"]));
		            $inventory->setItem(6,Item::get(Item::RAW_CHICKEN)->setCustomName("§b§oChicken §7Spawner")->setLore(["\n§cCost: $50000\n§7Click To Buy!"]));
		            $inventory->setItem(7,Item::get(Item::WOOL)->setCustomName("§b§oSheep §7Spawner")->setLore(["\n§cCost: $25000\n§7Click To Buy!"]));
			    $inventory->setItem(26,Item::get(Item::GLASS)->setCustomName("§l§cEXIT")->setLore(["\n§bClick to exit this GUI"]));
                    $spawnermenu->send($sender);
    }
    
    public function handleSpawnerMenu(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
        $in = $player->getInventory()->getItemInHand()->getCustomName();
            if($itemClicked->getId() === Item::IRON_INGOT && "§7IronGolem Spawner"){  
					if(\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player) >= ("2000000")){
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "ms IronGolem 1 " . $player->getName());
						$player->sendMessage("§c§l-2000000");
                				EconomyAPI::getInstance()->reduceMoney($player, ("20000000"));
                $player->sendPopup("§aBought an §7IronGolem Spawner");
					} else {
						$player->sendMessage("§cYou do not have enough money to purchase this Spawner");
	    $player->sendPopup("§cFailed to buy an §7IronGolem Spawner");
	}
            }
            if($itemClicked->getId() === Item::BLAZE_ROD && "§7Blaze Spawner"){
					if(\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player) >= ("390000")){
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "ms blaze 1 " . $player->getName());
						$player->sendMessage("§c§l-390000");
                				EconomyAPI::getInstance()->reduceMoney($player, ("390000"));
                $player->sendPopup("§aBought a §7Blaze Spawner");
					} else {
						$player->sendMessage("§l§cYou do not have enough money to purchase this Spawner");
					}
				}
            if($itemClicked->getId() === Item::ROTTEN_FLESH && "§7Zombie Spawner"){
					if(\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player) >= ("390000")){
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "ms zombie 1 " . $player->getName());
						$player->sendMessage("§c§l-390000");
                				EconomyAPI::getInstance()->reduceMoney($player, ("390000"));
                $player->sendPopup("§aBought a §7Zombie Spawner");
					} else {
						$player->sendMessage("§l§cYou do not have enough money to purchase this Spawner");
	    $player->sendPopup("§cFailed to buy a §7Zombie Spawner");
					}
				}
            if($itemClicked->getId() === Item::BONE && "§7Skeleton Spawner"){
					if(\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player) >= ("390000")){
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "ms skeleton 1 " . $player->getName());
						$player->sendMessage("§c§l-390000");
                				EconomyAPI::getInstance()->reduceMoney($player, ("390000"));
                $player->sendPopup("§aBought a §7Skeleton Spawner");
					} else {
						$player->sendMessage("§l§cYou do not have enough money to purchase this Spawner");
	    $player->sendPopup("§cFailed to buy a §7Skeleton Spawner");
					}
				}
            if($itemClicked->getId() === Item::RAW_BEEF && "§7Cow Spawner"){
					if(\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player) >= ("390000")){
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "ms cow 1 " . $player->getName());
						$player->sendMessage("§c§l-100000");
                				EconomyAPI::getInstance()->reduceMoney($player, ("100000"));
                $player->sendPopup("§aBought a §7Cow Spawner");
					} else {
						$player->sendMessage("§l§cYou do not have enough money to purchase this Spawner");
	    $player->sendPopup("§cFailed to buy a §7Cow Spawner");
					}
				}
            if($itemClicked->getId() === Item::RAW_PORKCHOP && "§7Pig Spawner"){
					if(\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player) >= ("390000")){
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "ms pig 1 " . $player->getName());
						$player->sendMessage("§c§l-75000");
                				EconomyAPI::getInstance()->reduceMoney($player, ("75000"));
                $player->sendPopup("§aBought a §7Pig Spawner");
					} else {
						$player->sendMessage("§l§cYou do not have enough money to purchase this Spawner");
	    $player->sendPopup("§cFailed to buy a §7Pig Spawner");
					}
				}
            if($itemClicked->getId() === Item::RAW_CHICKEN && "§7Chicken Spawner"){
					if(\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player) >= ("390000")){
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "ms chicken 1 " . $player->getName());
						$player->sendMessage("§c§l-50000");
                				EconomyAPI::getInstance()->reduceMoney($player, ("50000"));
                $player->sendPopup("§aBought a §7Chicken Spawner");
					} else {
						$player->sendMessage("§l§cYou do not have enough money to purchase this Spawner");
	    $player->sendPopup("§cFailed to buy a §7Chicken Spawner");
					}
				}
            if($itemClicked->getId() === Item::WOOL && "§7Sheep Spawner"){
					if(\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player) >= ("390000")){
						$this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "ms sheep 1 " . $player->getName());
						$player->sendMessage("§c§l-25000");
                				EconomyAPI::getInstance()->reduceMoney($player, ("25000"));
                $player->sendPopup("§aBought a §7Sheep Spawner");
					} else {
						$player->sendMessage("§l§cYou do not have enough money to purchase this Spawner");
	    $player->sendPopup("§cFailed to buy a §7Sheep Spawner");
					}
            }
            if($itemClicked->getId() === Item::GLASS && "§l§cEXIT"){
            $player->removeWindow($action->getInventory());
	    $player->sendPopup("§cExiting...");
            }
            return true;
    }


}

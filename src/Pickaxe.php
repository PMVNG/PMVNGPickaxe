<?php 

namespace DavidGlitch04\PMVNGPickaxe;

//Essentials Class
use pocketmine\plugin\PluginBase;
use pocketmine\command\{CommandSender, Command, ConsoleCommandSender};
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\Listener;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants as CE;
use DavidGlitch04\PMVNGPickaxe\listener\EventListener;
use DavidGlitch04\PMVNGPickaxe\utils\SingletonTrait;

class Pickaxe extends PluginBase implements Listener{

	const KEY_VALUE = "Level";

	use SingletonTrait;
	
	private $pic, $li, $CE, $score, $eco, $form;

	protected function onEnable(): void{
		self::setInstance($this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->saveDefaultConfig();
		$this->pic = new Config($this->getDataFolder()."pickaxe.yml", Config::YAML);
		$this->li = $this->getServer()->getPluginManager()->getPlugin("LockedItem");
		$this->CE =  $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
		$this->score =  $this->getServer()->getPluginManager()->getPlugin("ScoreMC");
		$this->eco =  $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		$this->form =  $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$task = new Score($this);
		$this->getScheduler()->scheduleRepeatingTask($task, 20);

		//Check Plugin
		if($this->li == null){ //LockedItem 
			$this->getLogger()->notice("PMVNG Pickaxe > You have not installed LockedItem, please download it at https://poggit.pmmp.io/p/LockedItem/3.0.0 and then try again.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		if($this->CE == null){ //PiggyCustomEnchant
			$this->getLogger()->notice("PMVNG Pickaxe > You have not installed PiggyCustomEnchants, please download it and then try again.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		if($this->score == null){ //ScoreMC
			$this->getLogger()->notice("PMVNG Pickaxe > You have not installed ScoreMC, please download it and then try again.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		if($this->eco == null){ //EconomyAPI
			$this->getLogger()->notice("PMVNG Pickaxe > You have not installed EconomyAPI, please download it and then try again.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		if($this->form == null){ //FormAPI
			$this->getLogger()->notice("PMVNG Pickaxe > You have not installed FormAPI, please download it and then try again.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}

    //Event Join
	

	//Item Event
	

	//BreakBlock Popup
	

//Commands
	public function onCommand(CommandSender $s, Command $cmd, String $label, Array $args): bool 
	{
		///command /pickaxe
		if($cmd->getName() == "pickaxe"){
			if($s instanceof Player){
				$this->MainForm($s);
			} else{
				$this->getLogger()->error($this->getConfig()->get("Console-CMD"));
			}
		}
		//Admin
		if($cmd->getName() == "adminpickaxe"){
			if(!$s->isOp()){
				$s->sendMessage("§cYou can't use this command!");
			} else{
				$this->AdminForm($s);
			}
		}
		if($cmd->getName() == "toppickaxe"){
			$levelplot = $this->pic->getAll();
			$max = 0;
			$max = count($levelplot);
			$max = ceil(($max / 5));
			
			$message = "";
			$message1 = "";
			
			$page = array_shift($args);
			$page = max(1, $page);
			$page = min($max, $page);
			$page = (int)$page;
			
			$aa = $this->pic->getAll();
			arsort($aa);
			$i = 0;
			
			foreach ($aa as $b=>$a) {
				if (($page - 1) * 5 <= $i && $i <= ($page - 1) * 5 + 4) {
					$i1 = $i + 1;
					$c = $this->pic->get($b)["Level"];
					$trang = "§l§c⚒§6 Xếp Hạng Cấp Cúp §a ".$page."§f/§a".$max."§c ⚒\n";
					$message .= "§l§bHạng §e".$i1."§b thuộc về §c".$b."§f Với §e".$c." §cCấp\n";
					$message1 .= "§l§bHạng §e".$i1."§b thuộc về §c".$b."§f Với §e".$c." §cCấp\n";
				} $i++;
			}
			$form = $this->form->createCustomForm(function (Player $s, $data) use ($trang, $message) {
				if ($data === null) { 
					return $this->MainForm($s); 
				}
				$this->getServer()->dispatchCommand($s, "toppickaxe ".$data[1]);
			});
			$form->setTitle("§6§lTOP PICKAXE");
			$form->addLabel($trang. $message);
			$form->addInput("§1§l↣ §aNext Page", "0");
			$form->sendToPlayer($s);
		}
		return true;
	}


	//MainForm
	public function MainForm(Player $player){
		$form = $this->form->createSimpleForm(function (Player $player, ?int $data = null){
			$result = $data;
			if($data === null){
				return false;
			}
			switch ($result) {
				case 0:
					$this->info($player);
					break;
				case 1:
				$this->getServer()->dispatchCommand($player, "toppickaxe");
				break;
				case 2:
				$this->popup($player);
				break;
			}
		});
		$type = $this->getConfig()->get("Type");
		$png1 = $this->getConfig()->get("PNGINFO");
		$png2 = $this->getConfig()->get("PNGTOP");
		$png3 = $this->getConfig()->get("PNGPOPUP");
		$form->setTitle($this->getConfig()->get("Title"));
		$form->setContent($this->getConfig()->get("Content"));
		$form->addButton($this->getConfig()->get("ButtonINFO"), $type, $png1);
		$form->addButton($this->getConfig()->get("ButtonTOP"), $type, $png2);
		$form->addButton($this->getConfig()->get("ButtonPOPUP"), $type, $png3);
		$form->sendToPlayer($player);
		return $form;
	}

	//Info Form
	public function info(Player $player){
		$form = $this->form->createSimpleForm(function (Player $player, ?int $data = null){
			$result = $data;
			if($data === null){
				return false;
			}
		});
		$type = $this->getConfig()->get("Type");
		$png = $this->getConfig()->get("PNGBACK");
		$form->setTitle($this->getConfig()->get("Title"));
		$form->setContent($this->getConfig()->get("Contentinfo"));
		$form->addButton($this->getConfig()->get("ButtonBACK"), $type, $png);
		$form->sendToPlayer($player);
	}
	//Admin Form

	public function AdminForm(Player $player){
		$form = $this->form->createCustomForm(function (Player $player, $data){
			if($data == null){
				return false;
			}
			if($data[0] == null || $data[1] == null || $data[2] == null){
				$player->sendMessage("§cVui lòng nhập đầy đủ thông tin!");
				return false;
			}
			if(!is_numeric($data[0]) || !is_numeric($data[1]) || !is_numeric($data[2])){
				$player->sendMessage("§cThông tin phải là số!");
				return false;
			}
			$this->pic->set(($player->getName()), [
				"Exp" => $data[1],
				"Level" => $data[0],
				"NextExp" => $data[2],
				"Popup" => true
			]);
			$this->pic->save();
		});
		$form->setTitle("§c§lAdmin Pickaxe");
		$form->addInput("§1§l↣ §aLevel:", "0");
		$form->addInput("§1§l↣ §aExp:", "0");
		$form->addInput("§1§l↣ §aNextExp:", "0");
		$form->sendToPlayer($player);
		return $form;
	}
//Popup Form
	public function popup(Player $player){
		$form = $this->form->createCustomForm(function (Player $player, $data){
			if($data === null){
				return $this->MainForm($player);
			}
			if($data[0] == true){
				$current = $this->pic->get($player->getName())["Exp"];
			$currentlv = $this->pic->get($player->getName())["Level"];
			$currentne = $this->pic->get($player->getName())["NextExp"];
			$this->pic->set(($player->getName()), [
				"Exp" => $current,
				"Level" => $currentlv,
				"NextExp" => $currentne,
				"Popup" => true
			]);
			$this->pic->save();
			}
			if($data[0] == false){
				$current = $this->pic->get($player->getName())["Exp"];
			$currentlv = $this->pic->get($player->getName())["Level"];
			$currentne = $this->pic->get($player->getName())["NextExp"];
			$this->pic->set(($player->getName()), [
				"Exp" => $current,
				"Level" => $currentlv,
				"NextExp" => $currentne,
				"Popup" => false
			]);
			$this->pic->save();
			}
		});
		$form->setTitle("§6§lPoppup Pickaxe");
		$form->addToggle("§1§l↣ §aKéo sang phải để bật", false);
		$form->sendToPlayer($player);
		return $form;
	}

	//Name Pickaxe Level
	public function getPickaxeName($player){
		if($player instanceof Player){
				$player = $player->getName();
				}
				$name = "§l§a⚒§b PMVNG PICKAXE §6 §r§l[§cLevel: §b ".$this->pic->get($player)["Level"]." §r§l]§a§l ".$player;
			return $name;	
	}

	//Lore Pickaxe Level
	public function getPickaxeLore($player){
		if($player instanceof Player){
				$player = $player->getName();
				}
		$lore = "§b§l⇲ Thông Tin:\n§e§lChiếc Cúp Được Rèn Từ\n§e§l§cMột Vị Thần tài Giỏi Đã Chiến Thắng §eCuộc Thời Chiến Tranh\n§e§l✦ §6Cậu Đã Triệu Hồi Ta?, Thế Cậu Đã sẵn Sàng Đối Đầu Chưa?\n\n§9§l↦ §bChủ Nhân: §a".$player."!";
		return $lore;
	}

	//Set Pickaxe Level
	public function setPickaxe(Item $item) : Item {
		$item->setNamedTagEntry(new StringTag("Pickaxe", self::KEY_VALUE));
		return $item;
	}

	//Check Pickaxe Level
	public function onCheck(Item $item) : bool{
		return $item->getNamedTag()->hasTag("Pickaxe", StringTag::class);
	}

    //getExp player
	public function getExp($player){
		if($player instanceof Player){
			$player = $player->getName();
			if(!$this->pic->exists($player)){
				$exp = 0;
				return $exp;
			} else{
				$exp = $this->pic->get($player)["Exp"];
				return $exp;
			}
		}
	}

	//getNextExp player
	public function getNextExp($player){
		if($player instanceof Player){
			$player = $player->getName();
			if(!$this->pic->exists($player)){
				$nexp = 0;
				return $nexp;
			} else{
				$nexp = $this->pic->get($player)["NextExp"];
				return $nexp;
			}
		}
	}

	//get Level player
	public function getLevel($player){
		if($player instanceof Player){
			$player = $player->getName();
			if(!$this->pic->exists($player)){
				$lv = 0;
				return $lv;
			} else{
				$lv = $this->pic->get($player)["Level"];
				return $lv;
			}		
		}
	}

	//addExp for player
	public function addExp($player, $xp){
		if($player instanceof Player){
			$player = $player->getName();
			$current = $this->pic->get($player)["Exp"];
			$currentlv = $this->pic->get($player)["Level"];
			$currentne = $this->pic->get($player)["NextExp"];
			$currentpopup = $this->pic->get($player)["Popup"];
			$this->pic->set(($player), [
				"Exp" => $current + $xp,
				"Level" => $currentlv,
				"NextExp" => $currentne,
				"Popup" => $currentpopup
			]);
			$this->pic->save();
		}
	}

	//set level next
	public function setLevel($player, $level){
		if($player instanceof Player){
			$name = $player->getName();
         $nextexp = ($this->getLevel($player)+1)*120;
         $currentpopup = $this->pic->get($player->getName())["Popup"];
          $this->pic->set(($name), ["Exp" => 0, "Level" => $level, "NextExp" => $nextexp, "Popup" => $currentpopup]);
          $this->pic->save();
      }
  }

  //add piggycustomenchants
	public function addCE(CommandSender $sender, $enchantment, $level, $target)
    {
        $plugin = $this->CE;
        if ($plugin instanceof CE) {
            if (!is_numeric($level)) {
                $level = 1;
                $sender->sendMessage("Level must be numerical. Setting level to 1.");
            }
            $target == null ? $target = $sender : $target = $this->getServer()->getPlayer($target);
            if (!$target instanceof Player) {
                if ($target instanceof ConsoleCommandSender) {
                    $sender->sendMessage("Please provide a player.");
                    return;
                }
                $sender->sendMessage("Invalid player.");
                return;
            }
            $target->getInventory()->setItemInHand($plugin->addEnchantment($target->getInventory()->getItemInHand(), $enchantment, $level, $sender->hasPermission("piggycustomenchants.overridecheck") ? false : true, $sender));
        }
    }
}
##------------------------------------[END]--------------------------------------------------
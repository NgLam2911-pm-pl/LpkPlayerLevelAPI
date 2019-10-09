<?php

namespace LamPocketVN\PlayerLevelAPI;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\block\Block;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;

use LamPocketVN\PlayerLevelAPI\UpgradeTask;


class PlayerLevelAPI extends PluginBase implements Listener
{
    public $data, $save, $msg, $setting;
	
	public static $instance;
	public static function getInstance(){
		return self::$instance;
	}
	public function onLoad(){
		
		self::$instance = $this;
	}
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		@mkdir($this->getDataFolder());
		$this->saveResource("PlayerData.yml");
		$this->PlayerData = new Config($this->getDataFolder() . "PlayerData.yml", Config::YAML);
		$this->data = $this->PlayerData->getAll();
		
		@mkdir($this->getDataFolder());
		$this->saveResource("SaveData.yml");
		$this->SaveData = new Config($this->getDataFolder() . "SaveData.yml", Config::YAML);
		$this->save = $this->SaveData->getAll();
		
		@mkdir($this->getDataFolder());
		$this->saveResource("LevelSetting.yml");
		$this->LevelSetting = new Config($this->getDataFolder() . "LevelSetting.yml", Config::YAML);
		$this->setting = $this->LevelSetting->getAll();
		
		@mkdir($this->getDataFolder());
		$this->saveResource("Message.yml");
		$this->Message = new Config($this->getDataFolder() . "Message.yml", Config::YAML);
		$this->msg = $this->Message->getAll();
	}
	public function onJoin(PlayerJoinEvent $e)
	{
		$task = new UpgradeTask($this, $e->getPlayer());
        $this->tasks[$e->getPlayer()->getId()] = $task;
        $this->getScheduler()->scheduleRepeatingTask($task, 20);

  
		$player = $e->getPlayer();
		$name = strtolower($player->getName());
    
		if(!isset($this->data[$name]["save"])){
    
		$this->data[$name]["save"] = "on";
		$this->data[$name]["exp"] = 0;
		$this->data[$name]["level"] = 1;
      
		$this->save1();
		}
	}
	public function getXp(Player $player){
    
		$name = strtolower($player->getName());
    
		if(!isset($this->data[$name]["exp"])){
    
		return 0;
		}
		else{
		
		return $this->data[$name]["exp"];
		}
	}
	public function getLevel(Player $player){
  
		$name = strtolower($player->getName());
    
		if(!isset($this->data[$name]["level"])){
		
		return 1;
		}
		else{
    
		return $this->data[$name]["level"];
		}
	}
	public function addXp(Player $player, $xp){
	
		$name = strtolower($player->getName());
		
		$level = $this->getLevel($player);
    
		if(isset($this->save["maxlevel"])){
    
			if(isset($this->save[$level])){
      	
				if(is_numeric($this->save["maxlevel"]) and is_numeric($this->save[$level])){
        
					if($this->save["maxlevel"] === $level){
          
						$this->setXp($player, 0);
					}
					else{
				
					$this->data[$name]["exp"] = $this->data[$name]["exp"] + $xp;
					$this->save1();
					}
				}
			}
		}
	}
	public function addLevel(Player $player, $lv){
  
		$name = strtolower($player->getName());
    
		$this->data[$name]["level"] = $this->data[$name]["level"] + $lv;
		$this->save1();
	}
	public function setXp(Player $player, $xp){
  
		$name = strtolower($player->getName());
    
		$this->data[$name]["exp"] = $xp;
		$this->save1();
	}
	public function setLevel(Player $player, $lv){
  
		$name = strtolower($player->getName());
    
		$this->data[$name]["level"] = $lv;
		$this->save1();
	}
	public function getMaxLevel(){
  
		if(isset($this->save["maxlevel"])){
    
		return $this->save["maxlevel"];
		}
		else{
    
		return "Error";
	}
  }
	public function getMaxXp(Player $player){
  
		$level = $this->getLevel($player);
    
		if(isset($this->save[$level])){
    
		return $this->save[$level];
		}
		else{
    
		return "Error";
		}
	}
	public function Upgrade(Player $player){
  
		$name = strtolower($player->getName());
		$level = $this->getLevel($player);
		$point = $this->getXp($player);
    
		if(isset($this->save["maxlevel"])){
    
			if(isset($this->save[$level])){
      	
				if(is_numeric($this->save["maxlevel"]) and is_numeric($this->save[$level])){
        
					if($point >= $this->save[$level]){
        
						$this->addLevel($player, 1);
						$this->setXp($player, 0);
          
						$level2 = $this->getLevel($player);
          
						$player->sendMessage("§l§e! Bạn đã nân cấp thành công! §e!", "§l§fLv.".$level." → Lv.".$level2);
            
					}
				}
			}
		}
	}
	public function save1(){
  
		$this->PlayerData->setAll($this->data); 
		$this->PlayerData->save();
	}
	public function save2(){
  
		$this->SaveData->setAll($this->save); 
		$this->SaveData->save();
	}
	public function onDisable(){
  	
		$this->save1();
		$this->save2();
	}
	public function onBlockBreak(BlockBreakEvent $ev)
	{
		$bl = $ev->getBlock();
		$player = $ev->getPlayer();
		$level = $this->getLevel($player);
		if (in_array($bl->getId(), $this->setting[$level]["can-break"]) === false)
		{
			$player->sendMessage($this->msg["cannot-break"]);
			$ev->setCancelled();
		}
	return;
	}
	
	public function onCraft (CraftItemEvent $ev)
	{
		$items = $ev->getOutputs();
		$player = $ev->getPlayer();
		$level = $this->getLevel($player);
		foreach ($items as $item)
		{
			if (in_array($item, $this->setting[$level]["can-craft"]) === false)
			{	
				$player->sendMessage($this->msg["cannot-craft"]);
				$ev->setCancelled();
			}
		}
		return;
	}
}
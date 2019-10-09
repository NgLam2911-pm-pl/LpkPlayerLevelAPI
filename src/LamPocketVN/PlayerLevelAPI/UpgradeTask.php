<?php

namespace LamPocketVN\PlayerLevelAPI;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\Player;
use LamPocketVN\PlayerLevelAPI\PlayerLevelAPI;

Class UpgradeTask extends Task{


    public function __construct(PlayerLevelAPI $plugin, Player $player){

        $this->plugin = $plugin;
        $this->player = $player;
    }

    public function onRun($currentTick){
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) 
		{
		    if ($this->plugin->getLevel($player) < $this->plugin->getMaxLevel())
			{
				if ($this->plugin->getXp($player) > $this->plugin->getMaxXp($player))
				{
					$this->plugin->Upgrade($player);
				}
			}
        }
    }



}
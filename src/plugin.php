<?php

declare(strict_types=1);

namespace nicholass003\outpost;

use nicholass003\outpost\area\AreaManager;
use nicholass003\outpost\command\OutpostCommand;
use pocketmine\command\SimpleCommandMap;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

final class Outpost extends PluginBase{
	use SingletonTrait;

	private Config $outpost;

	protected function onLoad() : void{
		$this->saveDefaultConfig();
	}

	protected function onEnable() : void{
		$this->outpost = new Config($this->getDataFolder() . "outposts.yml", Config::YAML);

		$this->registerCommands($this->getServer()->getCommandMap());

		AreaManager::getInstance()->load();

		$this->getScheduler()->scheduleRepeatingTask(new CaptureTask(), 20);
	}

	private function registerCommands(SimpleCommandMap $commandMap) : void{
		$commandMap->register("outpost", new OutpostCommand($this, "outpost", "Outpost Commands"));
	}

	protected function onDisable() : void{
		AreaManager::getInstance()->save();
	}

	public function getOutpostConfig() : Config{
		return $this->outpost;
	}
}

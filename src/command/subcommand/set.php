<?php

declare(strict_types=1);

namespace nicholass003\outpost\command\subcommand;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use nicholass003\outpost\area\Area;
use nicholass003\outpost\area\AreaManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SetOutpostSubCommand extends BaseSubCommand{

	protected function prepare() : void{
		$this->setPermission("outpost.command.set");

		$this->registerArgument(0, new IntegerArgument("radius", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void{
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED . "You must be logged in to use this command.");
			return;
		}

		$radius = 5;
		if(isset($args["radius"])){
			$radius = $args["radius"];
		}

		$area = new Area($sender->getPosition()->floor(), (int) $radius, $sender->getInventory()->getContents());
		AreaManager::getInstance()->add($area);

		$sender->sendMessage(TextFormat::GREEN . "Success add Area Outpost with id: " . $area->getId());
	}
}

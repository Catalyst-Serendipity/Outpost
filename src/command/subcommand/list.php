<?php

declare(strict_types=1);

namespace nicholass003\outpost\command\subcommand;

use CortexPE\Commando\BaseSubCommand;
use nicholass003\outpost\area\AreaManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function count;

class ListOutpostSubCommand extends BaseSubCommand{

	protected function prepare() : void{
		$this->setPermission("outpost.command.list");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void{
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED . "You must be logged in to use this command.");
			return;
		}

		if(count(AreaManager::getInstance()->getAll()) === 0){
			$sender->sendMessage(TextFormat::RED . "No Outpost found.");
			return;
		}

		$sender->sendMessage(TextFormat::GREEN . "Outpost Lists:");
		foreach(AreaManager::getInstance()->getAll() as $id => $area){
			$sender->sendMessage(TextFormat::YELLOW . "- ({$id})" . $area->getPosition()->__toString());
		}
	}
}

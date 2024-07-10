<?php

declare(strict_types=1);

namespace nicholass003\outpost\command\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use nicholass003\outpost\area\AreaManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function count;

class RemoveOutpostSubCommand extends BaseSubCommand{

	protected function prepare() : void{
		$this->setPermission("outpost.command.remove");

		$this->registerArgument(0, new RawStringArgument("id"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void{
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED . "You must be logged in to use this command.");
			return;
		}

		if(!isset($args["id"])){
			$sender->sendMessage(TextFormat::RED . "Usage: /{$aliasUsed} <id: string|all>");
			return;
		}

		if($args["id"] === "all"){
			if(count(AreaManager::getInstance()->getAll()) === 0){
				$sender->sendMessage(TextFormat::RED . "Failed to remove, no Outpost set.");
				return;
			}
			foreach(AreaManager::getInstance()->getAll() as $id => $area){
				AreaManager::getInstance()->remove($id);
				$area->destroy();
			}
			$sender->sendMessage(TextFormat::GREEN . "Success remove all Outpost.");
			return;
		}

		$area = AreaManager::getInstance()->get($args["id"]);
		if($area === null){
			$sender->sendMessage(TextFormat::RED . "Failed to remove, no Outpost with id:" . $args["id"]);
			return;
		}
		AreaManager::getInstance()->remove($area->getId());
		$area->destroy();
		$sender->sendMessage(TextFormat::GREEN . "Success remove Outpost with id:" . $args["id"]);
	}
}

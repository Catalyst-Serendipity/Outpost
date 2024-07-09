<?php

declare(strict_types=1);

namespace nicholass003\outpost\command;

use CortexPE\Commando\BaseCommand;
use nicholass003\outpost\command\subcommand\ListOutpostSubCommand;
use nicholass003\outpost\command\subcommand\SetOutpostSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class OutpostCommand extends BaseCommand{

	protected function prepare() : void{
		$this->setPermission("outpost.command");

		$this->registerSubCommand(new ListOutpostSubCommand($this->plugin, "list", "Show Outpost Area List"));
		$this->registerSubCommand(new SetOutpostSubCommand($this->plugin, "set", "Set Outpost Area"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void{
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED . "You must be logged in to use this command.");
			return;
		}

		$sender->sendMessage(TextFormat::RED . "Usage: /{$aliasUsed} <list|set>");
	}
}

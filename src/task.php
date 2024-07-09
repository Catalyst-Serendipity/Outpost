<?php

declare(strict_types=1);

namespace nicholass003\outpost;

use nicholass003\outpost\area\AreaManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function time;

final class CaptureTask extends Task{

	public function onRun() : void{
		foreach(AreaManager::getInstance()->getAll() as $id => $area){
			foreach(Server::getInstance()->getOnlinePlayers() as $player){
				if(!$player->isConnected() && !$player->spawned){
					continue;
				}
				if(!$area->insideArea($player)){
					continue;
				}
				if(!$area->isCaptured() && $area->getOwner() === null){
					$currentTime = time();
					$captureTime = Outpost::getInstance()->getConfig()->get("capture-time", 30);
					$diff = time() + $captureTime - $currentTime;
					if($diff <= 0){
						$area->setCaptured();
						$area->setOwner($player);
						$player->sendTip(TextFormat::GREEN . "Succes take Outpost!");
						return;
					}
					//TODO: better floating text
					$area->getPosition()->getWorld()->addParticle($area->getPosition(), $area->getText((Outpost::getInstance()->getConfig()->get("capture-floatingtext", "Capturing in {time}s"))));
				}
			}
		}
	}
}

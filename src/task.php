<?php

declare(strict_types=1);

namespace nicholass003\outpost;

use nicholass003\outpost\area\Area;
use nicholass003\outpost\area\AreaManager;
use nicholass003\outpost\text\FloatingText;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use function array_key_exists;
use function count;
use function str_replace;
use function time;

final class CaptureTask extends Task{

	public function onRun() : void{
		$config = Outpost::getInstance()->getConfig();
		foreach(AreaManager::getInstance()->getAll() as $id => $area){
			$area->tick();
			foreach(Server::getInstance()->getOnlinePlayers() as $player){
				if(!$player->isConnected() && !$player->spawned){
					continue;
				}
				if(!$area->insideArea($player)){
					if(array_key_exists((int) $player->getXuid(), $area->getPlayersInside())){
						$area->removePlayersInside($player);
						$area->setCapturing(false);
					}
					if(!$area->isCapturing()){
						if($area->getInsideArea() !== $player){
							$area->setInsideArea(null);
							if($area->getCaptureTime() !== null){
								$area->setCaptureTime(null);
							}
						}
					}
					continue;
				}
				$area->addPlayersInside($player);
				if(!$area->isCaptured() && $area->getOwner() === null && count($area->getPlayersInside()) === 1){
					if($area->getInsideArea() === null){
						$area->setInsideArea($player);
					}
					if($area->getInsideArea()->getXuid() !== $player->getXuid()){
						$area->setInsideArea(null);
						$area->setCaptureTime(null);
						return;
					}
					if($area->getCaptureTime() === null){
						$area->setCaptureTime(time());
					}
					$currentTime = time();
					$captureTime = $config->get("capture-time", 30);
					$diff = ($area->getCaptureTime() + $captureTime) - $currentTime;
					$area->updateTexts(Area::TAG_CAPTURE,
						str_replace(
							["{capture-time}", "{player}"],
							[(string) $diff, $area->getOwner() instanceof Player ? $area->getOwner()->getName() : "None"],
							$config->get("ft-text", "§cCapture Time §8: §7{capture-time}\n§cCaptured By §8: §7{player}")
						),
						FloatingText::TEXT_ADD
					);
					$area->setCapturing();
					if($diff <= 0){
						$area->setCaptured();
						$area->setOwner($player);
						$area->updateTexts(Area::TAG_CAPTURE,
							str_replace(
								["{capture-time}", "{player}"],
								[(string) $diff, $area->getOwner() instanceof Player ? $area->getOwner()->getName() : "None"],
								$config->get("ft-text", "§cCapture Time §8: §7{capture-time}\n§cCaptured By §8: §7{player}")
							),
							FloatingText::TEXT_ADD
						);
						Server::getInstance()->broadcastMessage(str_replace("{player}", $player->getName(), $config->get("broadcast-message", "§6{player} just control the outpost!")));
						$player->sendTip($config->get("tip-message", "§aSuccess control the outpost!"));
						$area->setCapturing(false);
						return;
					}
				}
				if($area->isCaptured()){
					if($area->getOwner() !== $player){
						$area->setOwner(null);
						$area->setCaptured(false);
					}
				}
			}
		}
	}
}

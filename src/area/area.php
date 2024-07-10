<?php

declare(strict_types=1);

namespace nicholass003\outpost\area;

use nicholass003\outpost\Outpost;
use nicholass003\outpost\text\FloatingText;
use nicholass003\outpost\utils\ItemsHelper;
use pocketmine\player\Player;
use pocketmine\world\Position;
use function base64_encode;
use function bin2hex;
use function count;
use function json_encode;
use function random_bytes;
use function str_replace;

class Area{

	public const TAG_IDLE = "idle";
	public const TAG_CAPTURE = "capture";
	public const TAG_CAPTURING = "capturing";
	public const TAG_REWARD = "reward";

	private int $time = 0;
	private ?int $captureTime = null;

	private string $id;
	private bool $captured = false;
	private bool $capturing = false;
	private ?Player $owner = null;
	private ?FloatingText $floatingText = null;
	private ?Player $player = null;

	private array $playersInside = [];
	private array $texts = [];

	public function __construct(
		private Position $position,
		private float|int $radius,
		private array $rewards,
		bool $fromLoad = false
	){
		if($fromLoad === false){
			$this->id = bin2hex(random_bytes(4));
			$this->floatingText = new FloatingText($position, $this->getTexts(self::TAG_IDLE));
		}
		$this->updateTexts(self::TAG_IDLE, Outpost::getInstance()->getConfig()->get("ft-text-idle", "§bOutpost Ready to Capture"), FloatingText::TEXT_ADD);
	}

	public function getId() : string{
		return $this->id;
	}

	/**
	 * This just for internal use, do not use this.
	 */
	public function setId(string $id) : void{
		$this->id = $id;
	}

	public function getPosition() : Position{
		return $this->position;
	}

	public function getRadius() : float|int{
		return $this->radius;
	}

	public function getPlayersInside() : array{
		return $this->playersInside;
	}

	public function addPlayersInside(Player $player) : void{
		$xuid = (int) $player->getXuid();
		if(!isset($this->playersInside[$xuid])){
			$this->playersInside[$xuid] = true;
		}
	}

	public function removePlayersInside(Player $player) : void{
		$xuid = (int) $player->getXuid();
		if(isset($this->playersInside[$xuid])){
			unset($this->playersInside[$xuid]);
		}
	}

	public function getRewards() : array{
		return $this->rewards;
	}

	public function setRewards(array $contents = []) : void{
		$this->rewards = $contents;
	}

	public function insideArea(Player $player) : bool{
		return $player->getPosition()->distance($this->position) <= $this->radius;
	}

	public function getInsideArea() : ?Player{
		return $this->player;
	}

	public function setInsideArea(?Player $player = null) : void{
		$this->player = $player;
	}

	public function isCapturing() : bool{
		return $this->capturing === true;
	}

	public function setCapturing(bool $value = true) : void{
		$this->capturing = $value;
	}

	public function isCaptured() : bool{
		return $this->captured === true;
	}

	public function setCaptured(bool $value = true) : void{
		$this->captured = $value;
	}

	public function getOwner() : ?Player{
		return $this->owner;
	}

	public function setOwner(?Player $player = null) : void{
		$this->owner = $player;
	}

	public function setRewardTime(int $time) : void{
		$this->time = $time;
	}

	public function getCaptureTime() : ?int{
		return $this->captureTime;
	}

	public function setCaptureTime(?int $captureTime = null) : void{
		$this->captureTime = $captureTime;
	}

	public function getTexts(string $key) : string{
		return isset($this->texts[$key]) ? $this->texts[$key] : "";
	}

	public function updateTexts(string $key, string $value, int $action) : void{
		switch($action){
			case FloatingText::TEXT_ADD:
				if($key === self::TAG_CAPTURE || $key === self::TAG_CAPTURING || $key === self::TAG_IDLE){
					$value .= "\n";
				}
				$this->texts[$key] = $value;
				break;
			case FloatingText::TEXT_REMOVE:
				if(isset($this->texts[$key])){
					unset($this->texts[$key]);
				}
				break;
			default:
				throw new \InvalidArgumentException("Unknown FloatingText action");
		}
		if($this->floatingText !== null){
			$this->floatingText->updateText($this->getTexts(self::TAG_IDLE) . $this->getTexts(self::TAG_CAPTURING) . $this->getTexts(self::TAG_CAPTURE) . $this->getTexts(self::TAG_REWARD));
		}
	}

	public function getFloatingText() : ?FloatingText{
		return $this->floatingText;
	}

	public function spawn() : void{
		$world = $this->position->getWorld();
		$chunk = $world->getOrLoadChunkAtPosition($this->position);
		if($chunk !== null){
			$this->floatingText = new FloatingText($this->position, $this->getTexts(self::TAG_IDLE));
			$world->addParticle($this->position, $this->floatingText, $world->getPlayers());
		}
	}

	public function destroy() : void{
		if($this->floatingText !== null){
			$this->floatingText->destroy();
		}
	}

	public function tick() : void{
		if($this->floatingText === null){
			$this->spawn();
			return;
		}
		if(count($this->position->getWorld()->getPlayers()) === 0){
			//disable update when no player in the world
			return;
		}
		$this->floatingText->updateTitle(Outpost::getInstance()->getConfig()->get("ft-title", "§l§4Outpost"));
		if($this->captured === true){
			--$this->time;
			$this->updateTexts(self::TAG_CAPTURING, "", FloatingText::TEXT_REMOVE);
			$this->updateTexts(self::TAG_IDLE, "", FloatingText::TEXT_REMOVE);
			$this->updateTexts(self::TAG_REWARD, str_replace("{reward-time}", (string) $this->time, Outpost::getInstance()->getConfig()->get("ft-reward", "Reward drop in {reward-time}")), FloatingText::TEXT_ADD);
			if($this->time <= 0){
				foreach($this->rewards as $slot => $item){
					$this->position->getWorld()->dropItem($this->position, $item);
				}
				$this->time = (int) Outpost::getInstance()->getConfig()->get("reward-time", 5 * 60);
			}
		}else{
			if($this->player === null && $this->capturing === false){
				$this->updateTexts(self::TAG_CAPTURE, "", FloatingText::TEXT_REMOVE);
				$this->updateTexts(self::TAG_CAPTURING, "", FloatingText::TEXT_REMOVE);
				$this->updateTexts(self::TAG_REWARD, "", FloatingText::TEXT_REMOVE);
				$this->updateTexts(self::TAG_IDLE, Outpost::getInstance()->getConfig()->get("ft-text-idle", "§bReady to Capture"), FloatingText::TEXT_ADD);
			}
			if($this->capturing === true && $this->player !== null){
				$this->updateTexts(self::TAG_IDLE, "", FloatingText::TEXT_REMOVE);
				$this->updateTexts(self::TAG_CAPTURING, str_replace("{player}", $this->player->getName(), Outpost::getInstance()->getConfig()->get("ft-text-capturing", "§eCapturing by {player}")), FloatingText::TEXT_ADD);
			}
		}
	}

	public function toJSON() : string{
		return json_encode([
			"id" => $this->id,
			"radius" => $this->radius,
			"world" => $this->position->getWorld()->getFolderName(),
			"pos" => [
				"x" => $this->position->getX(),
				"y" => $this->position->getFloorY(),
				"z" => $this->position->getZ()
			],
			"rewards" => base64_encode(ItemsHelper::write($this->rewards))
		]);
	}
}

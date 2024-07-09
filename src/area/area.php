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
use function json_encode;
use function random_bytes;
use function str_replace;

class Area{

	private int $time = 0;

	private string $id;
	private bool $captured = false;
	private ?Player $owner = null;
	private ?FloatingText $text = null;

	public function __construct(
		private Position $position,
		private float|int $radius,
		private array $rewards
	){
		$this->id = bin2hex(random_bytes(4));
		$this->text = new FloatingText($position, Outpost::getInstance()->getConfig()->get("capture-text", "Free"));
	}

	public function getId() : string{
		return $this->id;
	}

	public function getPosition() : Position{
		return $this->position;
	}

	public function getRadius() : float|int{
		return $this->radius;
	}

	public function getRewards() : array{
		return $this->rewards;
	}

	public function insideArea(Player $player) : bool{
		return $player->getPosition()->distance($this->position) <= $this->radius;
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

	public function setOwner(Player $player) : void{
		$this->owner = $player;
	}

	public function getText() : ?FloatingText{
		return $this->text;
	}

	public function destroy() : void{
		$this->text->destroy();
	}

	public function tick() : void{
		--$this->time;
		$this->text->setText(str_replace("{time}", (string) $this->time, Outpost::getInstance()->getConfig()->get("reward-floatingtext", "Reward drop in {time}s")));
		$this->position->getWorld()->addParticle($this->position, $this->text);
		if($this->time <= 0){
			foreach($this->rewards as $slot => $item){
				$this->position->getWorld()->dropItem($this->position, $item);
			}
			$this->time = (int) Outpost::getInstance()->getConfig()->get("reward-time", 5 * 60);
		}
	}

	public function toJSON() : string{
		return json_encode([
			"id" => $this->id,
			"radius" => $this->radius,
			"world" => $this->position->getWorld()->getFolderName(),
			"pos" => [
				"x" => $this->position->getFloorX(),
				"y" => $this->position->getFloorY(),
				"z" => $this->position->getFloorZ()
			],
			"rewards" => base64_encode(ItemsHelper::write($this->rewards))
		]);
	}
}

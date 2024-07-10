<?php

declare(strict_types=1);

namespace nicholass003\outpost\text;

use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\world\Position;

class FloatingText extends FloatingTextParticle{

	public const TEXT_ADD = 0;
	public const TEXT_REMOVE = 1;

	public function __construct(
		private Position $position,
		string $text = "Unknown",
		string $title = ""
	){
		parent::__construct($text, $title);
	}

	public function getPosition() : Position{
		return $this->position;
	}

	public function updateText(string $text) : void{
		$this->setText($text);
		$this->update();
	}

	public function updateTitle(string $title) : void{
		$this->setTitle($title);
		$this->update();
	}

	public function update() : void{
		$this->position->getWorld()->addParticle($this->position, $this, $this->position->getWorld()->getPlayers());
	}

	public function destroy() : void{
		$this->setInvisible();
		$this->update();
	}
}

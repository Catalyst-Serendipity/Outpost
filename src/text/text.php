<?php

declare(strict_types=1);

namespace nicholass003\outpost\text;

use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\world\Position;

class FloatingText extends FloatingTextParticle{

    public function __construct(
        private Position $position,
        string $text,
        string $title = ""
    ){
        parent::__construct($text, $title);
    }

    public function update() : void{
        $this->position->getWorld()->addParticle($this->position, $this);
    }

    public function destroy() : void{
        $this->setInvisible();
        
    }
}
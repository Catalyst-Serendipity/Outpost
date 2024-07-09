<?php

declare(strict_types=1);

namespace nicholass003\outpost\area;

use nicholass003\outpost\Outpost;
use nicholass003\outpost\utils\ItemsHelper;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;
use function base64_decode;

class AreaManager{
	use SingletonTrait;

	/** @var array<string, Area> */
	private array $areas = [];

	private function __construct(){}

	public function load() : void{
		$config = Outpost::getInstance()->getOutpostConfig();
		foreach($config->getAll() as $id => $data){
			$this->areas[$id] = new Area(Position::fromObject(
				new Vector3((int) $data["pos"]["x"], (int) $data["pos"]["x"], (int) $data["pos"]["x"]),
				Server::getInstance()->getWorldManager()->getWorldByName($data["world"])), $data["radius"], ItemsHelper::read(base64_decode($data["rewards"], true)));
		}
	}

	public function add(Area $area) : void{
		$this->areas[$area->getId()] = $area;
	}

	public function remove(string $id) : void{
		if(isset($this->areas[$id])){
			unset($this->areas[$id]);
		}
	}

	/**
	 * @return array<string, Area>
	 */
	public function getAll() : array{
		return $this->areas;
	}

	public function get(string $id) : ?Area{
		return $this->areas[$id] ?? null;
	}

	public function save() : void{
		$config = Outpost::getInstance()->getOutpostConfig();
		foreach($this->areas as $id => $area){
			$config->set($id, $area->toJSON());
		}
		$config->save();
	}
}

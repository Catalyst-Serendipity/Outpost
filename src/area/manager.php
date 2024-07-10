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
use function json_decode;

class AreaManager{
	use SingletonTrait;

	/** @var array<string, Area> */
	private array $areas = [];

	private function __construct(){}

	public function load() : void{
		$config = Outpost::getInstance()->getOutpostConfig();
		$worldManager = Server::getInstance()->getWorldManager();
		foreach($config->getAll() as $id => $data){
			$data = json_decode($data, true);
			$area = new Area(Position::fromObject(
				new Vector3((float) $data["pos"]["x"], (int) $data["pos"]["y"], (float) $data["pos"]["z"]),
				$worldManager->getWorldByName($data["world"])), (float) $data["radius"], ItemsHelper::read(base64_decode($data["rewards"], true)),
				true
			);
			$this->areas[$id] = $area;
			if(!$area->getPosition()->isValid()){
				if($worldManager->loadWorld($data["world"])){
					Outpost::getInstance()->getLogger()->notice("\"" . $data["world"] . "\" world is now Loaded.");
					$area->setPosition(Position::fromObject($area->getPosition()->asVector3(), $worldManager->getWorldByName($data["world"])));
				}
			}
			$area->setId($id);
			$area->spawn();
		}
	}

	public function add(Area $area) : void{
		$this->areas[$area->getId()] = $area;
	}

	public function remove(string|int $id) : void{
		if(isset($this->areas[$id])){
			$config = Outpost::getInstance()->getOutpostConfig();
			$config->remove($id);
			$config->save();
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
			$area->destroy();
			$config->set($id, $area->toJSON());
		}
		$config->save();
	}
}

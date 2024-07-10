<?php

declare(strict_types=1);

namespace nicholass003\outpost\utils;

use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use function zlib_decode;
use function zlib_encode;
use const ZLIB_ENCODING_GZIP;

class ItemsHelper{

	public const TAG_INVENTORY = "Inventory";

	public static function read(string $data) : array{
		$contents = [];
		$invTag = (new BigEndianNbtSerializer())->read(zlib_decode($data))->mustGetCompoundTag()->getListTag(self::TAG_INVENTORY);
		/** @var CompoundTag $tag */
		foreach($invTag as $tag){
			$contents[$tag->getByte("Slot")] = Item::nbtDeserialize($tag);
		}
		return $contents;
	}

	public static function write(array $items) : string{
		$contents = [];
		foreach($items as $slot => $item){
			$contents[] = $item->nbtSerialize($slot);
		}
		return zlib_encode((new BigEndianNbtSerializer())->write(new TreeRoot(CompoundTag::create()
				->setTag(self::TAG_INVENTORY, new ListTag($contents, NBT::TAG_Compound)))), ZLIB_ENCODING_GZIP);
	}
}

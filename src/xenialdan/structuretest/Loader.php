<?php

/*
 * structuretest
 * A plugin by XenialDan aka thebigsmileXD
 * http://github.com/thebigsmileXD/structuretest
 * Demonstration of libstructure
 */

namespace xenialdan\structuretest;

use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\plugin\PluginBase;
use xenialdan\libstructure\PacketListener;
use xenialdan\libstructure\tile\StructureBlockTags;
use xenialdan\libstructure\window\StructureBlockInventory;

class Loader extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        PacketListener::register($this);
    }

    public function onJoin(PlayerJoinEvent $e)
    {
        for ($data = StructureBlockTags::TAG_DATA_EXPORT; $data <= StructureBlockTags::TAG_DATA_EXPORT; $data++) {

            $player = $e->getPlayer();

            $pos = $player->asVector3()->floor();
            $pos = $pos->add($data, 1);

            $updateBlockPacket = new UpdateBlockPacket;
            $updateBlockPacket->x = $pos->x;
            $updateBlockPacket->y = $pos->y;
            $updateBlockPacket->z = $pos->z;
            $updateBlockPacket->blockRuntimeId = RuntimeBlockMapping::toStaticRuntimeId(Block::STRUCTURE_BLOCK, $data);
            $updateBlockPacket->flags = UpdateBlockPacket::FLAG_NONE;;
            $player->sendDataPacket($updateBlockPacket);
            $player->getLevel()->setBlockIdAt($pos->x, $pos->y, $pos->z, Block::STRUCTURE_BLOCK);
            $player->getLevel()->setBlockDataAt($pos->x, $pos->y, $pos->z, $data);

            $v1 = new Vector3(19, -1, 19);
            $v2 = new Vector3(-19, 21, -19);

            $min = new Vector3(min($v1->getFloorX(), $v2->getFloorX()), min($v1->getFloorY(), $v2->getFloorY()), min($v1->getFloorZ(), $v2->getFloorZ()));
            $max = new Vector3(max($v1->getFloorX(), $v2->getFloorX()), max($v1->getFloorY(), $v2->getFloorY()), max($v1->getFloorZ(), $v2->getFloorZ()));

            $blockActorDataPacket = new BlockActorDataPacket;
            $blockActorDataPacket->x = $pos->x;
            $blockActorDataPacket->y = $pos->y;
            $blockActorDataPacket->z = $pos->z;
            $nbtWriter = new NetworkLittleEndianNBTStream();
            $nbt = new CompoundTag("", [
                new IntTag("data", $data),
                new StringTag("dataField", ""),
                new StringTag("id", "StructureBlock"),
                new ByteTag("ignoreEntities", 0),
                new ByteTag("includePlayers", 0),
                new FloatTag("integrity", 100.0),
                new ByteTag("isMovable", 1),
                new ByteTag("isPowered", 0),
                new ByteTag("mirror", 0),
                new ByteTag("removeBlocks", 0),
                new ByteTag("rotation", 0),
                new LongTag("seed", 0),
                new ByteTag("showBoundingBox", 1),
                new StringTag("structureName", ""),
                new IntTag("x", $pos->getFloorX()),
                new IntTag("xStructureOffset", $min->x),
                new IntTag("xStructureSize", $max->x - $min->x + 1),
                new IntTag("y", $pos->getFloorY()),
                new IntTag("yStructureOffset", $min->y),
                new IntTag("yStructureSize", $max->y - $min->y + 1),
                new IntTag("z", $pos->getFloorZ()),
                new IntTag("zStructureOffset", $min->z),
                new IntTag("zStructureSize", $max->z - $min->z + 1)
            ]);
            $blockActorDataPacket->namedtag = $nbtWriter->write($nbt);
            $player->sendDataPacket($blockActorDataPacket);
        }
    }

    public function onClick(PlayerInteractEvent $e)
    {
        if ($e->getBlock()->getId() === Block::STRUCTURE_BLOCK) {
            $player = $e->getPlayer();
            var_dump($e->getBlock()->asVector3());
            $player->addWindow(new StructureBlockInventory($e->getBlock()->asPosition()));
        }
    }
}
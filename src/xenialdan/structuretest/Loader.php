<?php

/*
 * structuretest
 * A plugin by XenialDan aka thebigsmileXD
 * http://github.com/thebigsmileXD/structuretest
 * Demonstration of libstructure
 */

namespace xenialdan\structuretest;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use xenialdan\libstructure\PacketListener;
use xenialdan\libstructure\StructureUI;
use xenialdan\MagicWE2\exception\SessionException;
use xenialdan\MagicWE2\helper\SessionHelper;

class Loader extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        PacketListener::register($this);
        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
    }

    public function onSneak(PlayerToggleSneakEvent $e)
    {
        if ($e->isSneaking()) return;
        try {
            $session = SessionHelper::getUserSession($e->getPlayer());
            if (is_null($session)) return;
            $selection = $session->getLatestSelection();
            if (is_null($selection)) return;
            try {
                $shape = $selection->getShape();
            } catch (\Exception $ex) {
                $e->getPlayer()->sendMessage($ex->getMessage());
                return;
            }
            $aabb = $shape->getAABB();
            $min = new Vector3($aabb->minX, $aabb->minY, $aabb->minZ);
            $max = new Vector3($aabb->maxX, $aabb->maxY, $aabb->maxZ);
            $name = basename(get_class($shape));
            $menu = InvMenu::create(StructureUI::class, StructureUI::getMinV3($min, $max), StructureUI::getMaxV3($min, $max), $name);
            $menu->send($e->getPlayer());
        } catch (SessionException $e) {
        }
    }
}
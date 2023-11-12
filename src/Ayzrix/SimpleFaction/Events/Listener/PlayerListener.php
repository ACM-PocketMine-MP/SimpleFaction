<?php

/***
 *       _____ _                 _      ______         _   _
 *      / ____(_)               | |    |  ____|       | | (_)
 *     | (___  _ _ __ ___  _ __ | | ___| |__ __ _  ___| |_ _  ___  _ __
 *      \___ \| | '_ ` _ \| '_ \| |/ _ \  __/ _` |/ __| __| |/ _ \| '_ \
 *      ____) | | | | | | | |_) | |  __/ | | (_| | (__| |_| | (_) | | | |
 *     |_____/|_|_| |_| |_| .__/|_|\___|_|  \__,_|\___|\__|_|\___/|_| |_|
 *                        | |
 *                        |_|
 */

namespace Ayzrix\SimpleFaction\Events\Listener;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use pocketmine\world\format\Chunk;

class PlayerListener implements Listener {

    public function PlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        if (!FactionsAPI::hasLanguages($player)) {
            FactionsAPI::setLanguages($player, Utils::getIntoLang("default-language"));
        }
    }

    public function PlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            $cause = $player->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
                $damager = $cause->getDamager();
                if ($damager instanceof Player) {
                    if (FactionsAPI::isInFaction($damager->getName())) {
                        $dFaction = FactionsAPI::getFaction($damager->getName());
                        FactionsAPI::addPower($dFaction, (int)Utils::getIntoConfig("power_gain_per_kill"));
                        if (FactionsAPI::isInFaction($player->getName())) {
                            $pFaction = FactionsAPI::getFaction($player->getName());
                            if (isset(FactionsAPI::$Wars[$dFaction]) and FactionsAPI::$Wars[$dFaction]["faction"] === $pFaction) {
                                FactionsAPI::$Wars[$dFaction]["kills"]++;
                            }
                        }
                    }
                }
            }

            if (FactionsAPI::isInFaction($player->getName())) {
                $pFaction = FactionsAPI::getFaction($player->getName());
                FactionsAPI::removePower($pFaction, (int)Utils::getIntoConfig("power_lost_per_death"));
            }
        }
    }

    public function PlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();
        if (in_array($player->getWorld()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
            $pos = $event->getBlock()->getPosition()->asVector3();
            $chunkX = $pos->getFloorX() >> Chunk::COORD_BIT_SIZE;
            $chunkZ = $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE;
            if (FactionsAPI::isInClaim($player->getWorld(), $chunkX, $chunkZ)) {
                switch($block->getTypeId()){
                    case BlockTypeIds::CHERRY_DOOR:
                    case BlockTypeIds::DARK_OAK_DOOR:
                    case BlockTypeIds::JUNGLE_DOOR:
                    case BlockTypeIds::WARPED_DOOR:
                    case BlockTypeIds::CRIMSON_DOOR:
                    case BlockTypeIds::MANGROVE_DOOR:
                    case BlockTypeIds::OAK_DOOR:
                    case BlockTypeIds::ACACIA_DOOR:
                    case BlockTypeIds::SPRUCE_DOOR:
                    case BlockTypeIds::BIRCH_DOOR:
                    case BlockTypeIds::IRON_DOOR:

                    case BlockTypeIds::OAK_FENCE:
                    case BlockTypeIds::OAK_FENCE_GATE:
                    case BlockTypeIds::ACACIA_FENCE_GATE:
                    case BlockTypeIds::BIRCH_FENCE_GATE:
                    case BlockTypeIds::DARK_OAK_FENCE_GATE:
                    case BlockTypeIds::SPRUCE_FENCE_GATE:
                    case BlockTypeIds::JUNGLE_FENCE_GATE:
                    case BlockTypeIds::CHERRY_FENCE:
                    case BlockTypeIds::WARPED_FENCE:
                    case BlockTypeIds::CRIMSON_FENCE:
                    case BlockTypeIds::MANGROVE_FENCE:

                    case BlockTypeIds::IRON_TRAPDOOR:
                    case BlockTypeIds::OAK_TRAPDOOR:
                    case BlockTypeIds::BIRCH_TRAPDOOR:
                    case BlockTypeIds::ACACIA_TRAPDOOR:
                    case BlockTypeIds::CHERRY_TRAPDOOR:
                    case BlockTypeIds::JUNGLE_TRAPDOOR:
                    case BlockTypeIds::SPRUCE_TRAPDOOR:
                    case BlockTypeIds::WARPED_TRAPDOOR:
                    case BlockTypeIds::DARK_OAK_TRAPDOOR:
                    case BlockTypeIds::MANGROVE_TRAPDOOR:

                    case BlockTypeIds::CHEST:
                    case BlockTypeIds::ENDER_CHEST:
                    case BlockTypeIds::TRAPPED_CHEST:
                    case BlockTypeIds::SHULKER_BOX:
                    case BlockTypeIds::BARREL:
                        if (FactionsAPI::isInFaction($player->getName())) {
                            $claimer = FactionsAPI::getFactionClaim($player->getWorld(), $chunkX, $chunkZ);
                            $faction = FactionsAPI::getFaction($player->getName());
                            if ($faction !== $claimer) $event->cancel();
                        } else $event->cancel();
                        break;
                }

                    switch ($item->getTypeId()) {
                        case ItemTypeIds::BUCKET:
                        case ItemTypeIds::LAVA_BUCKET:
                        case ItemTypeIds::WATER_BUCKET:

                        case ItemTypeIds::DIAMOND_HOE:
                        case ItemTypeIds::GOLDEN_HOE:
                        case ItemTypeIds::IRON_HOE:
                        case ItemTypeIds::STONE_HOE:
                        case ItemTypeIds::NETHERITE_HOE:	
                        case ItemTypeIds::WOODEN_HOE:

                        case ItemTypeIds::DIAMOND_SHOVEL:
                        case ItemTypeIds::GOLDEN_SHOVEL:
                        case ItemTypeIds::IRON_SHOVEL:
                        case ItemTypeIds::STONE_SHOVEL:
                        case ItemTypeIds::WOODEN_SHOVEL:
                        case ItemTypeIds::NETHERITE_SHOVEL:
                            
                        case ItemTypeIds::FLINT:
                            if (FactionsAPI::isInFaction($player->getName())) {
                                $claimer = FactionsAPI::getFactionClaim($player->getWorld(), $chunkX, $chunkZ);
                                $faction = FactionsAPI::getFaction($player->getName());
                                if ($faction !== $claimer) $event->cancel();
                            } else $event->cancel();
                            break;
                    }
            }
        }
    }

    public function PlayerChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        if (isset(FactionsAPI::$chat[$player->getName()])) {
            $chat = FactionsAPI::$chat[$player->getName()];
            switch ($chat) {
                case "FACTION":
                    $event->cancel();
                    FactionsAPI::factionMessage($player, $message);
                    break;
                case "ALLIANCE":
                    $event->cancel();
                    FactionsAPI::allyMessage($player, $message);
                    break;
            }
        }
    }
}
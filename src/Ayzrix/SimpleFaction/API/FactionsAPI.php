<?php

namespace Ayzrix\SimpleFaction\API;

use Ayzrix\SimpleFaction\Utils\MySQL;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\Player;
use pocketmine\Server;

class FactionsAPI {

    /**
     * @param Player $player
     * @return bool
     */
    public static function isInFaction(Player $player): bool {
        $name = $player->getName();
        $result = MySQL::getDatabase()->query("SELECT player FROM faction WHERE player='$name';");
        $array = $result->fetch_Array(MYSQLI_ASSOC);
        return empty($array) === false;
    }

    /**
     * @param $faction
     * @return bool
     */
    public static function existsFaction($faction): bool {
        $faction = strtolower($faction);
        $result = MySQL::getDatabase()->query("SELECT player FROM faction WHERE lower(faction)='$faction';");
        $array = $result->fetch_Array(MYSQLI_ASSOC);
        return empty($array) === false;
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function createFaction(Player $player, string $faction): void {
        $name = $player->getName();
        MySQL::query("INSERT INTO faction (player, faction, role) VALUES ('$name', '$faction', 'Leader')");
        MySQL::query("INSERT INTO power (faction, power) VALUES ('$faction', 0)");
        if (Utils::getIntoConfig("broadcast_message_created") === true) Server::getInstance()->broadcastMessage(Utils::getConfigMessage("FACTION_CREATE_BROADCAST", array($name, $faction)));
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function disbandFaction(Player $player, string $faction): void {
        $name = $player->getName();
        MySQL::query("DELETE FROM faction WHERE faction = '$faction'");
        MySQL::query("DELETE FROM power WHERE faction='$faction'");
        if (Utils::getIntoConfig("broadcast_message_disband") === true) Server::getInstance()->broadcastMessage(Utils::getConfigMessage("FACTION_DISBAND_BROADCAST", array($name, $faction)));
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getFaction(Player $player): string {
        $name = $player->getName();
        $faction = MySQL::getDatabase()->query("SELECT faction FROM faction WHERE player='$name';");
        $array = $faction->fetch_Array(MYSQLI_ASSOC);
        return $array["faction"]?? "";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getRank(string $name): string {
        $faction = MySQL::getDatabase()->query("SELECT role FROM faction WHERE player='$name';");
        $array = $faction->fetch_Array(MYSQLI_ASSOC);
        return $array["role"]?? "";
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getPower(string $faction): int {
        $return = MySQL::getDatabase()->query("SELECT power FROM power WHERE faction='$faction';");
        $array = $return->fetch_Array(MYSQLI_ASSOC);
        return $array["power"]?? 0;
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function addPower(string $faction, int $amount): void {
        MySQL::query("UPDATE power SET power = power + '$amount' WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function removePower(string $faction, int $amount): void {
       if(self::getPower($faction) - $amount <= 0) {
           self::setPower($faction, 0);
           return;
       }
       MySQL::query("UPDATE power SET power = power - '$amount' WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function setPower(string $faction, int $amount): void {
        MySQL::query("UPDATE power SET power = '$amount' WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @return array
     */
    public static function getAllPlayers(string $faction): array {
        $res = MySQL::getDatabase()->query("SELECT player FROM faction WHERE faction='$faction'");
        $return = [];
        while ($resultArr = $res->fetch_Array(MYSQLI_ASSOC)) {
            $return[] = $resultArr['player'];
        }
        return $return;
    }

    /**
     * @param string $faction
     * @return string
     */
    public static function getLeader(string $faction): string {
        $return = MySQL::getDatabase()->query("SELECT player FROM faction WHERE faction='$faction' AND role = 'Leader';");
        $array = $return->fetch_Array(MYSQLI_ASSOC);
        return $array['player'];
    }

    /**
     * @param string $faction
     * @return array
     */
    public static function getOfficers(string $faction): array {
        $array = [];
        foreach (self::getAllPlayers($faction) as $player) {
            if(self::getRank($player) === "Officer") {
                $array[] = $player;
            }
        }
        return $array;
    }

    /**
     * @param string $faction
     * @return array
     */
    public static function getMembers(string $faction): array {
        $array = [];
        foreach (self::getAllPlayers($faction) as $player) {
            if(self::getRank($player) === "Member") {
                $array[] = $player;
            }
        }
        return $array;
    }
}
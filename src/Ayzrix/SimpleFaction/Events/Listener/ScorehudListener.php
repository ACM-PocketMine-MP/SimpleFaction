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
use Ayzrix\SimpleFaction\Main;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;

use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class ScorehudListener implements Listener{

    /**
     * @param TagsResolveEvent $event
     * @return void
     */
    public function onTagResolve(TagsResolveEvent $event): void{
		$tag = $event->getTag();
		$tags = explode('.', $tag->getName(), 2);
		$value = "";
		if($tags[0] !== 'simplefaction' || count($tags) < 2){
			return;
		}
		switch($tags[1]){
			case "faction":
				$value = self::getPlayerFaction($event->getPlayer());
				break;

			case "power":
				$value = self::getFactionPower($event->getPlayer());
				break;
		}
		$tag->setValue(strval($value));
	}

    /**
     * @param Player $player
     * @return string
     */
    private static function getPlayerFaction(Player $player): string{
        if (FactionsAPI::isInFaction($player->getName())) {
            return FactionsAPI::getFaction($player->getName());
        } else return "N/F";
    }
    
    /**
     * @param Player $player
     * @return void
     */
    private static function getFactionPower(Player $player) {
        if(FactionsAPI::isInFaction($player->getName())) {
            return FactionsAPI::getPower(FactionsAPI::getFaction($player->getName()));
        } else return "N/P";
    }

    /**
     * @return void
     */
    private static function loadTags(): void{
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
			foreach(Server::getInstance()->getOnlinePlayers() as $player){
				if(!$player->isOnline()){
					continue;
				}

				(new PlayerTagUpdateEvent($player, new ScoreTag("simplefaction.faction", self::getPlayerFaction($player))))->call();
				(new PlayerTagUpdateEvent($player, new ScoreTag("simplefaction.power", (string) self::getFactionPower($player))))->call();
			}
		}), 20);
    }

    /**
     * @return void
     */
    public static function loadScorehud(): void{
        if (($scorehud = Server::getInstance()->getPluginManager()->getPlugin("ScoreHud")) !== null){
            $version = $scorehud->getDescription()->getVersion();
            if (version_compare($version, "6.0.0") === 1) {
                if (version_compare($version, "6.1.0") === -1) {
                    Main::getInstance()->getLogger()->warning("Outdated version of ScoreHud (v" . $version . ") detected, requires >= v6.1.0. Integration disabled.");
                    return;
                }
                Server::getInstance()->getPluginManager()->registerEvents(new ScorehudListener, Main::getInstance());
                self::loadTags();
            }
        }
    }
}
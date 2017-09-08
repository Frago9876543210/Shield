<?php

declare(strict_types=1);

namespace shield;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{

    private $count;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function handleLogin(DataPacketReceiveEvent $e)
    {
        if ($e->getPacket()::NETWORK_ID == ProtocolInfo::LOGIN_PACKET) {
            $ip = $e->getPlayer()->getAddress();
            if (!isset($this->count[$ip]))
                $this->count[$ip] = 1;
            else
                $this->count[$ip]++;

            if ($this->count[$ip] >= 3) {
                foreach ($this->getServer()->getOnlinePlayers() as $player) {
                    if ($player instanceof Player && $player->isOnline()) {
                        if ($player->getAddress() == $ip) {
                            $player->close();
                        }
                    }
                }
                $this->getServer()->getNetwork()->blockAddress($ip, PHP_INT_MAX);
                $this->getServer()->getIPBans()->addBan($ip);
            }
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $e)
    {
        $ip = $e->getPlayer()->getAddress();
        if (isset($this->count[$ip]))
            $this->count[$ip]--;
    }
}

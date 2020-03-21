<?php


namespace Nicodinus\UmeyeApi\Core\Network;


use Amp\Socket\Socket;

abstract class BasicClientSession implements ClientSessionInterface
{
    /**
     * @var ServerHandlerInterface
     */
    protected ServerHandlerInterface $serverHandler;

    /**
     * @var Socket
     */
    protected Socket $socket;

    /**
     * @var float
     */
    protected float $lastActivity = 0;

    /**
     * BasicClientSession constructor.
     * @param ServerHandlerInterface $serverHandler
     * @param Socket $socket
     */
    public function __construct(ServerHandlerInterface &$serverHandler, Socket &$socket)
    {
        $this->serverHandler = &$serverHandler;
        $this->socket = &$socket;
    }

    /**
     * @return ServerHandlerInterface
     */
    public function &getServerHandler(): ServerHandlerInterface
    {
        return $this->serverHandler;
    }

    /**
     * @return Socket
     */
    public function &getSocket(): Socket
    {
        return $this->socket;
    }

    /**
     * @return float
     */
    public function getLastActivityTime(): float
    {
        return $this->lastActivity;
    }

}
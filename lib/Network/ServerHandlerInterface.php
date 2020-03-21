<?php


namespace Nicodinus\UmeyeApi\Core\Network;


use Amp\Promise;
use Amp\Socket\Socket;
use Amp\Socket\SocketAddress;
use Nicodinus\Amp\Extensions\Runnable\AsyncRunnableInterface;

interface ServerHandlerInterface extends AsyncRunnableInterface
{
    /**
     * @param SocketAddress $peer
     * @return bool
     */
    public function hasSession(SocketAddress $peer): bool;

    /**
     * @param SocketAddress $peer
     * @return ClientSessionInterface
     */
    public function getSession(SocketAddress $peer): ClientSessionInterface;

    /**
     * @param Socket $socket
     * @return Promise<ClientSessionInterface>
     */
    public function initSession(Socket &$socket): Promise;

    /**
     * @return Promise
     */
    public function run(): Promise;
}
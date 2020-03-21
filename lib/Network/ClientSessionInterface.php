<?php

namespace Nicodinus\UmeyeApi\Core\Network;


use Amp\ByteStream\ClosedException;
use Amp\ByteStream\StreamException;
use Amp\Promise;
use Amp\Socket\Socket;

interface ClientSessionInterface
{
    /**
     * @return ServerHandlerInterface
     */
    public function &getServerHandler(): ServerHandlerInterface;

    /**
     * @return Socket
     */
    public function &getSocket(): Socket;

    /**
     * @return float
     */
    public function getLastActivityTime(): float;

    /**
     * @throws ClosedException
     * @throws StreamException
     * @return Promise
     */
    public function handleRx(): Promise;
}
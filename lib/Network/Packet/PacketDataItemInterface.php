<?php


namespace Nicodinus\UmeyeApi\Core\Network\Packet;


use Nicodinus\UmeyeApi\Core\ByteUtils\ByteBuffer;

interface PacketDataItemInterface
{
    /**
     * @return bool
     */
    public function isStaticLength(): bool;

    /**
     * @return int
     */
    public function getLength(): int;

    /**
     * @return ByteBuffer|null
     */
    public function getBuffer(): ?ByteBuffer;

    /**
     * Generates output buffer and put it's to local variable
     * You should get buffer from method @link getBuffer()
     * @return static
     */
    public function mapDataToBuffer(): self;
}
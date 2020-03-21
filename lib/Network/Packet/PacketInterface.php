<?php


namespace Nicodinus\UmeyeApi\Core\Network\Packet;


use Nicodinus\UmeyeApi\Core\ByteUtils\ByteBuffer;
use Nicodinus\UmeyeApi\Core\Network\Opcode\HeaderOpcodeInterface;

interface PacketInterface extends PacketDataItemInterface, \Iterator, \ArrayAccess, \Countable, \SeekableIterator
{
    /**
     * @return HeaderOpcodeInterface
     */
    public function getHeader(): HeaderOpcodeInterface;

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

    /**
     * @return PacketDataItemInterface[]
     */
    public function getData(): array;

    /**
     * @return float|null
     */
    public function getRecvTime(): ?float;
}
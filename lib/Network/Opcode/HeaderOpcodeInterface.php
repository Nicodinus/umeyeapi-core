<?php


namespace Nicodinus\UmeyeApi\Core\Network\Opcode;


use Nicodinus\UmeyeApi\Core\Network\Packet\PacketInterface;

interface HeaderOpcodeInterface extends OpcodeInterface
{
    /**
     * @return PacketInterface
     */
    public function getPacket(): PacketInterface;

    /**
     * @return int
     */
    public function getFullPacketLength(): int;

    /**
     * @return int
     */
    public function getDataPacketLength(): int;

    /**
     * @return bool
     */
    public function validateChecksum(): bool;

    /**
     * @return static
     */
    public function calculateChecksum(): self;
}
<?php


namespace Nicodinus\UmeyeApi\Core\Network\Opcode;


use Nicodinus\UmeyeApi\Core\Network\Packet\PacketInterface;

abstract class BasicHeaderOpcode extends BasicOpcode implements HeaderOpcodeInterface
{
    /** @var PacketInterface */
    protected PacketInterface $packet;

    /** @var int */
    protected int $fullPacketLength;

    /** @var int */
    protected int $dataPacketLength;

    /**
     * BasicHeaderOpcode constructor.
     * @param PacketInterface $packet
     * @param array $data
     */
    public function __construct(PacketInterface $packet, array $data = [])
    {
        $this->packet = $packet;

        parent::__construct($data);
    }

    /**
     * @return PacketInterface
     */
    public function getPacket(): PacketInterface
    {
        return $this->packet;
    }

    /**
     * @return int
     */
    public function getFullPacketLength(): int
    {
        return $this->fullPacketLength;
    }

    /**
     * @return int
     */
    public function getDataPacketLength(): int
    {
        return $this->dataPacketLength;
    }
}
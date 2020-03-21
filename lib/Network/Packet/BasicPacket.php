<?php


namespace Nicodinus\UmeyeApi\Core\Network\Packet;


use Nicodinus\UmeyeApi\Core\ByteUtils\ByteBuffer;
use Nicodinus\UmeyeApi\Core\Network\Opcode\HeaderOpcodeInterface;

abstract class BasicPacket implements PacketInterface
{
    /** @var HeaderOpcodeInterface */
    protected HeaderOpcodeInterface $header;

    /** @var ByteBuffer|null */
    protected ?ByteBuffer $buffer = null;

    /** @var PacketDataItemInterface[] */
    protected array $data = [];

    /** @var array */
    protected array $dataKeys = [];

    /** @var int */
    protected int $dataIteratorPosition = 0;

    /** @var float|null */
    protected ?float $recvTime = null;

    /**
     * BasicOpcode constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->dataKeys = array_keys($this->data);
    }

    /**
     * @return HeaderOpcodeInterface
     */
    public function getHeader(): HeaderOpcodeInterface
    {
        return $this->header;
    }

    /**
     * @return ByteBuffer|null
     */
    public function getBuffer(): ?ByteBuffer
    {
        return $this->buffer;
    }

    /**
     * @return PacketDataItemInterface[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return float|null
     */
    public function getRecvTime(): ?float
    {
        return $this->recvTime;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->getData()[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return $this->getData()[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
        $this->dataKeys = array_keys($this->getData());
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        if (!$this->offsetExists($offset)) {
            return;
        }

        unset($this->data[$offset]);
        $this->dataKeys = array_keys($this->getData());
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return sizeof($this->getData());
    }

    /**
     * @inheritDoc
     */
    public function seek($position)
    {
        if (!isset($this->dataKeys[$position])) {
            throw new \OutOfBoundsException;
        }

        $this->dataIteratorPosition = $position;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->getData()[$this->dataKeys[$this->dataIteratorPosition]];
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->dataIteratorPosition += 1;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->dataKeys[$this->dataIteratorPosition];
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return isset($this->dataKeys[$this->dataIteratorPosition]);
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->dataIteratorPosition = 0;
    }

    /**
     * @param ByteBuffer $buffer
     * @return static
     */
    public abstract static function createFromBuffer(ByteBuffer &$buffer): self;
}
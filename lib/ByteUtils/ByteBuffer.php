<?php


namespace Nicodinus\UmeyeApi\Core\ByteUtils;


use PHPinnacle\Buffer\BufferOverflow;
use PHPinnacle\Buffer\ByteBuffer as LibraryByteBuffer;


class ByteBuffer extends LibraryByteBuffer
    implements \Iterator, \ArrayAccess, \Countable, \SeekableIterator
{
    /**
     * @var bool
     */
    private static bool $isLittleEndian;

    /**
     * @return bool
     */
    public static function isLittleEndian(): bool
    {
        return self::$isLittleEndian;
    }

    /**
     * @param bool $isLittleEndian
     */
    public static function setIsLittleEndian(bool $isLittleEndian): void
    {
        self::$isLittleEndian = $isLittleEndian;
    }

    /**
     * ByteBuffer constructor.
     * @param string $buffer
     */
    public function __construct(string $buffer = '')
    {
        parent::__construct($buffer);

        if (!isset(self::$isLittleEndian)) {
            self::$isLittleEndian  = \unpack("S", "\x01\x00")[1] === 1;
        }
    }

    /**
     * @param int $n
     * @param int $offset
     *
     * @return string
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function remove(int $n, int $offset = 0): string
    {
        if ($this->size() < $n) {
            throw new \OutOfBoundsException();
        }

        if ($offset <= 0 || $n === $this->size()) {
            return $this->consume($n);
        }

        $buffer = substr_replace($this->flush(), '', $offset, $n);

        return $this->append($buffer);
    }

    /**
     * @param string|static|LibraryByteBuffer $value
     * @param int $offset
     *
     * @throws BufferOverflow|\OutOfBoundsException|\TypeError
     * @return static
     */
    public function write($value, int $offset = 0): self
    {
        if ($offset < 0) {
            throw new \OutOfBoundsException();
        }

        if ($offset >= $this->size()) {
            return $this->append($value);
        }

        if ($value instanceof static) {
            $value = $value->read($value->size());
        }

        if (!\is_string($value)) {
            throw new \TypeError;
        }

        $data = substr_replace($this->flush(), $value, $offset, strlen($value));

        return $this->append($data);
    }

    /**
     * @param int $value
     * @param int $offset
     *
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function writeInt8(int $value, int $offset = 0): self
    {
        return $this->write(pack("c", $value), $offset);
    }

    /**
     * @param int $value
     * @param int $offset
     *
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function writeUInt8(int $value, int $offset = 0): self
    {
        return $this->write(pack("C", $value), $offset);
    }

    /**
     * @param int $value
     * @param int $offset
     *
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function writeInt16(int $value, int $offset = 0): self
    {
        return $this->write(self::swapEndian16(\pack("s", $value)), $offset);
    }

    /**
     * @param int $value
     * @param int $offset
     *
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function writeUInt16(int $value, int $offset = 0): self
    {
        return $this->write(\pack("n", $value), $offset);
    }

    /**
     * @param int $value
     * @param int $offset
     *
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function writeInt32(int $value, int $offset = 0): self
    {
        return $this->write(self::swapEndian32(\pack("l", $value)), $offset);
    }

    /**
     * @param int $value
     * @param int $offset
     *
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function writeUint32(int $value, int $offset = 0): self
    {
        return $this->write(\pack("N", $value), $offset);
    }

    /**
     * @param int $value
     * @param int $offset
     *
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function writeInt64(int $value, int $offset = 0): self
    {
        return $this->write(self::swapEndian64(\pack("q", $value)), $offset);
    }

    /**
     * @param int $value
     * @param int $offset
     *
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function writeUint64(int $value, int $offset = 0): self
    {
        return $this->write(self::swapEndian64(\pack("Q", $value)), $offset);
    }

    /**
     * @param float $value
     * @param int $offset
     *
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function writeFloat(float $value, int $offset = 0): self
    {
        return $this->write(self::swapEndian32(\pack("f", $value)), $offset);
    }

    /**
     * @param float $value
     * @param int $offset
     *
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function writeDouble($value, int $offset = 0): self
    {
        return $this->write(self::swapEndian64(\pack("d", $value)), $offset);
    }

    /**
     * @param string $s
     *
     * @return string
     */
    private static function swapEndian16(string $s): string
    {
        return self::$isLittleEndian ? $s[1] . $s[0] : $s;
    }

    /**
     * @param string $s
     *
     * @return string
     */
    private static function swapEndian32(string $s): string
    {
        return self::$isLittleEndian ? $s[3] . $s[2] . $s[1] . $s[0] : $s;
    }

    /**
     * @param string $s
     *
     * @return string
     */
    private static function swapEndian64(string $s): string
    {
        return self::$isLittleEndian ? $s[7] . $s[6] . $s[5] . $s[4] . $s[3] . $s[2] . $s[1] . $s[0] : $s;
    }


    /** ----------------------------------------------------------------------------------------------------- */


    /** @var int */
    private int $__iteratorCurrentIndex = 0;

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0
     */
    public function current()
    {
        return $this->offsetGet($this->__iteratorCurrentIndex);
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0
     */
    public function next()
    {
        $this->__iteratorCurrentIndex += 1;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return string|float|int|bool|null scalar on success, or null on failure.
     * @since 5.0
     */
    public function key()
    {
        return $this->offsetExists($this->__iteratorCurrentIndex) ? $this->__iteratorCurrentIndex : null;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0
     */
    public function valid()
    {
        return $this->offsetExists($this->__iteratorCurrentIndex);
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0
     */
    public function rewind()
    {
        $this->__iteratorCurrentIndex = 0;
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0
     */
    public function offsetExists($offset)
    {
        return !$this->empty() && $offset >= 0 && $offset < $this->size();
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0
     */
    public function offsetGet($offset)
    {
        return $this->readUint8($offset);
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function offsetSet($offset, $value)
    {
        $this->writeUint8($value, $offset);
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0
     * @throws BufferOverflow|\OutOfBoundsException
     */
    public function offsetUnset($offset)
    {
        $this->remove(1, $offset);
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1
     */
    public function count()
    {
        return $this->size();
    }

    /**
     * Seeks to a position
     * @link https://php.net/manual/en/seekableiterator.seek.php
     * @param int $position <p>
     * The position to seek to.
     * </p>
     * @return void
     * @throws \OutOfBoundsException
     * @since 5.1
     */
    public function seek($position)
    {
        if ($position < 0 || $position >= $this->size())
            throw new \OutOfBoundsException();

        $this->__iteratorCurrentIndex = $position;
    }


    /** ----------------------------------------------------------------------------------------------------- */


    /**
     * @param int[] $value
     * @return static
     */
    public static function fromArray(array $value): self
    {
        $buffer = new static();

        foreach ($value as $byte) {
            $buffer->appendUint8($byte);
        }

        return $buffer;
    }

    /**
     * @param int|float $value
     * @param int $size
     * @return static
     * @throws BufferOverflow|\OutOfBoundsException|\LogicException
     */
    public static function fromNumber($value, int $size = 4): self
    {
        switch ($size)
        {
            case 1:
                return (new static())
                    ->writeInt8($value);
            case 2:
                return (new static())
                    ->writeUint16($value);
            case 4:
                if (is_integer($value)) {
                    return (new static())
                        ->writeUint32($value);
                } else if (is_float($value)) {
                    return (new static())
                        ->writeFloat($value);
                } else {
                    throw new \LogicException("Invalid size {$size} for numeric type!");
                }
            case 8:
                if (is_integer($value) || is_long($value)) {
                    return (new static())
                        ->writeUint64($value);
                } else if (is_float($value) || is_double($value)) {
                    return (new static())
                        ->writeDouble($value);
                } else {
                    throw new \LogicException("Invalid size {$size} for numeric type!");
                }
            default:
                throw new \LogicException("Invalid size {$size} for numeric type!");
        }
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return parent::__toString();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $arr = [];
        foreach ($this as $index => $byte) {
            $arr[$index] = $byte;
        }
        return $arr;
    }

    /**
     * @param int|float|null $size
     * @return int
     * @throws \LogicException
     */
    public function toNumber(int $size = null)
    {
        if (!$size) {
            $size = $this->size();
        }

        if ($size < 1 || $size > 8)
            $size = 4;

        switch ($size)
        {
            case 1:
                return $this->readUint8(0);
            case 2:
                return $this->readUint16(0);
            case 4:
                return $this->readUint32(0);
            case 8:
                return $this->readUint64(0);
            default:
                throw new \LogicException("Unsupported cast!");
        }
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function toHexString(string $delimiter = ''): string
    {
        $arr = [];
        foreach ($this as $index => $byte) {
            $arr[$index] = str_pad(dechex($byte), 2, 0, STR_PAD_LEFT);
        }

        return strtoupper(implode($delimiter, $arr));
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @return static
     * @throws BufferOverflow
     */
    public function chunk(int $offset = 0, int $length = null): self
    {
        if (!$length || $length < 0) {
            $length = $this->size();
        }

        return new static($this->read($length, $offset));
    }

    /**
     * @param int $value
     * @return int
     */
    public static function signed2UnsignedInt8(int $value): int
    {
        return (new static())
            ->appendInt8($value)
            ->consumeUint8();
    }

    /**
     * @param int $value
     * @return int
     */
    public static function unsigned2SignedInt8(int $value): int
    {
        return (new static())
            ->appendUint8($value)
            ->consumeInt8();
    }

    /**
     * @param int $value
     * @return int
     */
    public static function signedInt8(int $value): int
    {
        return (new static())
            ->appendInt8($value)
            ->consumeInt8();
    }

    /**
     * @param int $value
     * @return int
     */
    public static function unsignedInt8(int $value): int
    {
        return (new static())
            ->appendUint8($value)
            ->consumeUint8();
    }

    /**
     * @param int $value
     * @return static
     */
    public static function fromUnsignedInt8(int $value): self
    {
        return (new static())
            ->appendUint8($value);
    }

    /**
     * @param int $value
     * @return static
     */
    public static function fromSignedInt8(int $value): self
    {
        return (new static())
            ->appendInt8($value);
    }
}
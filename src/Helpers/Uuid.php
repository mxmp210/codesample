<?php
namespace App\Helpers;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\Injection\ValueInterface;

use Ramsey\Uuid\Uuid as UuidBody;
use Ramsey\Uuid\UuidInterface;


/**
 * Represents Uuid Type in Application
 */
final class Uuid implements ValueInterface
{
    private function __construct(
        private UuidInterface $uuid
    ) {
    }
    
    /**
     * Returns the binary string representation of the UUID
     *
     * @return string
     */
    public function rawValue(): string
    {
        return $this->uuid->getBytes();
    }

    /**
     * Treate as RAW Type by PDO - if used in underlying ORM
     *
     * @return integer
     */
    public function rawType(): int
    {
        return \PDO::PARAM_LOB;
    }

    /**
     * Returns the string standard representation of the UUID
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->uuid->toString();
    }

    /**
     * Get Uuid bytes string
     *
     * @return string
     */
    public function getBytes(): string 
    {
        return $this->uuid->getBytes();
    }

    /**
     * Genrate new Uuid
     * 
     * @return static
     */
    public static function generate(): static 
    {
        return new static(
            UuidBody::uuid4()
        );
    }

    /**
     * Convert Uuid value from DB - Required by ORM typecaster
     *
     * @param  string            $value
     * @param  DatabaseInterface $db
     * @return static
     */
    public static function castValue(string $value, DatabaseInterface $db): static
    {
        if (is_resource($value)) {
            // postgres
            $value = fread($value, 16);
        }

        return new static(
            UuidBody::fromBytes($value)
        );
    }

    /**
     * Validated if string is valid Uuid
     *
     * @param  string  $value
     * @return boolean
     */
    public static function isValid(string $value) : bool {
        return preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $value) != false;
    }

    /**
     * Create Uuid from string
     *
     * @param  string $value
     * @return self
     */
    public static function fromString(string $value): self 
    {
        return new self(
            UuidBody::fromString($value)
        );
    }
}
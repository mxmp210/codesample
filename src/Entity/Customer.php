<?php

namespace App\Entity;

use Cycle\Annotated\Annotation as ORM;
use Cycle\ORM\Entity\Behavior;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

/**
 * Represents POPO Class of Customer
 */
#[ORM\Entity(table:"customer", repository: \App\Repository\CustomerRepository::class )]
#[Behavior\CreatedAt(
    field: 'createdAt',   // Required. By default 'createdAt'
    column: 'created_at'  // Optional. By default 'null'. If not set, will be used information from property declaration.
)]
#[Behavior\UpdatedAt(
    field: 'updatedAt',   // Required. By default 'updatedAt' 
    column: 'updated_at'  // Optional. By default 'null'. If not set, will be used information from property declaration.
)]
#[AppAssert\UniqueCustomerEmail()]
class Customer
{
    /**
     * Generates Customer object
     *
     * @param  \App\Helpers\Uuid $id
     * @param  string $email
     * @param  string $phone
     * @param  string $name
     * @param  string $comment
     */
    public function __construct(

        /** @var \App\Helpers\Uuid $id */
        #[ORM\Column(field: 'id', type: 'uuid_binary',  typecast: [\App\Helpers\Uuid::class, 'castValue'],  primary: true) ]
        #[Assert\Uuid]
        #[Assert\NotBlank()]
        private \App\Helpers\Uuid $id,

        /** @var string $email */
        #[ORM\Column(type: "string(64)")]
        #[Assert\Email()]
        #[Assert\Length(min: 5, max: 64)]
        #[Assert\NotBlank()]
        private $email,

        /** @var string $phone */
        #[ORM\Column(type: "string(15)")]
        #[Assert\Length(min: 3, max: 15)]
        private $phone,

        /** @var string $name */
        #[ORM\Column(type: "string(255)")]
        #[Assert\Length(min: 3, max: 255)]
        #[Assert\NotBlank()]
        private $name,

        /** @var string $comment */
        #[ORM\Column(type: "string(1000)")]
        #[Assert\Length(max: 1000)]
        #[Assert\NotBlank()]
        private $comment,

        #[ORM\Column(type: 'datetime')]
        private \DateTimeImmutable $createdAt,
        
        #[ORM\Column(type: 'datetime', nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null
    ) {
    }

    public function getId(): string
    {
        return $this->id->__toString();
    }

    public function setId(string $id): self
    {
        $this->id = \App\Helpers\Uuid::fromString($id);
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt) {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}

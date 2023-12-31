<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ConfigurationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Configuration
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
#[UniqueEntity('name')]
#[ApiResource(
    types: ['https://schema.org/Configuration'],
    operations: [
        new GetCollection(
            order: [
                'name' => 'ASC',
            ]
        ),
        new Get(),
        new Post(
            security: 'is_granted(\'' . User::ROLE_ADMIN . '\')'
        ),
        new Put(
            security: 'is_granted(\'' . User::ROLE_ADMIN . '\')'
        ),
        new Patch(
            security: 'is_granted(\'' . User::ROLE_ADMIN . '\')'
        ),
        new Delete(
            security: 'is_granted(\'' . User::ROLE_ADMIN . '\')'
        ),
    ]
)]
class Configuration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var string $name
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    /**
     * @var string $value
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private string $value;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}

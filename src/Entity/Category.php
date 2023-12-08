<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\CategoryRepository;
use App\Util\Slugger;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Category
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('name')]
#[ApiResource(
    types: ['https://schema.org/Category'],
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => [
                    'categories:collection:get',
                ],
            ],
            order: [
                'name' => 'ASC',
            ]
        ),
        new Get(
            normalizationContext: [
                'groups' => [
                    'categories:item:get',
                ],
            ]
        ),
        new Get(
            uriTemplate: '/videos/{id}/category',
            uriVariables: [
                'id' => new Link(
                    fromClass: Video::class,
                    fromProperty: 'category'
                ),
            ],
            normalizationContext: [
                'groups' => [
                    'categories:item:get',
                ],
            ]
        ),
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
class Category implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['categories:collection:get', 'categories:item:get'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['categories:collection:get', 'categories:item:get'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $slug;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Video::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    #[ApiFilter(OrderFilter::class, arguments: ['orderParameterName' => 'order'])]
    private Collection $videos;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
    }

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
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setSlug(): void
    {
        $this->slug = Slugger::slugify($this->name);
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    /**
     * @param Video $video
     * @return $this
     */
    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setCategory($this);
        }

        return $this;
    }

    /**
     * @param Video $video
     * @return $this
     */
    public function removeVideo(Video $video): static
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getCategory() === $this) {
                $video->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}

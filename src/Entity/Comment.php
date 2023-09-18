<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\ApiResource\State\Processor\VideoCommentsProcessor;
use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Comment
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    types: ['https://schema.org/Comment'],
    operations: [
        new GetCollection(
            uriTemplate: '/videos/{id}/comments',
            uriVariables: [
                'id' => new Link(
                    fromClass: Video::class,
                    fromProperty: 'comments'
                ),
            ],
            normalizationContext: [
                'groups' => [
                    'comments:collection:get',
                ],
            ],
            order: [
                'createdAt' => 'DESC',
            ]
        ),
        new Post(
            read: false,
            uriTemplate: '/videos/{id}/comments',
            uriVariables: [
                'id' => new Link(
                    fromClass: Video::class,
                    toProperty: 'comments'
                ),
            ],
            processor: VideoCommentsProcessor::class,
            security: 'is_granted(\'' . User::ROLE_USER . '\')',
            normalizationContext: [
                'groups' => [
                    'comments:collection:get',
                ],
            ]
        ),
        new Delete(
            uriTemplate: '/videos/{id}/comments/{commentId}',
            uriVariables: [
                'id' => new Link(
                    toProperty: 'video',
                    fromClass: Video::class
                ),
                'commentId' => new Link(
                    fromClass: Comment::class
                ),
            ],
            processor: VideoCommentsProcessor::class,
            security: 'is_granted(\'' . User::ROLE_USER . '\')'
        )
    ]
)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['comments:collection:get'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['comments:collection:get'])]
    private string $comment;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['comments:collection:get'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['comments:collection:get'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Video $video = null;

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
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return void
     */
    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable('now');

    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Video
     */
    public function getVideo(): Video
    {
        return $this->video;
    }

    /**
     * @param Video|null $video
     * @return $this
     */
    public function setVideo(?Video $video): static
    {
        $this->video = $video;

        return $this;
    }

    public function isOwner(?User $user): bool
    {
        return $user && $this->user->getId() === $user->getId();
    }
}

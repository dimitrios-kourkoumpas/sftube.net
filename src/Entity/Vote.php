<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\ApiResource\State\Processor\VideoVotesProcessor;
use App\Repository\VoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Vote
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            read: false,
            uriTemplate: '/videos/{id}/votes',
            uriVariables: [
                'id' => new Link(
                    fromClass: Video::class,
                    toProperty: 'votes'
                ),
            ],
            processor: VideoVotesProcessor::class,
            security: 'is_granted(\'' . User::ROLE_USER . '\')'
        ),
    ]
)]
class Vote
{
    public const UP = 'up';

    public const DOWN = 'down';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 4)]
    private ?string $vote = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    private ?Video $video = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVote(): ?string
    {
        return $this->vote;
    }

    public function setVote(string $vote): static
    {
        $this->vote = $vote;

        return $this;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): static
    {
        $this->video = $video;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}

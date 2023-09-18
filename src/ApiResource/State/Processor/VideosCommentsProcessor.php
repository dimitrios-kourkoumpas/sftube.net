<?php

declare(strict_types=1);

namespace App\ApiResource\State\Processor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Comment;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class VideosCommentsProcessor
 * @package App\ApiResource\State\Processor
 */
final readonly class VideosCommentsProcessor implements ProcessorInterface
{
    /**
     * @param EntityManagerInterface $em
     * @param Security $security
     */
    public function __construct(private EntityManagerInterface $em, private Security $security)
    {
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return void
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $video = $this->em->getRepository(Video::class)->find($uriVariables['id']);

        $user = $this->security->getUser();

        if ($operation instanceof Post) {
            $data->setUser($user);

            $data->setVideo($video);

            $this->em->persist($data);
        }

        if ($operation instanceof Delete) {
            $comment = $this->em->getRepository(Comment::class)->find($uriVariables['commentId']);

            if ($comment->isOwner($user)) {
                $video->removeComment($comment);

                $this->em->persist($video);
            } else {
                throw new AccessDeniedException();
            }
        }

        $this->em->flush();
    }
}

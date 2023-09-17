<?php

declare(strict_types=1);

namespace App\ApiResource\State\Processor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Tag;
use App\Entity\Video;
use App\Security\Voter\VideoVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Class VideosTagsProcessor
 * @package App\ApiResource\State\Processor
 */
final readonly class VideosTagsProcessor implements ProcessorInterface
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
            if ($user && $this->security->isGranted(VideoVoter::EDIT, $video)) {
                $video->addTag($data);
            }
        }

        if ($operation instanceof Delete) {
            if ($user && $this->security->isGranted(VideoVoter::DELETE, $video)) {
                $tag = $this->em->getRepository(Tag::class)->find($uriVariables['tagId']);

                $video->removeTag($tag);
            }
        }

        $this->em->persist($video);
        $this->em->flush();
    }
}

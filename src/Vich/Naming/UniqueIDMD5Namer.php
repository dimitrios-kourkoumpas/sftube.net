<?php

declare(strict_types=1);

namespace App\Vich\Naming;

use App\Util\FileRenamer;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

/**
 * Class UniqueIDMD5Namer
 * @package App\Vich\Naming
 */
final class UniqueIDMD5Namer implements NamerInterface
{
    /**
     * @param object $object
     * @param PropertyMapping $mapping
     * @return string
     */
    public function name(object $object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);

        $filename = $file->getClientOriginalName();

        return FileRenamer::rename($filename);
    }
}

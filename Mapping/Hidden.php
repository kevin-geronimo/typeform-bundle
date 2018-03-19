<?php

namespace TypeformBundle\Mapping;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target({"CLASS","PROPERTY","ANNOTATION"})
 */
final class Hidden implements Annotation
{
    /**
     * Parameter hidden data name.
     *
     * @var string
     */
    public $name;
}

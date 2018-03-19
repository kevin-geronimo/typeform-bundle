<?php

namespace TypeformBundle\Mapping;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target({"CLASS","PROPERTY","ANNOTATION"})
 */
final class Field implements Annotation
{
    /**
     * Parameter field id.
     *
     * @var string
     */
    public $id;
}

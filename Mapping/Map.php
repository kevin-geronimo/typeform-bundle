<?php

namespace TypeformBundle\Mapping;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target({"CLASS","PROPERTY","ANNOTATION"})
 */
final class Map implements Annotation
{
    /**
     * Parameter fields ids.
     *
     * @var array
     */
    public $fields;
}

<?php

namespace TypeformBundle\Core;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\Request;
use TypeformBundle\Entity\BaseTypeform;

/**
 * Class Form.
 */
class Form
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * Reference class for form's mapped entity.
     *
     * @var BaseTypeform
     */
    private $entity;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $hidden = array();

    /**
     * @var array
     */
    private $fields = array();

    /**
     * @var bool
     */
    private $hydrated = false;

    /**
     * @var string
     */
    private $typeformUrl;

    /**
     * @var string
     */
    private $hookKey = 'complete';

    /**
     * Form constructor.
     *
     * @param string $name
     * @param string $id
     * @param string $entity
     * @param Client $client
     *
     * @throws \Exception
     */
    public function __construct(string $name, string $id, string $entity, Client $client)
    {
        $this->id = $id;
        $this->name = $name;
        $this->client = $client;

        if (!class_exists($entity)) {
            throw new \Exception('invalid entity field must be an existing class.');
        }

        $this->entity = new $entity();
        if (!$this->entity instanceof BaseTypeform) {
            throw new \Exception('invalid entity must be extends BaseTypeform entity.');
        }

        $this->load();
        $this->entity->setForm($this->name);
        $this->typeformUrl = $this->client->getConfiguration()->getEmbedUrl();
    }

    /**
     * Load class annotations.
     *
     */
    private function load()
    {
        $reflection = new \ReflectionClass(get_class($this->entity));
        $reader = new AnnotationReader();

        $properties = $reflection->getProperties();
        foreach ($properties as $key => $property) {
            // Get hidden data
            $hidden = $reader->getPropertyAnnotation($property, 'TypeformBundle\\Mapping\\Hidden');
            if (null !== $hidden) {
                $this->hidden[$property->getName()] = [
                    'name' => $hidden->name,
                    'value' => null,
                ];
            }

            // Get fields
            $field = $reader->getPropertyAnnotation($property, 'TypeformBundle\\Mapping\\Field');
            $column = $reader->getPropertyAnnotation($property, 'Doctrine\\ORM\\Mapping\\Column');
            if (null !== $field) {
                $this->fields[$property->getName()] = [
                    'id'       => $field->id,
                    'name'     => $property->getName(),
                    'type'     => $column->type,
                    'value'    => null,
                    'nullable' => $column->nullable,
                ];
            }

            // Get maps
            $map = $reader->getPropertyAnnotation($property, 'TypeformBundle\\Mapping\\Map');
            $column = $reader->getPropertyAnnotation($property, 'Doctrine\\ORM\\Mapping\\Column');
            if (null !== $map) {
                if (isset($map->fields[$this->name])) {
                    $this->fields[$property->getName()] = [
                        'id'       => $map->fields[$this->name],
                        'name'     => $property->getName(),
                        'type'     => $column->type,
                        'value'    => null,
                        'nullable' => $column->nullable,
                    ];
                }
            }
        }
    }

    /**
     * Hydrate entity with fields values.
     */
    private function hydrate()
    {
        foreach ($this->fields as $field) {
            $this->set($field['name'], $field['value']);
        }

        foreach ($this->hidden as $hidden) {
            $this->set($hidden['name'], $hidden['value']);
        }

        $this->hydrated = true;
    }

    /**
     * Set entity value from column name.
     *
     * @param string $column
     * @param $value
     */
    public function set(string $column, $value)
    {
        $camelCaseName = preg_replace_callback('/_([a-z])/', function ($c) {
            return strtoupper($c[1]);
        }, ucfirst($column));
        $setter = 'set'.$camelCaseName;
        $this->entity->{$setter}($value);
    }

    /**
     * Transform answer to column type.
     *
     * @param \stdClass $answer
     * @param string    $type
     *
     * @return mixed
     */
    private function transformValue(\stdClass $answer, string $type)
    {
        // Get answer value
        switch (true) { // @fixme : get all types
            case 'choices' === $answer->type || 'choice' === $answer->type:
                $key = 'label'.(('s' === substr($answer->type, -1)) ? 's' : '');
                $value = $answer->{$answer->type}->{$key};
                break;
            case 'text' === $answer->type || 'email' === $answer->type:
                $value = $answer->{$answer->type};
                break;
            default:
                $value = null;
                // @fixme: exception
                // throw new \Exception('transform value failed : unknow type -> ' . $answer->type);
                break;
        }

        // Set correct type hint from column type
        if (!is_array($value)) {
            switch (true) {
                case 'int' === $type || 'integer' === $type:
                    $value = intval($value);
                    break;
                case 'float' === $type:
                    $value = floatval($value);
                    break;
                case 'bool' === $type || 'boolean' === $type:
                    $value = boolval($value);
                    break;
            }
        }

        return $value;
    }

    /**
     * Set hidden data.
     *
     * @param $hiddenData
     */
    public function setHiddenData($hiddenData)
    {
        foreach ($hiddenData as $key => $value) {
            if (in_array($key, array_keys($this->hidden))) {
                $this->hidden[$key]['value'] = $value;
            }
        }
    }

    /**
     * Valid hidden data, all values must be filled with a string.
     *
     * @return bool
     */
    private function isValidHiddenData()
    {
        foreach ($this->hidden as $hidden) {
            if (null === $hidden['value'] || !is_string($hidden['value'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve embedded typeform url.
     *
     * @return string
     */
    public function getEmbedUrl()
    {
        if (!$this->isValidHiddenData()) {
            return null;
            // @fixme: exception
            // throw new \Exception('Hidden data must be filled, use setHiddenData');
        }

        // Build redirect url
        $configuration = $this->client->getConfiguration();
        $routingContext = $configuration->getRouter()->getContext();
        $redirectUrl = trim($configuration->getBaseUrl().$routingContext->getPathInfo().'?'.$routingContext->getQueryString(), '?');
        $redirectUrl .= ('' === $routingContext->getQueryString()) ? '?' : '&';
        $redirectUrl .= $this->hookKey.'=true';

        $hiddenData = array_column($this->hidden, 'value', 'name');
        $data = array_merge($hiddenData, [
            'redirect_url' => $redirectUrl,
        ]);

        // Build typeform url
        $url = $this->typeformUrl.'/to/'.$this->id;
        if (!empty($data)) {
            $url .= '?'.http_build_query($data);
        }

        return $url;
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function handleRequest(Request $request)
    {
        $hook = $request->get($this->hookKey, null);
        if (null !== $hook) {
            $response = $this->client->getLastResponse($this->id);
            if ($response && !empty($response->answers)) {
                foreach ($response->answers as $index => $answer) {
                    $key = array_search($answer->field->id, array_column($this->fields, 'id'));
                    if (false !== $key) {
                        $fieldName = array_column($this->fields, 'name')[$key];
                        $this->fields[$fieldName]['value'] = $this->transformValue($answer, $this->fields[$fieldName]['type']);
                    }
                }

                $this->hydrate();

                $this->entity->setToken($response->token);
                $this->entity->setAddressIp($request->getClientIp());
                $this->entity->setSubmitAt(new \DateTime($response->submitted_at));
            }
        }

        return $this;
    }

    /**
     * Check if form is valid for persistence.
     *
     * @return bool
     */
    public function isValid()
    {
        if (!$this->hydrated || null === $this->entity->getSubmitAt()) {
            return false;
        }

        if (!$this->isValidHiddenData()) {
            return false;
        }

        return true;
    }

    /**
     * Return current form entity.
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }
}

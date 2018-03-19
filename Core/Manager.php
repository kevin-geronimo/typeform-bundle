<?php

namespace TypeformBundle\Core;

/**
 * Class Manager.
 */
class Manager
{
    private $client;

    private $forms = array();

    /**
     * Manager constructor.
     *
     * @param array  $forms
     * @param Client $client
     *
     * @throws \Exception
     */
    public function __construct(array $forms, Client $client)
    {
        $this->client = $client;
        $this->loadForms($forms);
    }

    /**
     * Build form collection.
     *
     * @param array $forms
     *
     * @throws \Exception
     */
    private function loadForms(array $forms)
    {
        foreach ($forms as $formName => $formParams) {
            $this->addForm($formName, $formParams);
        }
    }

    /**
     * Add a form to collection.
     *
     * @param $name
     * @param $parameters
     *
     * @throws \Exception
     */
    private function addForm($name, $parameters)
    {
        if (!isset($this->forms[$name])) {
            try {
                $form = new Form($name, $parameters['id'], $parameters['entity'], $this->client);
                $this->forms[$name] = $form;
            } catch (\Exception $exception) {
                throw $exception;
            }
        } else {
            throw new \Exception('Form: '.$name.' already exist, form name must be unique');
        }
    }

    /**
     * Get form by name.
     *
     * @param $formName
     *
     * @return Form|null
     */
    public function get($formName)
    {
        if (!isset($this->forms[$formName])) {
            return null;
            // @fixme: exception
            // throw new \Exception('Unknow form');
        }

        return $this->forms[$formName];
    }
}

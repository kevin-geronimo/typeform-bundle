<?php

namespace TypeformBundle\Core;

use GuzzleHttp\Client as HttpClient;

/**
 * Class Client.
 */
class Client
{
    private const API_URL = 'https://api.typeform.com/v1';

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Client
     */
    private $client;

    // @todo: complete client with typeform api docs

    /**
     * Client constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->client = new HttpClient();
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * Get last response for given form.
     *
     * @param string $formId
     *
     * @return mixed
     */
    public function getLastResponse(string $formId)
    {
        $data = $this->request('GET', '/forms/'.$formId.'/responses', [
            'query' => [
                'completed' => true,
                'sort[]' => 'submitted_at,desc',
            ],
        ]);

        return $data->items[0] ?? null;
    }

    /**
     * Get responses for given form.
     *
     * @param string $formId
     * @param int    $limit
     *
     * @return bool|null
     */
    public function getResponses(string $formId, int $limit = 20)
    {
        $data = $this->request('GET', '/forms/'.$formId.'/responses', [
            'query' => [
                'completed' => true,
                'sort[]' => 'date_submit,desc',
                'limit' => (string) $limit,
            ],
        ]);

        return $data->items ?? null;
    }

    /**
     * Get a form.
     *
     * @param string $id
     * @param array  $data
     *
     * @return mixed
     */
    public function getForm(string $id, array $data = [])
    {
        return $this->request('GET', '/form/'.$id, $data);
    }

    /**
     * List all forms.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function getForms(array $data = [])
    {
        return $this->request('GET', '/forms', $data);
    }

    /**
     * Typeform API Client
     * See http://docs.guzzlephp.org for data parameters options.
     *
     * @param string     $method
     * @param string     $path
     * @param array|null $data
     *
     * @return mixed
     */
    public function request(string $method = 'GET', string $path = '/', array $data = [])
    {
        $url = self::API_URL.$path;

        // Always send credentials
        $data = array_merge_recursive($data, array(
            'query' => [
                'key' => $this->configuration->getApiKey(),
            ],
        ));

        $response = $this->client->request($method, $url, $data);
        if (200 !== $response->getStatusCode()) {
            return null;
        }

        return json_decode($response->getBody()->getContents());
    }
}

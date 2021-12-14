<?php

namespace BestBrands\Shiplee;

use Closure;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The main API client for shiplee, will handle messy authentication.
 */
class Client
{
    const ENDPOINT_URL = 'https://dashboard.shiplee.com';

    protected string $cookies = '';

    protected string $email = '';

    protected string $password = '';

    protected \GuzzleHttp\Client $client;

    protected CookieJar $jar;

    protected bool $ignore_authentication = false;

    public function __construct(string $email, string $password)
    {
        $this->email    = $email;
        $this->password = $password;

        if (empty($email) || empty($password)) {
            throw new InvalidArgumentException("Empty email or password.");
        }

        $stack = HandlerStack::create();
        $stack->push(Middleware::mapRequest($this->getUserAgentFakerHandler()));
        $stack->push(Middleware::mapRequest($this->getRequestHandler()));
        $stack->push(Middleware::retry($this->getRetryHandler()));

        $this->jar = new CookieJar();

        $this->client = new \GuzzleHttp\Client([
            'handler'         => $stack,
            'base_uri'        => self::ENDPOINT_URL,
            'cookies'         => $this->jar,
            'allow_redirects' => [
                'max'             => 5,
                'track_redirects' => true,
            ],
        ]);
    }

    /**
     * Authenticate the client.
     *
     * @throws GuzzleException
     */
    public function authenticate()
    {
        $this->ignore_authentication = true;

        $this->post('inloggen', [
            RequestOptions::FORM_PARAMS => [
                'email'       => $this->email,
                'password'    => $this->password,
                'remember_me' => 'y',
                'submit'      => 'Inloggen',
            ],
            'shiplee_requires_csrf' => true,
        ])->getBody();

        $this->ignore_authentication = false;
    }

    /**
     * Adds some fake headers to prevent detection.
     *
     * @return Closure
     */
    private function getUserAgentFakerHandler(): Closure
    {
        return function (RequestInterface $request) {
            return $request
                ->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.93 Safari/537.36')
                ->withHeader('Accept-Language', 'en-US,en;q=0.9,nl;q=0.8')
                ->withHeader('Referer', 'https://dashboard.shiplee.com/')
            ;
        };
    }

    /**
     * Get the request handler
     *
     * @return Closure
     */
    private function getRequestHandler(): Closure
    {
        return function (RequestInterface $request) {
            return $request;
        };
    }

    /**
     * Retry the request if needed.
     *
     * @return Closure
     */
    private function getRetryHandler(): Closure
    {
        return function ($retries, ?RequestInterface $request, ?ResponseInterface $response,
            ?GuzzleException $exception) {
            return false;
        };
    }

    /**
     * Perform a get request.
     *
     * @param string $url
     * @param array  $options
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->request('GET', $url, $options);
    }

    /**
     * Perform a post request.
     *
     * @param string $url
     * @param array  $options
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post(string $url, array $options = []): ResponseInterface
    {
        return $this->request('POST', $url, $options);
    }

    /**
     * Perform a head request.
     *
     * @param string $url
     * @param array  $options
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function head(string $url, array $options = []): ResponseInterface
    {
        return $this->request('HEAD', $url, $options);
    }

    /**
     * @throws GuzzleException
     */
    public function request(string $method, string $url, array $options)
    {
        $options = array_merge($options, [
            'cookies' => $this->jar,
        ]);

        if (!empty($options['shiplee_requires_csrf'])) {
            unset($options['shiplee_requires_csrf']);
            $options['form_params']['csrf_token'] = $this->getCsrfToken($url);
        }

        return $this->client->request($method, $url, $options);
    }

    /**
     * @param string $url
     *
     * @return string|null
     * @throws GuzzleException
     */
    private function getCsrfToken(string $url): ?string
    {
        try {
            $crawler = new Crawler((string)$this->get($url)->getBody());
            return $crawler->filter('input[name="csrf_token"]')->attr('value');
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }
}

<?php

namespace TheThunderTurner\Docker;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use TheThunderTurner\Docker\Exceptions\DockerException;

class Transport
{
    public function __construct(
        private ?string $socketPath = null,
        private ?string $apiVersion = null,
    ) {
        $this->socketPath ??= config('laradocker-api.socket', '/var/run/docker.sock');
        $this->apiVersion ??= config('laradocker-api.api_version', 'v1.54');
    }

    public function client(): PendingRequest
    {
        return Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => $this->socketPath]])
            ->baseUrl("http://localhost/{$this->apiVersion}")
            ->withHeaders(['Content-Type' => 'application/json']);
    }

    /**
     * @param  array<string, mixed>  $query
     *
     * @throws ConnectionException
     * @throws DockerException
     */
    public function get(string $endpoint, array $query = []): Response
    {
        return $this->ensureOk($this->client()->get($endpoint, $query), $endpoint);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, mixed>  $query
     *
     * @throws ConnectionException
     * @throws DockerException
     */
    public function post(string $endpoint, ?array $body = null, array $query = []): Response
    {
        $url = $query === [] ? $endpoint : $endpoint.'?'.http_build_query($query);

        return $this->ensureOk($this->client()->post($url, $body ?? []), $endpoint);
    }

    /**
     * @param  array<string, mixed>  $query
     *
     * @throws ConnectionException
     * @throws DockerException
     */
    public function delete(string $endpoint, array $query = []): Response
    {
        $url = $query === [] ? $endpoint : $endpoint.'?'.http_build_query($query);

        return $this->ensureOk($this->client()->delete($url), $endpoint);
    }

    /**
     * @param  array<string, mixed>  $query
     *
     * @throws ConnectionException
     * @throws DockerException
     */
    public function head(string $endpoint, array $query = []): Response
    {
        $url = $query === [] ? $endpoint : $endpoint.'?'.http_build_query($query);

        return $this->ensureOk($this->client()->head($url), $endpoint);
    }

    /**
     * PUT a raw body (used for archive uploads, which send tarballs).
     *
     * @param  array<string, string>  $headers  Override Content-Type, etc.
     * @param  array<string, mixed>  $query
     *
     * @throws ConnectionException
     * @throws DockerException
     */
    public function put(string $endpoint, string $body, array $headers = [], array $query = []): Response
    {
        $url = $query === [] ? $endpoint : $endpoint.'?'.http_build_query($query);

        $request = Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => $this->socketPath]])
            ->baseUrl("http://localhost/{$this->apiVersion}")
            ->withHeaders(array_merge(['Content-Type' => 'application/x-tar'], $headers))
            ->withBody($body, $headers['Content-Type'] ?? 'application/x-tar');

        return $this->ensureOk($request->put($url), $endpoint);
    }

    /**
     * @throws DockerException
     */
    private function ensureOk(Response $response, string $endpoint): Response
    {
        if ($response->failed()) {
            $message = $response->json('message') ?? $response->body();
            throw new DockerException(
                "Docker API error on {$endpoint} ({$response->status()}): {$message}",
                $response->status(),
            );
        }

        return $response;
    }
}

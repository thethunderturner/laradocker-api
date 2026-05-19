<?php

namespace TheThunderTurner\Docker\Resources;

use Illuminate\Http\Client\ConnectionException;
use TheThunderTurner\Docker\Transport;

class System
{
    public function __construct(private Transport $transport) {}

    /**
     * Validate credentials for a registry and, if available, get an identity token for accessing the registry without password.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/System/operation/SystemAuth
     */
    public function auth(string $username, string $password, string $serverAddress): string
    {
        return $this->transport
            ->post('/auth', ['username' => $username, 'password' => $password, 'serveraddress' => $serverAddress])
            ->body();
    }

    /**
     * Get system information
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/System/operation/SystemInfo
     */
    public function info(): array
    {
        return $this->transport
            ->get('/info')
            ->json();
    }

    /**
     * Returns the version of Docker that is running and various information about the system that Docker is running on.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/System/operation/SystemVersion
     */
    public function version(): array
    {
        return $this->transport
            ->get('/version')
            ->json();
    }

    /**
     * This is a dummy endpoint you can use to test if the server is accessible.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/System/operation/SystemPing
     */
    public function ping(): array
    {
        return $this->transport
            ->get('/_ping')
            ->json();
    }

    /**
     * This is a dummy endpoint you can use to test if the server is accessible.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/System/operation/SystemPingHead
     */
    public function pingHead(): array
    {
        return $this->transport
            ->head('/_ping')
            ->json();
    }

    /**
     * Stream real-time events from the server.
     * Various objects within Docker report events when something happens to them.
     *
     * NOTE: Works only as a "snapshot", capturing events from the past. As for real time sockets, its a TODO.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/System/operation/SystemEvents
     */
    public function events(int $since, int $until, array $filters = []): array
    {
        $query = [
            'since' => (string) $since,
            'until' => (string) $until,
        ];

        if ($filters !== []) {
            $query['filters'] = json_encode($filters);
        }

        $body = $this->transport->get('/events', $query)->body();

        // Docker returns NDJSON: one JSON object per line, not a JSON array.
        return array_values(array_filter(array_map(
            fn (string $line) => json_decode($line, true),
            explode("\n", trim($body))
        )));
    }

    /**
     * Get data usage information
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/System/operation/SystemDataUsage
     */
    public function usage(array $type, bool $verbose = false): array
    {
        return $this->transport
            ->get('/system/df', [
                'type' => $type,
                'verbose' => $verbose,
            ])
            ->json();
    }
}

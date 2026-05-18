<?php

namespace TheThunderTurner\Docker;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Docker
{
    private function socket(): PendingRequest
    {
        $socket = config('laradocker-api.socket', '/var/run/docker.sock');

        return Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => $socket]])
            ->baseUrl('http://localhost')
            ->withHeaders(['Content-Type' => 'application/json']);
    }

    /** @return array<int, array<string, mixed>>
     * @throws ConnectionException
     */
    public function listContainers(bool $all = true): array
    {
        return $this->socket()
            ->get('/containers/json', ['all' => $all ? 'true' : 'false'])
            ->json();
    }

    /**
     * @throws ConnectionException
     */
    public function stopContainer(string $id): array
    {
        return $this->socket()
            ->post("/containers/{$id}/stop")
            ->json();
    }

    /**
     * @throws ConnectionException
     */
    public function restartContainer(string $id): array
    {
        return $this->socket()
            ->post("/containers/{$id}/restart")
            ->json();
    }

    /** @param array<string, mixed> $config
     * @throws ConnectionException
     */
    public function createContainer(array $config): array
    {
        return $this->socket()
            ->post('/containers/create', $config)
            ->json();
    }

    /**
     * @param string $id
     * @param bool $stdout
     * @param bool $stderr
     * @param bool $timestamps
     * @param int|null $tail
     * @return array{stdout: string, stderr: string}
     * @throws ConnectionException
     */
    public function containerLogs(
        string $id,
        bool $stdout = true,
        bool $stderr = true,
        bool $timestamps = false,
        ?int $tail = null
    ): array {
        $query = [
            'stdout' => $stdout ? '1' : '0',
            'stderr' => $stderr ? '1' : '0',
            'timestamps' => $timestamps ? '1' : '0',
        ];

        if ($tail !== null) {
            $query['tail'] = (string) $tail;
        }

        $response = $this->socket()->get("/containers/{$id}/logs", $query);

        if ($response->failed()) {
            $message = $response->json('message') ?? $response->body();
            throw new \RuntimeException("Failed to fetch logs for {$id}: {$message}", $response->status());
        }

        return $this->demuxLogs($response->body());
    }

    /**
     * Parse Docker's multiplexed log stream format.
     * Each frame: [stream_type(1)][padding(3)][size(4 big-endian)][payload(size)]
     *
     * @return array{stdout: string, stderr: string}
     */
    private function demuxLogs(string $raw): array
    {
        $stdout = '';
        $stderr = '';
        $offset = 0;
        $length = strlen($raw);

        while ($offset + 8 <= $length) {
            $streamType = ord($raw[$offset]);
            $size = unpack('N', substr($raw, $offset + 4, 4))[1];
            $offset += 8;

            if ($offset + $size > $length) {
                break;
            }

            $payload = substr($raw, $offset, $size);
            $offset += $size;

            match ($streamType) {
                1 => $stdout .= $payload,
                2 => $stderr .= $payload,
                default => null,
            };
        }

        return ['stdout' => $stdout, 'stderr' => $stderr];
    }
}

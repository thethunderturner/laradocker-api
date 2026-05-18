<?php

namespace TheThunderTurner\Docker\Resources;

use Illuminate\Http\Client\ConnectionException;
use TheThunderTurner\Docker\Exceptions\DockerException;
use TheThunderTurner\Docker\Support\LogDemuxer;
use TheThunderTurner\Docker\Transport;

//  Documentation: https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container
class Containers
{
    public function __construct(private Transport $transport) {}

    /**
     * Returns a list of containers. For details on the format, see the inspect endpoint.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     *
     * @throws ConnectionException
     *
     * @link  https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerList
     */
    public function list(bool $all = false, ?int $limit = null, bool $size = false, array $filters = []): array
    {
        $query = [
            'all' => $all ? 'true' : 'false',
            'size' => $size ? 'true' : 'false',
        ];

        if ($limit !== null) {
            $query['limit'] = (string) $limit;
        }

        if ($filters !== []) {
            $query['filters'] = json_encode($filters);
        }

        return $this->transport->get('/containers/json', $query)->json();
    }

    /**
     * Create a new container.
     *
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerCreate
     */
    public function create(array $config, ?string $name = null, ?string $platform = null): array
    {
        $query = [];

        if ($name !== null) {
            $query['name'] = $name;
        }

        if ($platform !== null) {
            $query['platform'] = $platform;
        }

        return $this->transport->post('/containers/create', $config, $query)->json();
    }

    /**
     * Return low-level information about a container.
     *
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerInspect
     */
    public function inspect(string $id, bool $size = false): array
    {
        return $this->transport->get("/containers/{$id}/json", [
            'size' => $size ? 'true' : 'false',
        ])->json();
    }

    /**
     * On Unix systems, this is done by running the ps command. This endpoint is not supported on Windows.
     *
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerTop
     */
    public function top(string $id, string $psArgs = '-ef'): array
    {
        return $this->transport->get("/containers/{$id}/top", ['ps_args' => $psArgs])->json();
    }

    /**
     * Fetch and demultiplex container logs.
     *
     * @return array{stdout: string, stderr: string}
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerLogs
     */
    public function logs(
        string $id,
        ?bool $follow = false,
        ?bool $stdout = false,
        ?bool $stderr = false,
        ?int $since = null,
        ?int $until = null,
        ?bool $timestamps = false,
        ?string $tail = 'all',
    ): array {
        $query = [
            'follow' => $follow,
            'stdout' => $stdout,
            'stderr' => $stderr,
            'since' => $since,
            'until' => $until,
            'timestamps' => $timestamps,
            'tail' => $tail,
        ];

        $body = $this->transport->get("/containers/{$id}/logs", $query)->body();

        return LogDemuxer::demuxLogs($body);
    }

    /**
     * Returns which files in a container's filesystem have been added, deleted, or modified. The Kind of modification can be one of:
     * 0: Modified ("C")
     * 1: Added ("A")
     * 2: Deleted ("D")
     *
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerChanges
     */
    public function changes(string $id): ?array
    {
        return $this->transport->get("/containers/{$id}/changes")->json();
    }

    /**
     * Export the contents of a container as a tarball.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerChanges
     */
    public function export(string $id): ?array
    {
        return $this->transport->get("/containers/{$id}/export")->json();
    }

    /**
     * This endpoint returns a live stream of a container’s resource usage statistics.
     *
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerStats
     */
    public function stats(string $id, bool $stream = true, bool $oneShot = false): array
    {
        return $this->transport->get("/containers/{$id}/stats", [
            'stream' => $stream,
            'one-shot' => $oneShot,
        ])->json();
    }

    /**
     * Resize the TTY for a container.
     *
     * @throws ConnectionException
     */
    public function resize(string $id, int $height, int $width): array
    {
        return $this->transport->post("/containers/{$id}/resize", [
            'h' => $height,
            'w' => $width,
        ])->json();
    }

    /**
     * Start a container.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerStart
     */
    public function start(string $id, string $detachKeys): void
    {
        $this->transport->post("/containers/{$id}/start", [
            'detachKeys' => $detachKeys,
        ]);
    }

    /**
     * Stop a container.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerStop
     */
    public function stop(string $id, ?string $signal = null, ?int $timeout = null): void
    {
        $query = [];

        if ($signal !== null) {
            $query['signal'] = $signal;
        }

        if ($timeout !== null) {
            $query['t'] = (string) $timeout;
        }

        $this->transport->post("/containers/{$id}/stop", null, $query);
    }

    /**
     * Restart a container.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerRestart
     */
    public function restart(string $id, ?string $signal = null, ?int $timeout = null): void
    {
        $query = [];

        if ($signal !== null) {
            $query['signal'] = $signal;
        }

        if ($timeout !== null) {
            $query['t'] = (string) $timeout;
        }

        $this->transport->post("/containers/{$id}/restart", null, $query);
    }

    /**
     * Send a signal to a container (default SIGKILL).
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerKill
     */
    public function kill(string $id, string $signal = 'SIGKILL'): void
    {
        $this->transport->post("/containers/{$id}/kill", null, ['signal' => $signal]);
    }

    /**
     * Change various configuration options of a container without having to recreate it.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerUpdate
     */
    public function update(string $id, array $config): void
    {
        $this->transport->post("/containers/{$id}/update", $config)->json();
    }

    /**
     * Rename a container.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerRename
     */
    public function rename(string $id, string $name): void
    {
        $this->transport->post("/containers/{$id}/rename", null, ['name' => $name]);
    }

    /**
     * Use the freezer cgroup to suspend all processes in a container.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerPause
     */
    public function pause(string $id): void
    {
        $this->transport->post("/containers/{$id}/pause");
    }

    /**
     * Resume a container which has been paused.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerUnpause
     */
    public function unpause(string $id): void
    {
        $this->transport->post("/containers/{$id}/unpause");
    }

    /**
     * Block until a container stops, then return its exit code.
     *
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerWait
     */
    public function wait(string $id, string $condition = 'not-running'): array
    {
        return $this->transport->post("/containers/{$id}/wait", null, ['condition' => $condition])->json();
    }

    /**
     * Remove a container.
     *
     * @throws ConnectionException
     * @throws DockerException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerDelete
     */
    public function remove(string $id, bool $removeVolumes = false, bool $force = false, bool $link = false): void
    {
        $this->transport->delete("/containers/{$id}", [
            'v' => $removeVolumes ? 'true' : 'false',
            'force' => $force ? 'true' : 'false',
            'link' => $link ? 'true' : 'false',
        ]);
    }

    /**
     * A response header X-Docker-Container-Path-Stat is returned, containing a base64 - encoded JSON object with some filesystem header information about the path.
     *
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws DockerException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerArchiveInfo
     */
    public function archiveInfo(string $id, string $path): array
    {
        $response = $this->transport->head("/containers/{$id}/archive", ['path' => $path]);

        $header = $response->header('X-Docker-Container-Path-Stat');

        if ($header === '') {
            throw new DockerException(
                'Docker did not return X-Docker-Container-Path-Stat header'
            );
        }

        $decoded = base64_decode($header, true);

        if ($decoded === false) {
            throw new DockerException(
                'Failed to base64-decode X-Docker-Container-Path-Stat header'
            );
        }

        $parsed = json_decode($decoded, true);

        if (! is_array($parsed)) {
            throw new DockerException(
                'Failed to JSON-decode X-Docker-Container-Path-Stat header'
            );
        }

        return $parsed;
    }

    /**
     * Get a tar archive of a resource in the filesystem of container id.
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/ContainerArchive
     */
    public function getArchive(string $id, string $path): string
    {
        return $this->transport
            ->get("/containers/{$id}/archive", ['path' => $path])
            ->body();
    }

    /**
     * Upload a tar archive to be extracted to a path in the filesystem of container id. path parameter is asserted to be a directory.
     * If it exists as a file, 400 error will be returned with message "not a directory".
     *
     * @throws ConnectionException
     *
     * @link https://docs.docker.com/reference/api/engine/version/v1.54/#tag/Container/operation/PutContainerArchive
     */
    public function putArchive(
        string $id,
        string $path,
        string $tarball,
        bool $noOverwriteDirNonDir = false,
        bool $copyUidGid = false,
    ): void {
        $query = [
            'path' => $path,
            'noOverwriteDirNonDir' => $noOverwriteDirNonDir ? 'true' : 'false',
            'copyUIDGID' => $copyUidGid ? 'true' : 'false',
        ];

        $this->transport->put("/containers/{$id}/archive", $tarball, [], $query);
    }

    /**
     * Remove all stopped containers.
     *
     * @param  array<string, mixed>  $filters
     *
     * @throws ConnectionException
     * @throws DockerException
     */
    public function prune(array $filters = []): array
    {
        $query = $filters !== [] ? ['filters' => json_encode($filters)] : [];

        return $this->transport->post('/containers/prune', null, $query)->json();
    }
}

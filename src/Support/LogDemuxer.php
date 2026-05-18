<?php

namespace TheThunderTurner\Docker\Support;

class LogDemuxer
{
    /**
     * Parse Docker's multiplexed log stream format.
     * Each frame: [stream_type(1)][padding(3)][size(4 big-endian)][payload(size)]
     *
     * @return array{stdout: string, stderr: string}
     */
    public static function demuxLogs(string $raw): array
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

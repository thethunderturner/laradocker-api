<?php

/**
 * Build a minimal ustar-format tarball containing exactly one regular file.
 *
 * This is enough for putArchive() integration tests. No external `tar` binary needed.
 */
function buildTar(string $filename, string $content): string
{
    $header = str_repeat("\x00", 512);

    // Name (100 bytes)
    $name = $filename;
    for ($i = 0; $i < min(strlen($name), 100); $i++) {
        $header[$i] = $name[$i];
    }

    // Mode (8 bytes) — 0644
    $mode = sprintf('%07o', 0o644);
    for ($i = 0; $i < 7; $i++) {
        $header[100 + $i] = $mode[$i];
    }

    // UID / GID (8 bytes each)
    $uid = sprintf('%07o', 0);
    $gid = sprintf('%07o', 0);
    for ($i = 0; $i < 7; $i++) {
        $header[108 + $i] = $uid[$i];
        $header[116 + $i] = $gid[$i];
    }

    // Size (12 bytes, octal)
    $size = sprintf('%011o', strlen($content));
    for ($i = 0; $i < 11; $i++) {
        $header[124 + $i] = $size[$i];
    }

    // Mtime (12 bytes)
    $mtime = sprintf('%011o', time());
    for ($i = 0; $i < 11; $i++) {
        $header[136 + $i] = $mtime[$i];
    }

    // Type flag (1 byte) — '0' for regular file
    $header[156] = '0';

    // Magic + version for ustar
    $magic = 'ustar';
    $version = '00';
    for ($i = 0; $i < 5; $i++) {
        $header[257 + $i] = $magic[$i];
    }
    $header[263] = $version[0];
    $header[264] = $version[1];

    // Calculate checksum — treat checksum field (bytes 148-155) as spaces.
    for ($i = 0; $i < 8; $i++) {
        $header[148 + $i] = ' ';
    }

    $sum = 0;
    for ($i = 0; $i < 512; $i++) {
        $sum += ord($header[$i]);
    }

    $checksum = sprintf('%06o', $sum)."\x00 ";
    for ($i = 0; $i < 8; $i++) {
        $header[148 + $i] = $checksum[$i];
    }

    // Pad content to 512-byte boundary.
    $paddedContent = $content.str_repeat("\x00", (512 - (strlen($content) % 512)) % 512);

    // Two null blocks mark end of archive.
    return $header.$paddedContent.str_repeat("\x00", 1024);
}

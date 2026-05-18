<?php

namespace TheThunderTurner\Docker\Commands;

use Illuminate\Console\Command;

class DockerCommand extends Command
{
    public $signature = 'laradocker-api';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}

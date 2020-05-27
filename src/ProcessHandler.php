<?php

namespace EDouna\LaravelDBBackup;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ProcessHandler
{
    /**
     * @param string $command
     * @return boolean
     */
    public function run(string $command): bool
    {
        $process = Process::fromShellCommandline($command, null, null, null, 999.00);
        $process->run();

        if ($process->getExitCode() !== 0) {
            Log::error(sprintf('Failure in the processor. Please verify if the command is recognized. Exit code text returned: "%s". Error output: %s', $process->getExitCodeText(), $process->getErrorOutput()));
            return false;
        }

        return true;
    }

    /**
     * @param array $command
     * @return bool
     */
    public static function runArray(array $command): bool
    {
        $process = new Process($command);
        $process->run();

        if ($process->getExitCode() !== 0) {
            Log::error(sprintf('Failure in the processor. Please verify if the command is recognized. Exit code text returned: "%s". Error output: %s', $process->getExitCodeText(), $process->getErrorOutput()));
            return false;
        }

        return true;
    }
}

<?php

namespace EDouna\LaravelDBBackup;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ProcessHandler
{
   /**
    * Run the Symfony fromShellCommandLine
    *
    * @param string $command
    * @return boolean
    */
    public function run(string $command): bool
    {
        $process = Process::fromShellCommandline($command, null, null, null, 999.00);

        $processStatus = false;
        $process->run(function ($type, $buffer) use ($processStatus): bool {
            if (Process::OUT === $type) {
                Log::debug('Process buffer: ' . $buffer);
            }
            if (Process::ERR === $type) {
                if (!strpos($buffer, '[Warning]')) {
                    Log::error('Error will running processor. Output of buffer: ' . $buffer);
                    $processStatus = true;
                }
            }

            return $processStatus;
        });

        return $processStatus;
    }
}

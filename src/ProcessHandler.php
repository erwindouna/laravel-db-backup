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

        $processStatus = true;
        $process->run(function ($type, $buffer) use ($processStatus): bool {
            if (Process::OUT === $type) {
                Log::debug('Process buffer: ' . $buffer);
            }
            if (Process::ERR === $type) {
                if (!strpos($buffer, '[Warning]')) {
                    Log::error('Error will running processor. Output of buffer: ' . $buffer);
                    $processStatus = false;
                }
            }

            return $processStatus;
        });

        return $processStatus;
    }

    /**
     * @param array $command
     * @return bool
     */
    public static function runArray(array $command): bool
    {
        $process = new Process($command);

        $processStatus = true;
        $process->run(function ($type, $buffer) use ($processStatus): bool {
            if (Process::OUT === $type) {
                Log::debug('Success buffer: ' . $buffer);
            }
            if (Process::ERR === $type) {
                Log::error('Error whilst performing zip action. Output of buffer: ' . $buffer);
                $processStatus = false;
            }

            return $processStatus;
        });

        return $processStatus;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\Parser\Parser;
use Illuminate\Console\Command;

class StartParsingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a parser';

    public function handle(Parser $parser)
    {
        $parser->parseAllSources();
    }
}

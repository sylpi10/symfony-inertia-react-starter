<?php

declare(strict_types=1);

namespace App\Command;

use Spatie\TypeScriptTransformer\Enums\RunnerMode;
use Spatie\TypeScriptTransformer\Runners\Runner;
use Spatie\TypeScriptTransformer\Support\Loggers\SymfonyConsoleLogger;
use Spatie\TypeScriptTransformer\Transformers\AttributedClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\When;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;

#[When(env: "dev")]
#[
    AsCommand(
        name: "app:typescript:generate",
        description: "Generate TypeScript types from PHP classes marked with #[TypeScript]",
    ),
]
final class GenerateTypeScriptCommand extends Command
{
    public function __construct(
        #[Autowire("%kernel.project_dir%")] private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $config = TypeScriptTransformerConfigFactory::create()
            ->transformer(
                new EnumTransformer(),
                AttributedClassTransformer::class,
            )
            ->transformDirectories($this->projectDir . "/src")
            ->writer(new FlatModuleWriter("generated.ts"))
            ->formatter(PrettierFormatter::class)
            ->outputDirectory($this->projectDir . "/assets/types")
            ->withoutManifest()
            ->get();

        return new Runner()->run(
            new SymfonyConsoleLogger($output),
            $config,
            RunnerMode::Direct,
        );
    }
}

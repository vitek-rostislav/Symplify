<?php declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Init\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class InitCommand extends Command
{
    /**
     * @var string
     */
    private const OUTPUT = 'output';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(Filesystem $filesystem, SymfonyStyle $symfonyStyle)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->symfonyStyle = $symfonyStyle;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->addArgument(self::OUTPUT, InputArgument::REQUIRED, 'Directory to generate monorepo into.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output = $input->getArgument(self::OUTPUT);

        $this->filesystem->copy(__DIR__ . '/../../templates/monorepo', $output);

        $this->symfonyStyle->success('Congrats! Your first monorepo is here:' . $output);

        $this->symfonyStyle->note(sprintf(
            'Now try the next step - mergin package composer.json to root one: 
              "vendor/bin/monorepo-builder merge --monorepo %s"',
            $output
        ));
    }
}

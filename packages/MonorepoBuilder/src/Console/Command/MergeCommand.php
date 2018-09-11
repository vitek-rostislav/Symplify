<?php declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\MonorepoBuilder\Console\Reporter\ConflictingPackageVersionsReporter;
use Symplify\MonorepoBuilder\DependenciesMerger;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use Symplify\MonorepoBuilder\Package\PackageComposerJsonMerger;
use Symplify\MonorepoBuilder\VersionValidator;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class MergeCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var PackageComposerJsonMerger
     */
    private $packageComposerJsonMerger;

    /**
     * @var string[]
     */
    private $mergeSections = [];

    /**
     * @var DependenciesMerger
     */
    private $dependenciesMerger;

    /**
     * @var VersionValidator
     */
    private $versionValidator;

    /**
     * @var ComposerJsonProvider
     */
    private $composerJsonProvider;

    /**
     * @var ConflictingPackageVersionsReporter
     */
    private $conflictingPackageVersionsReporter;

    /**
     * @param string[] $mergeSections
     */
    public function __construct(
        array $mergeSections,
        SymfonyStyle $symfonyStyle,
        PackageComposerJsonMerger $packageComposerJsonMerger,
        DependenciesMerger $dependenciesMerger,
        VersionValidator $versionValidator,
        ComposerJsonProvider $composerJsonProvider,
        ConflictingPackageVersionsReporter $conflictingPackageVersionsReporter
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->packageComposerJsonMerger = $packageComposerJsonMerger;
        $this->dependenciesMerger = $dependenciesMerger;
        $this->mergeSections = $mergeSections;
        $this->versionValidator = $versionValidator;
        $this->composerJsonProvider = $composerJsonProvider;

        $this->conflictingPackageVersionsReporter = $conflictingPackageVersionsReporter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Merge "composer.json" from all found packages to root one');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conflictingPackageVersions = $this->versionValidator->findConflictingPackageVersionsInFileInfos(
            $this->composerJsonProvider->getRootAndPackageFileInfos()
        );
        if (count($conflictingPackageVersions) > 0) {
            $this->conflictingPackageVersionsReporter->report($conflictingPackageVersions);

            // fail
            return 1;
        }

        $merged = $this->packageComposerJsonMerger->mergeFileInfos(
            $this->composerJsonProvider->getPackagesFileInfos(),
            $this->mergeSections
        );

        if ($merged === []) {
            $this->symfonyStyle->note('Nothing to merge.');
            // success
            return 0;
        }

        $this->dependenciesMerger->mergeJsonToRootFilePathAndSave($merged, getcwd() . '/composer.json');

        $this->symfonyStyle->success('Main "composer.json" was updated.');

        // success
        return 0;
    }
}

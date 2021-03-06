<?php declare(strict_types=1);

namespace Symplify\ChangelogLinker\ChangeTree;

use Nette\Utils\Strings;
use Symplify\ChangelogLinker\ChangeTree\Resolver\CategoryResolver;
use Symplify\ChangelogLinker\ChangeTree\Resolver\PackageResolver;
use Symplify\ChangelogLinker\Git\GitCommitDateTagResolver;

final class ChangeFactory
{
    /**
     * @var GitCommitDateTagResolver
     */
    private $gitCommitDateTagResolver;

    /**
     * @var string[]
     */
    private $authorsToIgnore = [];

    /**
     * @var CategoryResolver
     */
    private $categoryResolver;

    /**
     * @var PackageResolver
     */
    private $packageResolver;

    /**
     * @param string[] $authorsToIgnore
     */
    public function __construct(
        GitCommitDateTagResolver $gitCommitDateTagResolver,
        CategoryResolver $categoryResolver,
        PackageResolver $packageResolver,
        array $authorsToIgnore
    ) {
        $this->gitCommitDateTagResolver = $gitCommitDateTagResolver;
        $this->categoryResolver = $categoryResolver;
        $this->authorsToIgnore = $authorsToIgnore;
        $this->packageResolver = $packageResolver;
    }

    /**
     * @param mixed[] $pullRequest
     */
    public function createFromPullRequest(array $pullRequest): Change
    {
        $message = sprintf('- [#%s] %s', $pullRequest['number'], trim($pullRequest['title']));

        $author = $pullRequest['user']['login'] ?? '';

        // skip the main maintainer to prevent self-thanking floods
        if ($author && ! in_array($author, $this->authorsToIgnore, true)) {
            $message .= ', Thanks to @' . $author;
        }

        $category = $this->categoryResolver->resolveCategory($pullRequest['title']);
        $package = $this->packageResolver->resolvePackage($pullRequest['title']);
        $messageWithoutPackage = $this->resolveMessageWithoutPackage($message);

        // @todo 'merge_commit_sha' || 'head'
        $pullRequestTag = $this->gitCommitDateTagResolver->resolveCommitToTag($pullRequest['merge_commit_sha']);

        return new Change($message, $category, $package, $messageWithoutPackage, $pullRequestTag);
    }

    private function resolveMessageWithoutPackage(string $message): string
    {
        $match = Strings::match($message, '#\[(?<package>\w+)\]#');

        if (! isset($match['package'])) {
            return $message;
        }

        return Strings::replace($message, '#\[' . $match['package'] . '\]\s+#');
    }
}

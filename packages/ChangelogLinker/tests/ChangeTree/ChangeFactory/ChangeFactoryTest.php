<?php declare(strict_types=1);

namespace Symplify\ChangelogLinker\Tests\ChangeTree\ChangeFactory;

final class ChangeFactoryTest extends AbstractChangeFactoryTest
{
    public function testeEgoTag(): void
    {
        $pullRequest = [
            'number' => 10,
            'title' => 'Add cool feature',
            'user' => [
                'login' => 'me',
            ],
            'merge_commit_sha' => 'random',
        ];

        $change = $this->changeFactory->createFromPullRequest($pullRequest);
        $this->assertSame('- [#10] Add cool feature, Thanks to @me', $change->getMessage());

        $pullRequest = [
            'number' => 10,
            'title' => 'Add cool feature',
            'user' => [
                'login' => 'ego',
            ],
            'merge_commit_sha' => 'random',
        ];

        $change = $this->changeFactory->createFromPullRequest($pullRequest);
        $this->assertSame('- [#10] Add cool feature', $change->getMessage());
    }

    public function testGetMessageWithoutPackage(): void
    {
        $pullRequest = [
            'number' => 10,
            'title' => '[SomePackage] SomeMessage',
            'merge_commit_sha' => 'random',
        ];

        $change = $this->changeFactory->createFromPullRequest($pullRequest);

        $this->assertSame('- [#10] [SomePackage] SomeMessage', $change->getMessage());
        $this->assertSame('- [#10] SomeMessage', $change->getMessageWithoutPackage());
    }

    public function testTagDetection(): void
    {
        if (! defined('SYMPLIFY_MONOREPO')) {
            $this->markTestSkipped('Can be tested only with merge commit in monorepo, not in split where are no PRs.');
        }

        $pullRequest = [
            'number' => 10,
            'title' => '[SomePackage] SomeMessage',
            'merge_commit_sha' => '58f3eea3a043998e272e70079bccb46fac10e4ad',
        ];
        $change = $this->changeFactory->createFromPullRequest($pullRequest);

        $this->assertSame('v4.2.0', $change->getTag());
    }
}

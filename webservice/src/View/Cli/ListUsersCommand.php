<?php

declare(strict_types=1);

namespace App\View\Cli;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\UserManagement\Query\ListUsers;
use App\Core\UserManagement\ReadModel\UserListEntry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function count;

#[AsCommand(name: 'app:user:list', description: 'List users including their local/SSO login state')]
final class ListUsersCommand extends Command
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Users');

        $users = iterator_to_array($this->queryBus->get(ListUsers::withoutFilter()));

        if (0 === count($users)) {
            $io->info('No users found');

            return Command::SUCCESS;
        }

        $io->table(
            ['Username', 'Display Name', 'Roles', 'Login Type'],
            array_map(fn (UserListEntry $u): array => [
                $u->username,
                $u->displayname ?? '',
                implode(', ', $u->roles),
                $this->loginType($u),
            ], $users)
        );

        return Command::SUCCESS;
    }

    private function loginType(UserListEntry $user): string
    {
        $sso = null !== $user->externalId && '' !== $user->externalId;

        return match (true) {
            $sso && $user->hasPassword => 'SSO + Local',
            $sso => 'SSO',
            $user->hasPassword => 'Local',
            default => 'None',
        };
    }
}

<?php

namespace Modules\User\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Validator;
use Modules\Access\Api\AccessApiInterface;
use Modules\User\Api\UserApiInterface;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an administrator user (interactive; intended for initial setup)';

    public function __construct(
        private readonly UserApiInterface $userApi,
        private readonly AccessApiInterface $accessApi,
        private readonly Hasher $hasher,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->input->isInteractive()) {
            $this->error('This command prompts for credentials and cannot run non-interactively.');

            return CommandAlias::FAILURE;
        }

        $name = $this->askFor('name', fn() => $this->ask('Name'), ['required', 'string', 'max:255']);
        $email = $this->askFor('email', fn() => $this->ask('Email'), ['required', 'email', 'unique:users,email']);
        $password = $this->askForPassword();

        $user = $this->userApi->createUser([
            'name' => $name,
            'email' => $email,
            'password' => $this->hasher->make($password),
        ]);

        $granted = [];
        foreach ((array)config('permissions') as $permissionKey) {
            $this->accessApi->grantPermission($user->getId(), $permissionKey);
            $granted[] = $permissionKey;
        }

        $this->info(sprintf('Administrator #%d created: %s', $user->getId(), $user->getEmail()));
        $this->line('Permissions: ' . (empty($granted) ? '(none configured)' : implode(', ', $granted)));

        return CommandAlias::SUCCESS;
    }

    /**
     * Asks for a single value until it validates.
     *
     * @param  string  $field
     * @param  callable(): ?string  $ask
     * @param  array<int, string>  $rules
     * @return string
     */
    private function askFor(string $field, callable $ask, array $rules): string
    {
        while (true) {
            $value = $ask();
            $validator = Validator::make([$field => $value], [$field => $rules]);

            if ($validator->passes()) {
                return $value;
            }

            foreach ($validator->errors()->get($field) as $message) {
                $this->error($message);
            }
        }
    }

    /**
     * Asks for the password twice, hiding the input.
     *
     * @return string
     */
    private function askForPassword(): string
    {
        while (true) {
            $password = $this->askFor(
                'password',
                fn() => $this->secret('Password (min. 8 characters, hidden)'),
                ['required', 'string', 'min:8']
            );

            if ($password === $this->secret('Confirm password')) {
                return $password;
            }

            $this->error('The passwords do not match.');
        }
    }
}

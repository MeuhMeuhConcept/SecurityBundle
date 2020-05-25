<?php

namespace Mmc\Security\Command;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Entity\Enum\AuthType;
use Mmc\Security\Entity\User;
use Mmc\Security\Entity\UserAuth;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAnonymousUserCommand extends Command
{
    protected static $defaultName = 'mmc:security:create-anonymous-user';

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new user for anonymous connection.')
            ->setHelp('This command allows you to create a user for anonymous connection.')
            ->addArgument('key', InputArgument::REQUIRED, 'The authentication key for this anonymous user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument('key');

        $auth = $this->em->getRepository(UserAuth::class)->findOneBy([
            'type' => AuthType::ANONYMOUS,
            'key' => $key,
        ]);

        if ($auth) {
            throw new \Exception('This key with anonymous authentication already exists.');
        }

        $user = new User();
        $auth = new UserAuth();
        $auth->setUser($user)
            ->setType(AuthType::ANONYMOUS)
            ->setKey($key);

        $this->em->persist($user);
        $this->em->persist($auth);
        $this->em->flush();

        return 0;
    }
}

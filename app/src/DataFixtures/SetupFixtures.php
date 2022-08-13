<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserRole;
use App\Service\Log\LogSystemService;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class SetupFixtures extends Fixture implements FixtureGroupInterface
{
    private LogSystemService $log;

    public static function getGroups(): array
    {
        return ['setup'];
    }

    /**
     * @var UserPasswordHasherInterface
     */
    private $passwordHasher;

    public function __construct(LogSystemService $log, UserPasswordHasherInterface $passwordHasher, ContainerBagInterface $params)
    {
        $this->log = $log;
        $this->passwordHasher = $passwordHasher;
        $this->params = $params;
    }
    public function load(ObjectManager $manager)
    {
        $this->loadDefaultAdminUser($manager);
        $this->loadDefaultUserRoles($manager);
        $this->log->fixtures('setup', 'SetupFixtures loaded', true);
    }

    public function loadDefaultAdminUser(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setEmail('admin@admin.com');
        $user->setTimezone($this->params->get('app')['timezone']); //Load global Timezone
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                'admin'
            )
        );
        $user->setIsVerified(true);
        $user->setActive(true);
        #$user->setPid();
        $user->setRoles(['ROLE_SUPER_ADMIN']);

        $manager->persist($user);
        $manager->flush();
    }

    public function loadDefaultUserRoles(ObjectManager $manager)
    {
        $roles = [  'Super Admin' => 'ROLE_SUPER_ADMIN',
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER'];

        foreach ($roles as $k => $v) {
            $role = new Userrole();
            $role->setRole($v);
            $role->setName($k);
            $role->setDescription($k.' (System Role)');
            $role->setSystemrole(true);
            $manager->persist($role);
        }

        $role_test = new Userrole();
        $role_test->setRole('ROLE_TEST');
        $role_test->setName('ROLE_TEST');
        $role_test->setDescription('Testrole');
        $role_test->setSystemrole(false);
        $role_test->setParentRole($role);
        $manager->persist($role_test);

        $role_test2 = new Userrole();
        $role_test2->setRole('ROLE_TEST2');
        $role_test2->setName('ROLE_TEST2');
        $role_test2->setDescription('Testrole 2');
        $role_test2->setSystemrole(false);
        $role_test2->setParentRole($role_test);
        $manager->persist($role_test2);

        $manager->flush();
    }
}

<?php

namespace App\Repository;

use App\Entity\UserRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserRole>
 *
 * @method UserRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRole[]    findAll()
 * @method UserRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRole::class);
    }

    public function findAllActive(){
        return $this->findBy( array('isActive' => 1, 'deletedAt' => null), null );
    }

    public function getRoleAndParents(string $role): Array{
        $role = $this->createQueryBuilder('r')
                    ->andWhere('r.role = :role')->andWhere('r.isActive = 1')->andWhere('r.deletedAt IS NULL')
                    ->setParameter('role', $role)
                    ->getQuery()->getResult();
 
        if ($role[0]){
            $return = $role[0]->getRoleAndParents();
        }else{
            $this->addFlash('danger', $role.' is not active anymore , and was removed');
            $return = [];   
        }
        return $return;
    }

    public function getAllRolesToSave(Array $roles){

        $rolesToSave = [];
        foreach($roles as $r){
            $rolesToSave = array_merge($rolesToSave, $this->getRoleAndParents($r));
        }

        return array_filter(array_unique($rolesToSave));
    }

}

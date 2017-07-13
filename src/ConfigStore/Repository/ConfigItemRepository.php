<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Repository;

use ConfigStore\Model\AppGroup;
use Doctrine\ORM\EntityRepository;

/**
 * Class ConfigItemRepository
 *
 * @package \ConfigStore\Repository
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class ConfigItemRepository extends EntityRepository
{
    /**
     * @param string   $configKey
     * @param AppGroup $group
     *
     * @return array
     */
    public function findCorrespondingConfigItemsFromGroupApps($configKey, AppGroup $group)
    {
        return $this->createQueryBuilder('ci')
            ->leftJoin('ci.app', 'a')
            ->where('a.group = :appGroup')
            ->andWhere('ci.key = :key')
            ->getQuery()
            ->setParameter('appGroup', $group)
            ->setParameter('key', $configKey)
            ->getResult();
    }
}

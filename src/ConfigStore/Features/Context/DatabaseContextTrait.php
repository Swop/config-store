<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Features\Context;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

trait DatabaseContextTrait
{
    /**
     * Gets the appropriate object manager
     *
     * @param string $managerName
     *
     * @return EntityManager
     */
    abstract protected function getObjectManager($managerName = null);

    /**
     * Gets a registered database connection by name
     *
     * @param string $name  The connection name
     * @return Connection
     */
    abstract protected function getDatabaseConnection($name);

    /**
     * Gets the registered database connections
     *
     * @return array
     */
    abstract protected function getDatabaseConnections();

    /**
     * Checks if the given database connexion name is read-only (no purge action will be made)
     *
     * @param string $connexionName
     *
     * @return bool
     */
    abstract protected function isConnectionReadOnly($connexionName);

    /**
     * Checks if the given table name is read-only (no purge action will be made)
     *
     * @param string $connexionName
     * @param string $tableName
     *
     * @return bool
     */
    abstract protected function isTableReadOnly($connexionName, $tableName);

    /**
     * Clear data for a given connection name
     *
     * @param string  $connectionName The database connection name
     */
    public function resetDatabase($connectionName = 'default')
    {
        if ($this->isConnectionReadOnly($connectionName)) {
            return;
        }

        /** @var Connection $connection */
        $connection = $this->getDatabaseConnection($connectionName);

        $dbPlatform = $connection->getDatabasePlatform();

        foreach ($connection->getSchemaManager()->listTables() as $table) {

            if ($this->isTableReadOnly($connectionName, $table->getName())) {
                continue;
            }

            $connection->query('SET FOREIGN_KEY_CHECKS=0');

            $q = $dbPlatform->getTruncateTableSql($table->getName());
            $connection->executeUpdate($q);

            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}

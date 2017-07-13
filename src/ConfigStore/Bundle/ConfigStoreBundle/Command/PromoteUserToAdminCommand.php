<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Command;

use ConfigStore\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PromoteToAdminCommand
 *
 * @package \ConfigStore\Bundle\ConfigStoreBundle\Command
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class PromoteUserToAdminCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('user:promote-to-admin')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username')
            ))
            ->setDescription('Promotes a user to admin')
            ->setHelp(<<<EOT
The <info>user:promote-to-admin</info> command promotes a user to admin.

  <info>php app/console user:promote-to-admin matthieu</info>
EOT
            );

    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');

        /** @var UserManager $userManager */
        $userManager = $this->getContainer()->get('config_store.manager.user');

        $userManager->promoteToAdmin($userManager->getByUsernameOrEmail($username));

        $output->writeln(sprintf('User "%s" has been promoted as an administrator.', $username));
    }

    /**
     * {@inheritDoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a username:',
                function ($username) {
                    if (empty($username)) {
                        throw new \Exception('Username can not be empty');
                    }

                    return $username;
                }
            );
            $input->setArgument('username', $username);
        }
    }
}

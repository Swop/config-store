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
 * Class DemoteUserCommand
 *
 * @package \ConfigStore\Bundle\ConfigStoreBundle\Command
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class DemoteUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('user:demote')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username')
            ))
            ->setDescription('Demote a user')
            ->setHelp(<<<EOT
The <info>user:demote</info> command demotes a user from its admin role.

  <info>php app/console user:demote matthieu</info>
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

        $userManager->demote($userManager->getByUsernameOrEmail($username));

        $output->writeln(sprintf('User "%s" has been demoted as a simple user.', $username));
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

<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Command;

use ConfigStore\Exception\UnknownUserException;
use ConfigStore\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ActivateUserCommand
 *
 * @package ConfigStore\ConfigStore\Bundle\ConfigStoreBundle\Command
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class ActivateUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('user:activate')
            ->setDescription('Activate a user')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
            ))
            ->setHelp(<<<EOT
The <info>user:activate</info> command activates a user (so they will be able to log in):

  <info>php app/console user:activate matthieu</info>
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

        $userManager->activate($userManager->getByUsernameOrEmail($username));

        $output->writeln(sprintf('User "%s" has been activated.', $username));
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

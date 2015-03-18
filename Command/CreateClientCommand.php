<?php

namespace OAuth2\ServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony Command for create a client Oauth
 *
 * Usage:
 *  OAuth2:CreateClient [--public] identifier [redirect_uri] [grant_types] [scopes]
 *
 * Arguments:
 *     identifier            The client identifier
 *     redirect_uri          The client redirect uris (comma separated)
 *     grant_types           Grant types to restrict the client to (comma separated)
 *     scopes                Scopes to restrict the client to (comma separated)
 *
 * Options:
 *     --public              If client is public, no client secret is generated
 *
 * @example
 * app/console OAuth2:CreateClient web-app http://wwww.web-app.fr/redirect --public
 */
class CreateClientCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('OAuth2:CreateClient')
            ->setDescription('Create a OAuth2 client')
            ->addArgument('identifier', InputArgument::REQUIRED, 'The client identifier')
            ->addArgument('redirect_uri', InputArgument::OPTIONAL, 'The client redirect uris (comma separated)')
            ->addArgument('grant_types', InputArgument::OPTIONAL, 'Grant types to restrict the client to (comma separated)')
            ->addArgument('scopes', InputArgument::OPTIONAL, 'Scopes to restrict the client to (comma separated)')
            ->addOption(
                'public',
                null,
                InputOption::VALUE_NONE,
                'If client is public, no client secret is generated'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $clientManager = $container->get('oauth2.client_manager');

        $isPublic = ($input->getOption('public'));

        try {
            $client = $clientManager->createClient(
                $input->getArgument('identifier'),
                explode(',', $input->getArgument('redirect_uri')),
                explode(',', $input->getArgument('grant_types')),
                explode(',', $input->getArgument('scopes')),
                $isPublic
            );
        } catch (\Doctrine\DBAL\DBALException $e) {
            $output->writeln('<fg=red>Unable to create client '.$input->getArgument('identifier').'</fg=red>');
            $output->writeln('<fg=red>'.$e->getMessage().'</fg=red>');

            return 1;
        } catch (\OAuth2\ServerBundle\Exception\ScopeNotFoundException $e) {
            $output->writeln('<fg=red>Scope not found, please create it first</fg=red>');

            return 1;
        }

        if ($isPublic) {
            $output->writeln('<fg=green>Client '.$input->getArgument('identifier').' created</fg=green>');
        } else {
            $output->writeln('<fg=green>Client '.$input->getArgument('identifier').' created with secret '.$client->getClientSecret().'</fg=green>');
        }
    }
}

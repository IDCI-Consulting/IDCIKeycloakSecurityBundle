<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportKeycloakRealmCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('keycloak:import:realm')
            ->setDescription('Import keycloak realm')
            ->setHelp('Import a keycloak realm according to a json file.')
            ->addArgument('realm', InputArgument::REQUIRED, 'The realm json file path or string json')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command.
Here is an example:

# Import a keycloak realm
<info>php bin/console %command.name% path/to/realm-export.json</info>
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = [
            'realm' => $input->getArgument('realm'),
        ];

        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'realm' => null,
            ])
            ->setAllowedTypes('realm', ['string'])
            ->setNormalizer('realm', function (Options $options, $value): ?array {
                if (file_exists($value)) {
                    $value = json_decode(file_get_contents($value), true);
                }

                if (is_string($value)) {
                    $value = json_decode($value, true);
                }

                return $value;
            })
        ;

        try {
            $options = $resolver->resolve($options);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Invalid arguments: %s</error>', $e->getMessage()));

            return -1;
        }

        $client = new Client([
            'base_url' => 'http://keycloak.maier.docker:8080/auth',
        ]);

        $response = $client->request('POST', '/realms/Maier/protocol/openid-connect/token', [
            'form_params' => [
                'username' => 'admin',
                'password' => 'admin',
                'grant_type' => 'password',
                'client_id' => 'product-manager',
                'client_secret' => 'df90e11a-608e-427f-b6db-92642bc5ca19',
            ],
        ]);

        dump($response);

        $response = $client->request('POST', '/admin/realms', [
            'json' => $options['realm'],
        ]);

        dump($response);
        die();

        return 0;
    }
}

<?php

namespace OAuth2\ServerBundle\Manager;

use Doctrine\ORM\EntityManager;
use OAuth2\ServerBundle\Exception\ScopeNotFoundException;

/**
 * Client Manager Class
 */
class ClientManager
{
    private $em;

    /**
     * @var ScopeManagerInterface
     */
    private $sm;

    /**
     * Constructor
     *
     * @param EntityManager         $entityManager
     * @param ScopeManagerInterface $scopeManager
     */
    public function __construct(EntityManager $entityManager, ScopeManagerInterface $scopeManager)
    {
        $this->em = $entityManager;
        $this->sm = $scopeManager;
    }

    /**
     * Creates a new client
     *
     * @param string $identifier
     * @param array  $redirectUris
     * @param array  $grantTypes
     * @param array  $scopes
     * @param bool   $isPublic
     *
     * @return Client
     */
    public function createClient($identifier, array $redirectUris = array(), array $grantTypes = array(), array $scopes = array(), $isPublic = false)
    {
        $client = new \OAuth2\ServerBundle\Entity\Client();
        $client->setClientId($identifier);
        if (!$isPublic) {
            $client->setClientSecret($this->generateSecret());
        }
        $client->setRedirectUri($redirectUris);
        $client->setGrantTypes($grantTypes);

        if (!$scopes) {
            // Verify scopes
            foreach ($scopes as $scope) {
                // Get Scope
                $scopeObject = $this->sm->findScopeByScope($scope);
                if (!$scopeObject) {
                    throw new ScopeNotFoundException();
                }
            }
        }

        $client->setScopes($scopes);

        // Store Client
        $this->em->persist($client);
        $this->em->flush();

        return $client;
    }

    /**
     * Creates a secret for a client
     *
     * @return A secret
     */
    protected function generateSecret()
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }
}

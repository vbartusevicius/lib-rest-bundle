<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Tests\Functional\Fixtures\FixtureTestBundle\Controller;

use Maba\Bundle\RestBundle\Annotation\PathAttribute;
use Maba\Bundle\RestBundle\Tests\Functional\Fixtures\FixtureTestBundle\Entity\PersistedEntity;
use Maba\Bundle\RestBundle\Tests\Functional\Fixtures\FixtureTestBundle\Entity\SimplePersistedEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersistedEntityController
{
    /**
     * @Route(path="/persisted-entities/{identifier}", methods={"GET"})
     *
     * @PathAttribute(parameterName="entity", pathPartName="identifier")
     *
     * @param PersistedEntity $entity
     * @return Response
     */
    public function findPersistedEntity(PersistedEntity $entity)
    {
        return new Response($entity->getId());
    }

    /**
     * @Route(path="/simple-persisted-entities/{identifier}", methods={"GET"})
     *
     * @PathAttribute(parameterName="entity", pathPartName="identifier")
     *
     * @param SimplePersistedEntity $entity
     * @return Response
     */
    public function findSimplePersistedEntity(SimplePersistedEntity $entity)
    {
        return new Response($entity->getId());
    }
}

<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

class TimestampableTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return array(
            'BehaviorFixtures\\ORM\\TimestampableEntity'
        );
    }

    protected function getEventManager()
    {
        $em = new EventManager;

        $em->addEventSubscriber(new \Knp\DoctrineBehaviors\ORM\Timestampable\TimestampableListener);

        return $em;
    }

    public function testCreate()
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TimestampableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertInstanceOf('Datetime', $entity->getCreatedAt());
        $this->assertInstanceOf('Datetime', $entity->getUpdatedAt());

        $this->assertEquals(
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
            'On creation, createdAt and updatedAt are the same'
        );
    }

    public function testUpdate()
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TimestampableEntity();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $createdAt = $entity->getCreatedAt();
        $em->clear();

        // TODO avoid this. maybe by storing microseconds ? :)
        sleep(1); // needed to make diverge createdAt and updatedAt

        $entity = $em->getRepository('BehaviorFixtures\ORM\TimestampableEntity')->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $em->flush();
        $em->clear();

        $entity = $em->getRepository('BehaviorFixtures\ORM\TimestampableEntity')->find($id);
        $this->assertEquals($createdAt, $entity->getCreatedAt(), 'createdAt is constant');

        $this->assertNotEquals(
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
            'createat and updatedAt have diverged since new update'
        );
    }
}
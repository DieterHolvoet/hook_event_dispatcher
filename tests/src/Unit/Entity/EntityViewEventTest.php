<?php

namespace Drupal\Tests\hook_event_dispatcher\Unit\Entity;

use Drupal;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\hook_event_dispatcher\Event\Entity\EntityViewEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\Tests\hook_event_dispatcher\Unit\HookEventDispatcherManagerSpy;
use Drupal\Tests\UnitTestCase;
use function hook_event_dispatcher_entity_view;

/**
 * Class EntityViewEventTest.
 */
final class EntityViewEventTest extends UnitTestCase {

  /**
   * The manager.
   *
   * @var \Drupal\Tests\hook_event_dispatcher\Unit\HookEventDispatcherManagerSpy
   */
  private $manager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $builder = new ContainerBuilder();
    $this->manager = new HookEventDispatcherManagerSpy();
    $builder->set('hook_event_dispatcher.manager', $this->manager);
    $builder->compile();
    Drupal::setContainer($builder);
  }

  /**
   * Test EntityViewEvent by reference.
   */
  public function testEntityViewEventByReference() {
    $build = $expectedBuild = ['testBuild' => ['someBuild']];
    $entity = $this->createMock(EntityInterface::class);
    $display = $this->createMock(EntityViewDisplayInterface::class);
    $viewMode = 'testViewMode';

    $this->manager->setEventCallbacks([
      HookEventDispatcherInterface::ENTITY_VIEW => static function (EntityViewEvent $event) {
        $event->getBuild()['otherBuild'] = ['aBuild'];
      },
    ]);
    $expectedBuild['otherBuild'] = ['aBuild'];

    hook_event_dispatcher_entity_view($build, $entity, $display, $viewMode);

    /* @var \Drupal\hook_event_dispatcher\Event\Entity\EntityViewEvent $event */
    $event = $this->manager->getRegisteredEvent(HookEventDispatcherInterface::ENTITY_VIEW);
    $this->assertSame($build, $event->getBuild());
    $this->assertSame($expectedBuild, $event->getBuild());
    $this->assertSame($entity, $event->getEntity());
    $this->assertSame($display, $event->getDisplay());
    $this->assertSame($viewMode, $event->getViewMode());
  }

  /**
   * Test EntityViewEvent by set.
   */
  public function testEntityViewEventBySet() {
    $build = ['testBuild' => ['someBuild']];
    $otherBuild = ['otherBuild' => ['lalala']];
    $entity = $this->createMock(EntityInterface::class);
    $display = $this->createMock(EntityViewDisplayInterface::class);
    $viewMode = 'testViewMode';

    $this->manager->setEventCallbacks([
      HookEventDispatcherInterface::ENTITY_VIEW => static function (EntityViewEvent $event) use ($otherBuild) {
        $event->setBuild($otherBuild);
      },
    ]);

    hook_event_dispatcher_entity_view($build, $entity, $display, $viewMode);

    /* @var \Drupal\hook_event_dispatcher\Event\Entity\EntityViewEvent $event */
    $event = $this->manager->getRegisteredEvent(HookEventDispatcherInterface::ENTITY_VIEW);
    $this->assertSame($build, $event->getBuild());
    $this->assertSame($otherBuild, $event->getBuild());
    $this->assertSame($entity, $event->getEntity());
    $this->assertSame($display, $event->getDisplay());
    $this->assertSame($viewMode, $event->getViewMode());
  }

}

<?php

namespace Drupal\rabbitmq\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Drupal\Console\Command\Shared\ContainerAwareCommandTrait;
use Drupal\Console\Style\DrupalStyle;

/**
 * Class DefaultCommand.
 *
 * @package Drupal\rabbitmq
 */
class DefaultCommand extends BaseCommand {

  use ContainerAwareCommandTrait;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('rabbitmq:default')
      ->setDescription($this->trans('commands.rabbitmq.default.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    $text = sprintf(
      'I am a new generated command for the module: %s',
      $this->getModule()
    );

    $io->info($text);
  }

}

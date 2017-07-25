<?php

namespace AppBundle\Command;

use AppBundle\Entity\Deposit;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class DepositCalculateCommand
 *
 * @package AppBundle\Command
 */
class DepositCalculateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('tstech:deposit:calculate')

            // the short description shown while running "php bin/console list"
            ->setDescription('Recalculate deposit percents.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a user...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $deposits = $em->getRepository('AppBundle:Deposit');

        /** @var Deposit $deposit */
        foreach ($deposits as $deposit) {

            //calculate percents

            // if deposit created by 31. calculate at first day of next month
            if ($deposit->getCreatedAt()->format("d") === '31' && date("d", time()) === '1') {
                $deposit->setBalance($deposit->getBalance() + ($deposit->getBalance() * ($deposit->getPercent() / 12)));
            }
            // if deposit created by any day of month, but not 31. calculate at day-to-day
            if ($deposit->getCreatedAt()->format("Y-m-d") === date("Y-m-d", time()) && $deposit->getCreatedAt()->format("d") !== '31') {
                $deposit->setBalance($deposit->getBalance() + ($deposit->getBalance() * ($deposit->getPercent() / 12)));
            }

            // calculate commission
            if (date("d", time()) === '1') {
                if ($deposit->getBalance() < 1000) {
                    $deposit->setBalance($deposit->getBalance() * 0.05);
                }

                if ($deposit->getBalance() >= 1000 && $deposit->getBalance() < 10000) {
                    $deposit->setBalance($deposit->getBalance() * 0.06);
                }

                if ($deposit->getBalance() >= 10000) {
                    $deposit->setBalance($deposit->getBalance() * 0.07);
                }
            }

            $em->persist($deposit);
        }

        $em->flush();
    }
}
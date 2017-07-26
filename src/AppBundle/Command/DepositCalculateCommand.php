<?php

namespace AppBundle\Command;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\DepositLog;
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

                $depositLog = new DepositLog();
                $depositLog->setDeposit($deposit);
                $depositLog->setBalance($deposit->getBalance());
                $depositLog->setCreatedAt(new \DateTime());
                $em->persist($depositLog);
            }
            // if deposit created by any day of month, but not 31. calculate at day-to-day
            if ($deposit->getCreatedAt()->format("Y-m-d") === date("Y-m-d", time()) && $deposit->getCreatedAt()->format("d") !== '31') {
                $deposit->setBalance($deposit->getBalance() + ($deposit->getBalance() * ($deposit->getPercent() / 12)));

                $depositLog2 = new DepositLog();
                $depositLog2->setDeposit($deposit);
                $depositLog2->setBalance($deposit->getBalance());
                $depositLog2->setCreatedAt(new \DateTime());
                $em->persist($depositLog2);
            }

            // calculate commission
            if (date("d", time()) === '1') {
                if ($deposit->getBalance() < 1000) {
                    $deposit->setBalance($deposit->getBalance() * 0.05);

                    $depositLog3 = new DepositLog();
                    $depositLog3->setDeposit($deposit);
                    $depositLog3->setBalance($deposit->getBalance());
                    $depositLog3->setCreatedAt(new \DateTime());
                    $em->persist($depositLog3);
                }

                if ($deposit->getBalance() >= 1000 && $deposit->getBalance() < 10000) {
                    $deposit->setBalance($deposit->getBalance() * 0.06);

                    $depositLog4 = new DepositLog();
                    $depositLog4->setDeposit($deposit);
                    $depositLog4->setBalance($deposit->getBalance());
                    $depositLog4->setCreatedAt(new \DateTime());
                    $em->persist($depositLog4);
                }

                if ($deposit->getBalance() >= 10000) {
                    $deposit->setBalance($deposit->getBalance() * 0.07);

                    $depositLog5 = new DepositLog();
                    $depositLog5->setDeposit($deposit);
                    $depositLog5->setBalance($deposit->getBalance());
                    $depositLog5->setCreatedAt(new \DateTime());
                    $em->persist($depositLog5);
                }
            }

            $em->persist($deposit);
        }

        $em->flush();
    }
}
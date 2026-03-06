<?php

namespace App\Command;

use App\Entity\GameSession;
use App\Entity\Purchase;
use App\Entity\User;
use App\Service\StatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-stats',
    description: 'Migre les données existantes vers la table stats (JSONB)',
)]
class MigrateStatsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private StatService $statService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Migration des données existantes vers stats (JSONB)');

        // Achats
        $io->section('Achats (purchase)');
        $purchases = $this->em->getRepository(Purchase::class)->findBy(['status' => 'completed']);
        $io->progressStart(count($purchases));
        $count = 0;
        foreach ($purchases as $purchase) {
            try {
                $this->statService->recordPurchase($purchase, 'web');
                $count++;
            } catch (\Throwable $e) {
                $io->warning('Erreur purchase ' . $purchase->getId() . ' : ' . $e->getMessage());
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
        $io->success("$count achats migrés.");

        // Parties
        $io->section('Parties (game)');
        $sessions = $this->em->getRepository(GameSession::class)->findBy(['status' => 'finished']);
        $io->progressStart(count($sessions));
        $count = 0;
        foreach ($sessions as $session) {
            try {
                if ($session->getGameResult()) {
                    $this->statService->recordGame($session);
                    $count++;
                }
            } catch (\Throwable $e) {
                $io->warning('Erreur session ' . $session->getId() . ' : ' . $e->getMessage());
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
        $io->success("$count parties migrées.");

        // Inscriptions
        $io->section('Inscriptions (registration)');
        $users = $this->em->getRepository(User::class)->findBy(['isDeleted' => false]);
        $io->progressStart(count($users));
        $count = 0;
        foreach ($users as $user) {
            try {
                $this->statService->recordRegistration($user);
                $count++;
            } catch (\Throwable $e) {
                $io->warning('Erreur user ' . $user->getId() . ' : ' . $e->getMessage());
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
        $io->success("$count inscriptions migrées.");

        $io->success('Migration terminée !');
        return Command::SUCCESS;
    }
}
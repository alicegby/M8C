<?php

namespace App\Command;

use App\Entity\Purchase;
use App\Entity\User;
use App\Entity\GameSession;
use App\Service\StatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-stats',
    description: 'Migre toutes les données existantes de Supabase vers MongoDB pour les statistiques',
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
        $io->title('Migration des stats vers MongoDB');

        // ── 1. Purchases ────────────────────────────────────────────
        $io->section('Migration des achats (PurchaseStat)');

        $purchases = $this->em->getRepository(Purchase::class)->findBy(['status' => 'completed']);
        $io->progressStart(count($purchases));

        $purchaseCount = 0;
        foreach ($purchases as $purchase) {
            try {
                $this->statService->recordPurchase($purchase, 'web');
                $purchaseCount++;
            } catch (\Throwable $e) {
                $io->warning('Erreur purchase ' . $purchase->getId() . ' : ' . $e->getMessage());
            }
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success("$purchaseCount achats migrés.");

        // ── 2. GameSessions terminées ────────────────────────────────
        $io->section('Migration des parties (GameStat)');

        $gameSessions = $this->em->getRepository(GameSession::class)->findBy(['status' => 'finished']);
        $io->progressStart(count($gameSessions));

        $gameCount = 0;
        foreach ($gameSessions as $session) {
            try {
                if ($session->getGameResult()) {
                    $this->statService->recordGame($session);
                    $gameCount++;
                }
            } catch (\Throwable $e) {
                $io->warning('Erreur session ' . $session->getId() . ' : ' . $e->getMessage());
            }
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success("$gameCount parties migrées.");

        // ── 3. Utilisateurs ──────────────────────────────────────────
        $io->section('Migration des inscriptions (UserRegistrationStat)');

        $users = $this->em->getRepository(User::class)->findBy(['isDeleted' => false]);
        $io->progressStart(count($users));

        $userCount = 0;
        foreach ($users as $user) {
            try {
                $this->statService->recordRegistration($user);
                $userCount++;
            } catch (\Throwable $e) {
                $io->warning('Erreur user ' . $user->getId() . ' : ' . $e->getMessage());
            }
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success("$userCount inscriptions migrées.");

        $io->success('Migration terminée ! ' . ($purchaseCount + $gameCount + $userCount) . ' documents insérés dans MongoDB.');

        return Command::SUCCESS;
    }
}
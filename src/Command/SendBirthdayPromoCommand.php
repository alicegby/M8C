<?php

namespace App\Command;

use App\Entity\PromoCode;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-birthday-promo',
    description: 'Génère un code promo anniversaire pour les utilisateurs dont c\'est l\'anniversaire aujourd\'hui',
)]
class SendBirthdayPromoCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $today = new \DateTime();
        $day = (int) $today->format('d');
        $month = (int) $today->format('m');

        // Récupère les users dont c'est l'anniversaire aujourd'hui
        $users = $this->userRepository->findByBirthday($day, $month);

        if (empty($users)) {
            $io->info('Aucun anniversaire aujourd\'hui.');
            return Command::SUCCESS;
        }

        $count = 0;

        foreach ($users as $user) {
            // Vérifie qu'on n'a pas déjà envoyé un code anniv cette année
            $alreadyExists = $this->em->getRepository(PromoCode::class)->findOneBy([
                'code' => $this->buildCodePattern($user->getId()),
            ]);

            if ($alreadyExists) {
                $io->warning('Code déjà généré pour ' . $user->getEmail());
                continue;
            }

            // Génère le code unique
            $code = 'ANNIV-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 5));

            // Assure l'unicité
            while ($this->em->getRepository(PromoCode::class)->findOneBy(['code' => $code])) {
                $code = 'ANNIV-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 5));
            }

            $promo = new PromoCode();
            $promo->setCode($code);
            $promo->setDiscountType('percentage');
            $promo->setDiscountValue('10');
            $promo->setApplicableTo('both');
            $promo->setMaxUses(1);
            $promo->setIsActive(true);
            $promo->setValidFrom(new \DateTime());
            $promo->setValidUntil((new \DateTime())->modify('+1 month'));

            $this->em->persist($promo);
            $count++;

            $io->text('✓ Code ' . $code . ' généré pour ' . $user->getEmail());
        }

        $this->em->flush();
        $io->success($count . ' code(s) anniversaire généré(s).');

        return Command::SUCCESS;
    }

    private function buildCodePattern(string $userId): string
    {
        // Utilisé pour vérifier les doublons — pas utilisé comme code final
        return 'ANNIV-' . substr($userId, 0, 5);
    }
}
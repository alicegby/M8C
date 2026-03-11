<?php

namespace App\Command;

use App\Entity\PromoCode;
use App\Repository\UserRepository;
use App\Repository\NewsletterSubscriptionRepository;
use App\Service\PromoCodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:send-birthday-promo',
    description: 'Génère un code promo et envoie un email aux utilisateurs dont c\'est l\'anniversaire aujourd\'hui',
)]
class SendBirthdayPromoCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private PromoCodeService $promoCodeService,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private NewsletterSubscriptionRepository $newsletterRepo,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $today = new \DateTime();
        $day   = (int) $today->format('d');
        $month = (int) $today->format('m');
        $year  = (int) $today->format('Y');

        $users = $this->userRepository->findByBirthday($day, $month);

        if (empty($users)) {
            $io->info('Aucun anniversaire aujourd\'hui.');
            return Command::SUCCESS;
        }

        $count = 0;

        foreach ($users as $user) {
            // Vérifie qu'on n'a pas déjà envoyé un code anniv cette année
            $alreadyExists = $this->em->getRepository(PromoCode::class)
                ->createQueryBuilder('p')
                ->where('p.code LIKE :prefix')
                ->andWhere('p.validFrom >= :startOfYear')
                ->setParameter('prefix', 'ANNIV-%')
                ->setParameter('startOfYear', new \DateTime($year . '-01-01'))
                ->getQuery()
                ->getOneOrNullResult();

            // On vérifie plus précisément si ce user a déjà eu un code cette année
            // via les usages (si PromoCodeUsage existe) — sinon on se base sur la date
            if ($alreadyExists) {
                $usage = $this->em->getRepository(\App\Entity\PromoCodeUsage::class)
                    ->createQueryBuilder('u')
                    ->join('u.promoCode', 'p')
                    ->where('u.user = :user')
                    ->andWhere('p.code LIKE :prefix')
                    ->andWhere('p.validFrom >= :startOfYear')
                    ->setParameter('user', $user)
                    ->setParameter('prefix', 'ANNIV-%')
                    ->setParameter('startOfYear', new \DateTime($year . '-01-01'))
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($usage) {
                    $io->warning('Code déjà envoyé cette année pour ' . $user->getEmail());
                    continue;
                }
            }

            try {
                // Génère le code via PromoCodeService
                $promo = $this->promoCodeService->generateBirthdayCode();

                // Récupère le token de désinscription si abonné newsletter
                $subscription = $this->newsletterRepo->findOneBy(['email' => $user->getEmail()]);
                $unsubscribeToken = $subscription?->getUnsubscribeToken();

                // Envoie l'email
                $email = (new TemplatedEmail())
                    ->from('contact@meurtrehuisclos.fr')
                    ->to($user->getEmail())
                    ->subject('🎂 Joyeux anniversaire ' . $user->getPrenom() . ' !')
                    ->htmlTemplate('emails/birthday.html.twig')
                    ->context([
                        'user'             => $user,
                        'promoCode'        => $promo->getCode(),
                        'app_url'          => $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        'unsubscribeToken' => $unsubscribeToken,
                    ]);

                $this->mailer->send($email);

                $count++;
                $io->text('✓ Email envoyé à ' . $user->getEmail() . ' — code : ' . $promo->getCode());

            } catch (\Throwable $e) {
                $io->error('Erreur pour ' . $user->getEmail() . ' : ' . $e->getMessage());
            }
        }

        $io->success($count . ' email(s) anniversaire envoyé(s).');

        return Command::SUCCESS;
    }
}
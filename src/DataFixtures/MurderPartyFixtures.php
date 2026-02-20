<?php

namespace App\DataFixtures;

use App\Entity\MurderParty;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MurderPartyFixtures extends Fixture
{
    public const MP1_REFERENCE = 'mp-cocktailspotinsassassinat';

    public function load(ObjectManager $manager): void
    {
        $mp1 = new MurderParty();
        $mp1->setTitle('Cocktails, potins et assassinat');
        $mp1->setSlug('cocktails-potins-assassinat');
        $mp1->setSynopsis('La Duchesse Eugénie de Neuchâtel vous a conviées, pour un après-midi en sa compagnie dans son château. La Duchesse avait prévu, avec l\'aide de Marguerite, sa domestique, un dîner en 5 services, avec un dress code "élégance des années 20". A 20h, toutes les invitées de la Duchesse étaient dans le petit salon. C\'est autour de 20h30, que les invitées commencèrent à s\'inquiéter : la Duchesse n\'était toujours pas descendu. La Duchesse Eugénie était morte. Etendue au sol, baignant dans son sang. Un collier de perle brisé autour d\'elle. Qui a tué la Duchesse ? ');
        $mp1->setScenario('La Duchesse Eugénie de Neuchâtel vous a conviées, pour un après-midi en sa compagnie dans son château. Vous êtes ses amies, ou sa famille. Sauf Marguerite, qui est la domestique. 
                                            Suite au décès de ses parents, la Duchesse Eugénie a plus que besoin de ses proches pour se changer les idées. 
                                            Vous avez passé l\'après-midi autour de la piscine, à boire des coupes de champagnes et à déguster du caviar. Un vrai après-midi de reines. 
                                            La Duchesse avait prévu, avec l\'aide de Marguerite, un dîner en 5 services, avec un dress code "élégance des années 20". Les invitées avaient été priées de regagner leur chambre pour se changer aux alentours de 18h30.
                                            A 20h, toutes les invitées de la Duchesse étaient dans le petit salon, à attendre leur hôte pour se rendre dans la salle à manger. Marguerite leur avait servit du champagne pour les faire attendre. C\'est autour de 20h30, que les invitées commencèrent à s\'inquiéter : la Duchesse n\'était toujours pas descendu. 
                                            Marguerite en tant que domestique dévouée, se chargea d\'aller chercher sa patronne. En frappant, personne ne répondit. Elle entrouvera la porte et cria. Les invitées, alertées par le cri, sont apparu dans le dos de la domestique. La Duchesse Eugénie était morte. Etendue au sol, baignant dans son sang. Un collier de perle brisé autour d\'elle. 
                                            Qui a tué la Duchesse ?'
        );
        $mp1->setEpilogue('TBC');
        $mp1->setDuree(30);
        $mp1->setNbPlayers(4);
        $mp1->setPrice('0.00');
        $mp1->setIsFree(true);
        $mp1->setIsPublished(true);
        $mp1->setAverageRating('4.20');
        $mp1->setRatingsCount(5);
        $manager->persist($mp1);
        $this->addReference(self::MP1_REFERENCE, $mp1);

        $manager->flush();
    }
}
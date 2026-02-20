<?php

namespace App\DataFixtures;

use App\Entity\Character;
use App\Entity\MurderParty;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CharacterFixtures extends Fixture implements DependentFixtureInterface
{
    public const MARGUERITE_REFERENCE = 'character-marguerite';

    public function load(ObjectManager $manager): void
    {
        /** @var MurderParty $mp1 */
        $mp1 = $this->getReference(MurderPartyFixtures::MP1_REFERENCE, MurderParty::class);

        $characters1 = [
            [
                'prenom' => 'Marguerite', 
                'nom' => 'Berger', 
                'age' => 60, 
                'job' => 'Domestique', 
                'histoire' => 'Je travaille pour la famille depuis 40 ans. Mes patrons étaient les parents de la Duchesse Eugénie, et à leur mort, lorsque la Duchesse à repris le titre et le domaine, elle est devenue ma patronne. Mon attachement à la duchesse est profond, forgé par des années de soin et d\'affection. Je suis plus qu\'une domestique; je suis une figure maternelle, indispensable à la famille de Neuchâtel.', 
                'mobile' => 'J\'ai appris par le jardinier, qu\'Eugénie voulait me congédier. Ses parents m\'ont toujours considéré comme un membre de la famille. Je ne suis pas duper, je sais bien qu\'en tant que domestique, je ne pouvais pas être leur égal. En revanche, j\'ai toujours apprécié leur attention. Il était prévu que même lorsque je ne pourrais plus travailler, fatiguée par la vieillesse, la famille s\'occuperait de moi, étant donné que je n\'ai pas eu de famille. 
                                    Mais maintenant, on veut me congédier ? Moi qui est tout donné pour cette famille, et surtout pour cette fille ! Je l\'ai élevé comme si j\'étais sa mère et elle me remercie de cette façon ? ', 
                'alibi' => 'Eugénie a été exécrable la semaine avant la venue de ses amies, et ce soir également. Elle m’avait demandé de l’aide pour s’habiller, après l’après-midi piscine. Ce que j’ai fait, bien sûr. En parallèle, j’étais posté en bas pour servir du champagne à ses invités qui attendaient. Mais je m’éclipsais à chaque fois, une fois les verres servis, pour ne pas qu’elles voient ma colère. C’est alors qu’elle me rappelle. Elle utilise une clochette dans sa chambre qui sonne dans la cuisine. Ses invités étaient déjà en bas. Je suis montée en toute discrétion, et je me suis dépêchée. En entrant, Eugénie avait retirer sa robe et était folle de rage contre ses amies. Elle souhaitait que je les congédie toutes. Ensuite, elle a commencé à me parler très mal, et à me dire que je devrais partir aussi, que de toute façon son père souhaitait me faire virer. Que des informations que je connaissais déjà. Mais venant de sa bouche, Eugénie, que j’ai presque élevé, j’étais folle de rage. Eugénie s’est approché de moi, je ne savais ce qu’elle voulait faire, j’ai pris peur. Elle a tendu sa main, et m’a arraché mon collier de perle, en me disant que je ne méritais pas ce cadeau, fait par sa mère. Je me suis saisi du coupe papier, et je lui ai tranché la gorge. Je n’avais que quelques minutes pour fuir et faire comme si de rien n’était. J’ai récupéré son collier de perle, que j’ai mis autour de mon cou, maintenant le sien serait le mien, et le mien serait le sien : brisé. J’ai pris des portes dérobées jusqu’à rejoindre les autres convives. 
                                Comme Eugénie n\'était toujours pas là à 20h30, évidemment, je l’ai tué.  Je suis allé la chercher dans sa chambre. J’ai crié, et les convives m’ont rejoint. J’espère qu’elles ne sauront pas que je suis la coupable. ', 
                'is_guilty' => true, 
                'ref' => self::MARGUERITE_REFERENCE
            ],

            [
                'prenom' => 'Rosa',
                'nom' => 'Adler', 
                'age' => 30,
                'job' => null,
                'histoire' => 'Je suis la belle-soeur d\'Eugénie. Je suis mariée à son frère depuis cinq ans. Mais on se connait depuis beaucoup plus longtemps. J\'ai rencontré Eugénie en cours de danse classique quand nous avions que quatre ans. Nous avons pris des cours jusqu\'à nos dix-huit ans. C\'est à peu près à cette époque que j\'ai commencé à fréquenter son frère. Elle n\'a jamais apprécié notre relation, et n\'a jamais voulu nous voir ensemble. Elle était très protectrice, voire trop. Ce n\'était pas facile à vivre. Elle n\'arrivait pas à concevoir mon mariage avec son frère et mon amitié avec elle. 
                                    Je suis devenue peintre. Je peint majoritairement des portraits. J\'en ai fait un grand nombre de la famille d\'Eugénie et de mon mari. J\'en ai fait un de d\'elle une fois, je l\'ai vendu à un très haut prix lors d\'une vente aux enchères. Elle ne m\'a jamais demandé de commission.',
                'mobile' => 'Mon mari a refusé ses titres au décès de ses parents. C\'est Eugénie qui a hérité de la noblesse de la famille. En revanche, mon beau-père avait promis à mon mari qu\il hériterai de 50% des richesses. Ca n\'a pas été le cas. Mon mari a appris, en se rendant chez le notaire, que leur père avait modifié sa clause suite à une demande de sa fille. Eugénie avait donc du réclamer plus à son père. J\'ignore pourquoi. Et je trouve ça injuste. Mon mari m\'a dit d\'abandonner l\'idée d\'arranger les choses. Mais je ne suis pas de cet avis. Je dois en avoir le coeur net, et en discuter avec elle.',
                'alibi' => 'Après l’après-midi piscine, je me suis vite changée, pour pouvoir aller voir Eugénie dans sa chambre. J’ai prétexté vouloir me préparer avec elle, mais je voulais surtout lui parler en tête à tête, du problème actuel, sans oreilles autour. Pendant la discussion, nous avons évoqué le décès de ses parents. J’en vint à lui parler du problème autour de l’héritage. Sa seule réponse fut « je ne vois pas de quoi tu parles, je n’ai jamais demandé une telle chose à mon père ». Je suis donc sorti, ça ne servait à rien. 
                                Je suis retourné dans ma chambre pour téléphoner à mon mari. J’ai entendu quelqu’un entrer dans la chambre d’Eugénie un peu avant 19h50. Heure à laquelle j’ai rejoins les convives au salon. Marguerite est venu me servir un verre de champagne et s’est enfui. Je me rappelle avoir remarqué un joli collier autour de son cou.
                                Comme Eugénie n\'était toujours pas là à 20h30, Marguerite est allé la chercher dans sa chambre. 
                                Quand on l\'a entendu crier, nous l\'avons rejoint et avons découvert Eugénie étendue, morte. Jade avait l\’air presque soulagée.',
                'is_guilty' => false,
            ],
            [
                'prenom' => 'Marie',
                'nom' => 'Courrège',
                'age' => 29,
                'job' => null,
                'histoire' => 'Je suis en couple avec Jade. C\'est ma patronne, mais on a été beaucoup plus très vite. Je travaille toujours pour elle, mais le soir je rentre auprès d\'elle. Eugénie n\'est pas au courant de notre relation. Je ne sais pas pourquoi Jade ne souhaite pas lui en parler. Mais je respecte son choix, elle la connait beaucoup mieux que moi, comme elles sont cousines. Pour elle, nous sommes des meilleures amies. Et Eugénie m\'apprécie, c\'est pour ça que je suis invitée. Ça me convient parfaitement. Même si elle nous a prévue des chambres différentes pour Jade et moi.',
                'mobile' => 'Je veux venger ma bien-aimée de la trahison d\'Eugénie et sa famille quant à l\'héritage qu\'elle n\'a pas reçu. Le père d\'Eugénie avait promis à Jade qu\'il l\'ajouterai sur son testament. A leur mort, elle n\'a rien reçu. Et Eugénie n\'a rien fait pour sa cousine. 
                                    Jade s\'attendait à recevoir quelque chose, elle voulait les réinvestir dans son entreprise. Elle est tombée de haut,. Elle souffre... pas à cause du manque \'argent, mais à cause du manque de considération de ceux qu\'elle considérait comme sa famille.',
                'alibi' => 'Après l\'après-midi piscine, nous sommes allé nous changer vers 18h30. J’avais confectionné la robe de ma chérie Jade. Elle est venu se changer dans ma chambre, comme Eugénie nous avait mise dans deux chambres différentes, comme elle n’avait pas connaissance de notre relation. Je devais rejoindre Jade 10 min après qu’elle soit descendue, pour ne pas nous faire prendre. 
                                Mais au lieu de sagement l’attendre, je suis allé dans la chambre d’Eugénie. Elle me fit entrer, elle n’était même pas encore changé. Ma venue tombait bien, elle avait besoin d’aide pour repriser un bouton tombé. Elle avait l’air en colère, alors j’ai cru que c’était pour ce fichu bouton, mais maintenant que je sais ce qu’il s’est passé ensuite, je me demande si elle n’était pas énervée contre quelqu’un. Je l’ai même aidé à mettre son collier de perle. En recousant sa robe, je me mis à lui parler que Jade n’allait pas très bien en ce moment. Eugénie était surprise, alors j’ai lâché le morceau. Je lui ai dit qu’elle était triste de ne pas avoir été considéré dans le testament de son père. Eugénie a vu rouge, et m’a fichu dehors, sans même me donner réponse. Je suis donc descendu vers 19h40, Jade, Rosa et Marguerite étaient toutes là. 
                                Comme Eugénie n\'était toujours pas là à 20h30, Marguerite est allé la chercher dans sa chambre. 
                                Quand on l\'a entendu crier, nous l\'avons rejoint et avons découvert Eugénie étendue, morte. Rosa semblait satisfaite.',
                'is_guilty' => false, 
            ],
            [
                'prenom' => 'Jade',
                'nom' => 'Knowles',
                'age' => 30,
                'job' => null,
                'histoire' => 'Eugénie est ma cousine. Nous n\'avons jamais eu de soeur dans nos frateries et nous nous sommes toujours considérées comme telles. Nous avons grandis ensemble. Au décès de ses parents je l\'ai beaucoup soutenu. 
                                        Cependant, mon histoire avec sa famille est très compliquée. Ma mère a toujours été amoureuse du père d\'Eugénie. Mais il a choisi ma tante. J\'ai dû choisir un camp très tôt. Ma mère ne souhaitait pas que je cotoie sa soeur et sa famille. Le choix était trop difficile, mais le père d\'Eugénie m\'a assuré qu\'il s\'occuperait de moi si je les choisissais. Ce que j\'ai fait. 
                                        Je vis une histoire d\'amour avec Marie Courrèges. On travaille ensemble. En fait c\'est mon employé, mais nous sommes tombées amoureuses très vite. Eugénie ne sait rien de ma relation.',
                'mobile' => 'Le père d\'Eugénie m\'avait dit qu\'il m\'ajouterai sur son testament, étant donné que j\'étais comme sa fille, après les avoir choisi plutôt que ma propre mère. A leur mort j\'ai découvert que c\'était faux. J\'en ai parlé à Eugénie, et elle m\'a dit "c\'est normal, tu n\'es pas leur fille". J\'ai pris cette phrase comme un coup de couteau. Ma cousine ne s\'est même pas battu pour moi. Finalement je ne faisais pas tant partie de cette famille que ça. 
                                    J\'en veux à Eugénie.',
                'alibi' => 'Après l\'après-midi piscine, nous sommes allé nous changer vers 18h30. J\'avais prévu une belle robe beige avec des sequins, et un bijou de tête en diamant. Tout confectionné par ma chérie Marie. Comme Eugénie n\'est pas au courant de notre relation, elle nous a mise dans deux chambres séparés. Mais je suis allé me changer dans celle de Marie. 
                                Nous étions toutes les deux, elle m\'a aidé à m\'habiller. Comme on ne souhaitait pas se faire "prendre", j\'ai décidé de descendre la première, et Marie devait me retrouver dix minutes plus tard dans le salon. 
                                En descendant, j\'étais la première. Marguerite apporta un plateau avec des coupes et deux bouteilles de champagne et reparti. Rosa est arrivé 15 minutes après moi. Marie n\'était toujours pas là... Quand Marie est finalement arrivée, Rosa s\'absenta. 
                                C\'est finalement vers 19h50 que nous étions toute là : Marie, Rosa, Marguerite et moi. 
                                Comme Eugénie n\'était toujours pas là à 20h30, Marguerite est allé la chercher dans sa chambre. 
                                Quand on l\'a entendu crier, nous l\'avons rejoint et avons découvert Eugénie étendue, morte. Marguerite avait l’air très stressée.',
                'is_guilty' => false, 
            ]
        ];

        foreach ($characters1 as $data) {
            $character = new Character();
            $character->setMurderParty($mp1);
            $character->setPrenom($data['prenom']);
            $character->setNom($data['nom']);
            $character->setAge($data['age']);
            $character->setJob($data['job']);
            $character->setHistoire($data['histoire']);
            $character->setMobile($data['mobile']);
            $character->setAlibi($data['alibi']);
            $character->setIsGuilty($data['is_guilty']);
            $manager->persist($character);
            if (!empty($data['ref'])) {
                $this->addReference($data['ref'], $character);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [MurderPartyFixtures::class];
    }
}
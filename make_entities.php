<?php

$dirEnt = 'src/Modules/Equipement/Entity';
$dirRepo = 'src/Modules/Equipement/Repository';

if(!is_dir($dirEnt)) mkdir($dirEnt, 0777, true);
if(!is_dir($dirRepo)) mkdir($dirRepo, 0777, true);

// REPOSITORIES
file_put_contents($dirRepo . '/EquipementRepository.php', "<?php\nnamespace App\Modules\Equipement\Repository;\nuse App\Modules\Equipement\Entity\Equipement;\nuse Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;\nuse Doctrine\Persistence\ManagerRegistry;\nclass EquipementRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry \) { parent::__construct(\, Equipement::class); } }");

file_put_contents($dirRepo . '/EquipementGeoRepository.php', "<?php\nnamespace App\Modules\Equipement\Repository;\nuse App\Modules\Equipement\Entity\EquipementGeo;\nuse Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;\nuse Doctrine\Persistence\ManagerRegistry;\nclass EquipementGeoRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry \) { parent::__construct(\, EquipementGeo::class); } }");

file_put_contents($dirRepo . '/ReviewRepository.php', "<?php\nnamespace App\Modules\Equipement\Repository;\nuse App\Modules\Equipement\Entity\Review;\nuse Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;\nuse Doctrine\Persistence\ManagerRegistry;\nclass ReviewRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry \) { parent::__construct(\, Review::class); } }");

// ENTITIES
file_put_contents($dirEnt . '/Equipement.php', "<?php\nnamespace App\Modules\Equipement\Entity;\nuse App\Modules\Equipement\Repository\EquipementRepository;\nuse Doctrine\ORM\Mapping as ORM;\n\n#[ORM\Entity(repositoryClass: EquipementRepository::class)]\n#[ORM\Table(name: 'equipement')]\nclass Equipement\n{\n    #[ORM\Id]\n    #[ORM\GeneratedValue]\n    #[ORM\Column(type: 'integer')]\n    private ?int \ = null;\n\n    #[ORM\Column(type: 'string', length: 100)]\n    private ?string \ = null;\n\n    #[ORM\Column(type: 'string', length: 100)]\n    private ?string \ = null;\n\n    #[ORM\Column(type: 'float')]\n    private ?float \ = null;\n\n    #[ORM\Column(type: 'string', length: 50)]\n    private ?string \ = null;\n\n    #[ORM\Column(name: 'user_id', type: 'integer')]\n    private ?int \ = null;\n\n    // Add getters/setters via your IDE\n}");

file_put_contents($dirEnt . '/Review.php', "<?php\nnamespace App\Modules\Equipement\Entity;\nuse App\Modules\Equipement\Repository\ReviewRepository;\nuse Doctrine\ORM\Mapping as ORM;\n\n#[ORM\Entity(repositoryClass: ReviewRepository::class)]\n#[ORM\Table(name: 'review')]\nclass Review\n{\n    #[ORM\Id]\n    #[ORM\GeneratedValue]\n    #[ORM\Column(type: 'integer')]\n    private ?int \ = null;\n\n    #[ORM\Column(type: 'text')]\n    private ?string \ = null;\n\n    #[ORM\Column(type: 'integer')]\n    private ?int \ = null;\n\n    #[ORM\Column(name: 'date_review', type: 'date')]\n    private ?\DateTimeInterface \ = null;\n\n    #[ORM\Column(name: 'user_id', type: 'integer')]\n    private ?int \ = null;\n\n    #[ORM\ManyToOne(targetEntity: Equipement::class)]\n    #[ORM\JoinColumn(name: 'equipement_id', referencedColumnName: 'id')]\n    private ?Equipement \ = null;\n}");

file_put_contents($dirEnt . '/EquipementGeo.php', "<?php\nnamespace App\Modules\Equipement\Entity;\nuse App\Modules\Equipement\Repository\EquipementGeoRepository;\nuse Doctrine\ORM\Mapping as ORM;\n\n#[ORM\Entity(repositoryClass: EquipementGeoRepository::class)]\n#[ORM\Table(name: 'equipement_geo')]\nclass EquipementGeo\n{\n    #[ORM\Id]\n    #[ORM\GeneratedValue(strategy: 'NONE')]\n    #[ORM\OneToOne(targetEntity: Equipement::class)]\n    #[ORM\JoinColumn(name: 'equipement_id', referencedColumnName: 'id')]\n    private ?Equipement \ = null;\n\n    #[ORM\Column(name: 'garage_id', type: 'integer')]\n    private ?int \ = null;\n\n    #[ORM\Column(name: 'position_gps', type: 'string', length: 50)]\n    private ?string \ = null;\n\n    #[ORM\Column(name: 'statut_garage', type: 'string', length: 20)]\n    private ?string \ = null;\n\n    #[ORM\Column(name: 'derniere_localisation', type: 'datetime')]\n    private ?\DateTimeInterface \ = null;\n}");
echo "DONE";

# Formation Symfony

## PHP

- Constructeur : 
 ```
 public function __construct() {
        print "Ceci est un constructeur en php";
    }
 ```
- Object de type date : `$time = new \DateTime();`
- Afficher le contenu d'une variable dans la console : `dump()` , `var_dump()`
- Typer le retour d'une fonction :
 ```
 public function maFonction() : array
 {
        return ['0','1','2'];
 }
 ```
 - Pipe pour formatter les chiffres : `number_format()` 
 > Exemple : 200000 | number_format(0,'',' ') -> 200 000
 - Pipe pour ajouter des sauts de lignes s'il y a un saut de ligne dans le texte de tbase : `text | nl2br `
- Ajouter une dépendance (exp slugify) : `composer require cocur/slugify` + `use` au sein de la classe où l'on veut appeler la dép


## 3/16 : ORM Doctrine 

- Configuer la connexion à la base de données dans le fichier `.env`
> exemple : `mysql://root:root@127.0.0.1:3306/SuperAgence?serverVersion=5.7`

- Créer une BDD avec doctrine : `php bin/console doctrine:database:create`
- Créer une *Entity* : `php bin/console make:entity` ( Cette commande crée une Entity + un Repository )
- Préparer la migration vers la BDD : `php bin/console make:migration` ( Cette commande compare les entity et génère un fichier de version dans src/migrations)
- Migrer vers la BDD : `php bin/console doctrine:migrations:migrate `
- Modifier une Entity qui existe déjà : `php bin/console make:entity nomDelEntityAChanger`
- Pour persister les données, il faut utiliser un *EntityManager* : `$this->getDoctrine()->getManager()->persist($myEntity);` -> Envoyer les données persistées  à la BDD `$entityManager->flush();`
- Récupérer l'object *Repository* : `$this->getDoctrine()->getRepository(MyEntity::class);` *OU BIEN* l'injecter directement dans le contructeur (à la manière de SpringBoot) ou dans n'importe quelle autre méthode.
> L'objet est connu grâce à l'*autowiring* de Symfony ( `php bin/console debug:autowiring`)
- Récupérer un objet depuis la BDD : `find()` , `findAll()` , `findOneBy()`
- Créer une *query* personnalisé dans le *Repository* : `$this->createQueryBuilder()-> .. ->getQuery()->getResult();`
- Interpolation en Twig : `{{ property.title }}` au lieu de property.getTitle() -> twig le fait automatiquement

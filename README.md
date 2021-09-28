# Formation Symfony

Pour lancer l'application, taper la cmd `php -S localhost:8000 -t public`

Version Symfony : **5.0.7**

Version PHP : **7.4.5**

Version MySQL : **8.0.19**

## PHP

- Constructeur : 
 ```php
 public function __construct() {
        print "Ceci est un constructeur en php";
    }
 ```
- Object de type date : `$time = new \DateTime();`
- Afficher le contenu d'une variable dans la console : `dump()` , `var_dump()`
- Typer le retour d'une fonction :
 ```php
 public function maFonction() : array
 {
        return ['0','1','2'];
 }
 ```
 - Pipe pour formatter les chiffres : `number_format()` 
 > Exemple : 200000 | number_format(0,'',' ') -> 200 000
 - Pipe pour ajouter des sauts de lignes s'il y a un saut de ligne dans le texte de base : `text | nl2br `
- Ajouter une dépendance (exp slugify) : `composer require cocur/slugify` + `use` au sein de la classe où l'on veut appeler la dép
- `array_flip()` : inverser les clés et les valeurs d'un tableau

## 3/16 : ORM Doctrine 

- Configuer la connexion à la base de données dans le fichier `.env`
> exemple : `mysql://root:root@127.0.0.1:3306/SuperAgence?serverVersion=5.7`

- Créer une BDD avec doctrine : `php bin/console doctrine:database:create`
- Créer une **Entity** : `php bin/console make:entity` ( Cette commande crée une Entity + un Repository )
- Préparer la migration vers la BDD : `php bin/console make:migration` ( Cette commande compare les entity et génère un fichier de version dans src/migrations)
- Migrer vers la BDD : `php bin/console doctrine:migrations:migrate `
- Modifier une Entity qui existe déjà : `php bin/console make:entity nomDelEntityAChanger`
- Pour persister les données, il faut utiliser un **EntityManager** : `$this->getDoctrine()->getManager()->persist($myEntity);` -> Envoyer les données persistées  à la BDD `$entityManager->flush();`
- Récupérer l'object *Repository* : `$this->getDoctrine()->getRepository(MyEntity::class);` *OU BIEN* l'injecter directement dans le contructeur (à la manière de SpringBoot) ou dans n'importe quelle autre méthode.
> L'objet est connu grâce à l'**autowiring** de Symfony ( `php bin/console debug:autowiring`)
- Récupérer un objet depuis la BDD : `find()` , `findAll()` , `findOneBy()`
- Créer une *query* personnalisé dans le *Repository* : `$this->createQueryBuilder()-> .. ->getQuery()->getResult();`
- Interpolation en Twig : `{{ property.title }}` au lieu de property.getTitle() -> twig le fait automatiquement

## 4/16 : CRUD & Forms
- Générer un formulaire : `php bin/console make:form` , le nom doit finir par *Type*
- Envoyer une vue du formulaire à la vue(twig) : `$this->createForm(PropertyType::class,$property)->createView() `
- Commencer un formulaire dans la vue (twig) : `{{ form_start(form) }}` .. `{{ form_end(form) }}` , avec *form* est l'objet de type form ( `$this->createForm()` )
- Afficher un formulaire ( entre start et end ) : ` {{ form_rest(form) }} `
- Modifier le thème par défaut des formulaires : ajouter une ligne from_theme dans `config/packages/twig.yaml` ( exp `form_themes: ['bootstrap_4_layout.html.twig']` )
- Afficher le champ d'une seule propriété de l'objet : ` {{ form.row(proprerty.title) }}`
- Gestion des traductions pour les différents champs :
    * Ajouter un translation_domain
    ```php
    public function configureOptions(OptionsResolver $resolver) {
            $resolver->setDefaults([
                'data_class' => Property::class,
                'translation_domain' => 'forms'
            ]);
    }
    ```
    * Créer un fichier `forms.fr.yaml` dans le dossier `translations`
    * Modifier le fichier `config/packages/services.yaml`, ce paramètre sera utilisé par `config/packages/translations.yaml` dans `framework.default_locale`
    ```yaml
    parameters:
     locale : 'fr'
    ```
- Modifier le type de données dans le fomrulaire : ajouter un `ChoiceType::class` comme 2nd paramètre de la méthode `add` du builder du form
```php
$builder->add('heat',ChoiceType::class , [
                'choices' => array_flip(Property::HEAT)
            ])
```
- A la création d'une **nouvelle** entity, il faut la tracker par l'EntityManager grâce à la méthode `persist` avant de la flusher
- Inclure un template twig dans un autre : `{{ include ('base.template.twig', {options} }}`. ( options en format **json**)
- Ajouter un bouton pour supprimer : utilisation d'un input hidden en spécifiant la méthode Delete
```html
<form method="post" action="{{ path('admin.property.delete' , {id: property.id}) }}" onsubmit="return confirm('Êtes vous sur ?')">
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="btn btn-danger">Supprimer</button>
</form>
```
- **csrfToken** généré automatiquement pour les formulaires par symfony, pour le générer manuellement (Concaténation via **~** dans twig) : 
```html
<input type="hidden" name="_token" value="{{ csrf_token('delete' ~ property.id) }}">
```
Ensuite vérifier au niveau du Controller si le token est valide
```php
if ($this->isCsrfTokenValid('delete' . $property->getId(), $request->get('_token'))) { .. } 
```
- Afficher un message **flash** (sauvegardé en session) après une requete de get,post,delete.. : `$this->addFlash('success','modifié avec succes');`. Ensuite pour récuprer cette valeur au niveau de la vue twig 
```twig
 {% for msg in app.flashes('success') %}
            <div class="alert alert-success">
                {{ msg }}
            </div>
 {% endfor %}
```

## 5/16 : Validation des données dans le formulaire
- Rendre l'entity unique par rapport à un champ : `@UniqueEntity("title")`
- Utilisation des Regex : `@Assert\Regex("/^[0-9]{5}/")`
- Pour créer un validator perosnnalisé, il faut créer une [contrainte](https://symfony.com/doc/current/reference/constraints.html) ensuite le validator correspondant


## 6/16 : Securité & Authentification

- La config se trouve dans le fichier `config/packages/security.yaml` : 
    * `providers` pour la récupération des utilsateurs ( `in_memory` pour des utilsateurs ajouté au sein du même fichier, `from_database` pour des utilsateurs sauvegardés en bdd)
    * `firewalls` pour les composants qui permettent d'authentifer les utilisateurs ( `http_basic` pour basic auth, `form login` , etc )
    * `acces_control` pour définir les niveau d'accès des routes en fonctions des rôles
    * `encoders` pour les encoders à utiliser pour chiffrer les mdp
- Pour se déconnecter ( dans le cadre d'une basic auth ), il faut taper `log:out` devant l'url
- La classe `User` pour la gestion des utilsateurs doit implémenter `UserInterface` du composant sécurity ( et `Serializable` )
-  Pour obtenir les erreurs d'authentification, on peut injecter `AuthenticationUtils`:  `getLastUsername` pour obtenir le dernier utilisateur injecté, `getLastAuthenticationError` pour la dernière erreur
- Pour débugger la configuration d'un composant `php bin/console config:dump reference nomDuComposant`
- Modifier le path pour s'authentifier ( par défaut c'est `/login_check` ) : `firewalls.form_login.check_path : login` pour le path `/login`
- `php bin/console make:fixture` pour créer des fausses données (fixtures)
- Utiliser la méthode `encodePassword()` de l'interface `UserPasswordEncoderInterface` pour encoder les mots de passe ( le 1er paramètre de la méthode, donc le user, doit implémenter la UserInterface)
- `php bin/console doctrine:fixtures:load --append` charger les fixtures en BDD sans effacer les données qui y sont déjà
- pour savoir si il y a un utilisateur connecté à l'application `{% if app.user %}`

## 7/16 : Listing et pagination
- Pour générer des *fake* données : utilisation de la lib faker `composer require fzaninotto/faker`
- Pour paginer la liste des biens : utlisation du *bundle* paginator `composer require knplabs/knp-paginator-bundle` -> l'ajouter à la liste des bundles disponible dans `bundles.php` + création d'un fichier de configuration `config/packages/knp_paginator.yaml`
- Pour editer le template de pagination, il faut modifier la clé `template.pagination: '@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig' `
- Pour modifier le texte des boutons *Suivant* et *Précédent*, c'est géré au niveau des traductions `translations/KnpPaginatorBundle.fr.yml`:
```yml
label_next: Suivant
label_previous: Précédent
```
- Pour **vider le cache** `php bin/console cache:clear`

## 8/16 : Filtrer les données
- Principe :
   1. Créer une Entity qui représente la recherche
   1. Créer un formulaire
   1. Géréer le traitement (la recherche) dans le controller
- **Fluent** setter retourne l'objet et permet donc de chainer les modifications sur un objet
```php
public function setIndex($index)
{
   $this->index = $index;
   return $this;
}
```
- Si on utilise la commande `make:form` avec une classe qui ne fait pas partie de l'ORM, il faut taper le nom en entier ( exp : `\App`\Entity\PropertySearchType`) 
- Pas de besoin de csrf_token pour les résultats de recherche
- Utilisation de la méthode `Get` pour pouvoir partager les résultats ( via un lien )
- Pour modifier le préfix affiché dans le lien, il faut redéfinir la méthode `getBlockPrefix` dans `PropertySearchType`
- Pour ajouter des conditions au niveau du `PropertySearch`, il faut importer la classe `Symfony\Component\Validator\Constraints as Assert`, ensuite utiliser cette contrainte dans la partie *annotations* du champ surlequel on veut ajouter une contrainte exp : 
```php
/**
 * @var int|null
 * @Assert\Range(min=10,max=500)
 */
```
## 9/16 : Ajouter des options
- Pour mapper une nouvelle `Entity` avec une autre, il faut définir le `field type` à `relation` en choisissant la classe avec laquelle elle va être mappé ainsi que le type de relation( ManyToOne, ManyToMany..)
- Symfony manipule les tableaux en tant que `ArrayCollection`, cette classe implémente des methodes tels que `first()` ,`last()`..
- Pour générer un controller de **CRUD** : `php bin/console make:crud`
- Pour ajouter un champ `select` dans un formulaire, avec comme options les valeurs en BDD de l'objet, il faut choisir `EntityType::class`
```php
->add('options',EntityType::class)
```
- Pour revenir en arrière sur une migration, il faut d'abord afficher l'état de la base avec `php bin/console doctrine:migrations:status` ensuite choisir la version sur laquelle on veut se positionner `php bin/console doctrine:migrations:migrate numDeVersion`
- Dans une relation **ManyToMany**, il faut faire attention à la classe qui possède la relation et la classe mappé par la relation( `inversedBy` et `mappedBy` )

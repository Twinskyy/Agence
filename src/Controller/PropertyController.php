<?php

namespace App\Controller;

use App\Entity\Property;
use App\Repository\PropertyRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PropertyController extends AbstractController
{
    /**
     * @var PropertyRepository
     */
    private $repository;

    public function __construct(PropertyRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @Route( "/biens/{slug}-{id}" , name="property.show" , requirements={"slug" : "[a-z0-9\-]*" })
     * @param Property $property
     * @param string $slug
     * @return Response
     */
    public function show(Property $property , string $slug  ) : Response {

        if($property->getSlug() !== $slug ) {
            return $this->redirectToRoute('property.show' , [
                'id' => $property->getId(),
                'slug' => $property->getSlug()
            ],301);
        }

        // $property = $this->repository->find($id);
        return $this->render('property/show.html.twig', [
            'current_menu' => 'properties',
            'property' => $property
        ]);
    }


    /**
     * @Route("/biens" , name="property.index")
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        // $prop = $this->repository->findAllVisible();
        // $prop[0]->setSold(true);
        // $em = $this->getDoctrine()->getManager();

        // Persist : persister les données
        // $em->persist($prop);

        // flush : Mettre à jour la base de donnée
        // $em->flush();
        // dump($prop);
        $properties = $paginator->paginate(
            $this->repository->findAllVisibleQuery(),
            $request->query->getInt('page',1),
            12
        );
        return $this->render("property/index.html.twig", [
            'current_menu' => 'properties',
            'properties' => $properties
        ]);
    }
}

// Pour créer une nouvelle propriete
/* $property = new Property();
        $property->setTitle('Premier bien')
            ->setPrice("200000")
            ->setRooms('4')
            ->setBedrooms("3")
            ->setDescription("Une description")
            ->setSurface('60')
            ->setFloor('4')
            ->setHeat("1")
            ->setCity("Clermont-Ferrand")
            ->setAddress("133 Bd Lafayette")
            ->setPostalCode("63000");*/
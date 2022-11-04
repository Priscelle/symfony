<?php

namespace App\Controller;

use App\Entity\Acteurs;
use App\Entity\Films;
use App\Entity\Genres;
use App\Repository\FilmsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilmController extends AbstractController
{
    /**
    * @Route("/films", name="app_films")
    */
    public function index(): Response
    {
        $films = $this->getDoctrine()
            ->getRepository(Films::class)
            ->findAll();
        return $this->render('film/index.html.twig', [
            'controller_name' => 'FilmController',
            'films'          => $films
        ]);
    }
    /**
    * @Route("/film/{id}", name="film")
    */
    public function indiv(FilmsRepository $res ,$id): Response
    {
        $film=$res->find($id);
        return $this->render('film/film.html.twig', [
            'controller_name' => 'FilmController',
            'film'          => $film
        ]);
    }
     /**
     * @Route("/film_genre/{genre_id}", name="film_genre")
     */
    public function liste(FilmsRepository $res ,$genre_id): Response
    {
        $films=$res->findByGenre($genre_id);

        return $this->render('film/index.html.twig', [
            'controller_name' => 'FilmsController',
            'films'=>$films,
            'genre' => $this->getDoctrine()
            ->getRepository(Genres::class)
            ->find($genre_id)
        ]);
    }
    /**
    * @Route("/films/create", name="app_film_create")
    */
    public function create(Request $request): Response
    {
        if ($request->isMethod("POST")) {
            $manager = $this->getDoctrine()->getManager();

            $affiche = $request->files->get('affiche');
            $affiche_name = $affiche->getClientOriginalName();
            $affiche->move($this->getParameter('affiche_directory'),$affiche_name);
            // Insertion en BDD
            $film              = new Films;
            $film->setTitre($request->request->get('titre'))
                ->setResume($request->request->get('resume'))
                ->setAnneeDeSortie(
                    \DateTime::createFromFormat('Y-m-d', $request->request->get('annee_de_sortie'))
                )
                ->setAffiche($affiche_name);

            $genre = $this->getDoctrine()
                ->getRepository(Genres::class)
                ->find($request->request->get('genre'));
            $film->setGenre($genre);

            //Séléction d'un acteur pour le film créé
            $acteurs = $request->request->get('acteurs');
            foreach($acteurs as $acteurID) {
                $acteur = $this->getDoctrine()
                ->getRepository(Acteurs::class)
                ->find($acteurID);
                $film->addActeur($acteur);

}
            $manager->persist($film);
            $manager->flush();

            return $this->redirectToRoute('app_films');
        } else {
            $genres = $this->getDoctrine()
                ->getRepository(Genres::class)
                ->findAll();

            $acteurs = $this->getDoctrine()
                ->getRepository(Acteurs::class)
                ->findAll();
            // Affichage du formulaire
            return $this->render('film/create.html.twig', [
                'controller_name' => 'FilmController',
                'genres'        => $genres,
                'acteurs'        => $acteurs
                
            ]);
        }
    }

    /**
    * @Route("/films/{film}/edit", name="app_film_edit")
    */
    public function edit(Request $request, Films $film): Response
    {
        if ($request->isMethod("POST")) {
            $manager = $this->getDoctrine()->getManager();

            $affiche = $request->files->get('affiche');
            $affiche_name = $affiche->getClientOriginalName();
            $affiche->move($this->getParameter('affiche_directory'),$affiche_name);

            // Insertion en BDD
            $film->setTitre($request->request->get('titre'))
                ->setResume($request->request->get('resume'))
                ->setAnneeDeSortie(
                    \DateTime::createFromFormat('Y-m-d', $request->request->get('annee_de_sortie'))
                )
                ->setAffiche($affiche_name);

            $genre = $this->getDoctrine()
                ->getRepository(Genres::class)
                ->find($request->request->get('genre'));
            $film->setGenre($genre);

            $manager->flush();

            return $this->redirectToRoute('app_films');
        } else {
            $genres = $this->getDoctrine()
                ->getRepository(Genres::class)
                ->findAll();
            // Affichage du formulaire
            return $this->render('film/edit.html.twig', [
                'controller_name' => 'FilmController',
                'film'           => $film,
                'genres'        => $genres
            ]);
        }
    }

    /**
    * @Route("/films/{film}/delet", name="app_film_delete")
    */
    public function delete(Request $request, Films $film): Response
    {
        $this->getDoctrine()->getManager()->remove($film);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('app_films');
    }
}
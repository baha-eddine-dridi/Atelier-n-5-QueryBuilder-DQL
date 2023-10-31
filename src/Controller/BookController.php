<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Form\BookType;
use App\Form\SearchBookType;
use App\Repository\BookRepository;
use PHPUnit\Framework\Constraint\Count;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController

{
     /**
 *@Route("/book",name="app_book")
 */
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }
 /**
 *@Route("/AfficheBook",name="app_AfficheBook")
 */
    public function Affiche(BookRepository $repository)
    {
        //récupérer les livres publiés
        $publishedBooks = $this->getDoctrine()->getRepository(Book::class)->findBy(['published' => true]);
        //compter le nombre de livres pubbliés et non publiés
        $numPublishedBooks = count($publishedBooks);
        $numUnPublishedBooks = count($this->getDoctrine()->getRepository(Book::class)->findBy(['published' => false]));

        if ($numPublishedBooks > 0) {
            return $this->render('book/Affiche.html.twig', ['publishedBooks' => $publishedBooks, 'numPublishedBooks' => $numPublishedBooks, 'numUnPublishedBooks' => $numUnPublishedBooks]);

        } else {
            //afficher un message si aucun livre n'a été trouvé$
            return $this->render('book/no_books_found.html.twig');
        }

    }
 /**
 *@Route("/AddBook",name="app_AddBook")
 */
    public function Add(Request $request)
    {
        $book = new Book();
        $form = $this->CreateForm(BookType::class, $book);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //initialisation de l'attribut "published" a true
            //  $book->setPublished(true);
// get the accociated author from the book entity
            $author = $book->getAuthor();
            //incrementation de l'attribut "nb_books" de l'entire Author

            if ($author instanceof Author) {
                $author->setNbBooks($author->getNbBooks() + 1);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('app_AfficheBook');
        }
        return $this->render('book/Add.html.twig', ['f' => $form->createView()]);

    }

 /**
 *@Route("/editbook/{ref}",name="app_editBook")
 */
    public function edit(BookRepository $repository, $ref, Request $request)
    {
        $author = $repository->find($ref);
        $form = $this->createForm(BookType::class, $author);
        $form->add('Edit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush(); // Correction : Utilisez la méthode flush() sur l'EntityManager pour enregistrer les modifications en base de données.
            return $this->redirectToRoute("app_AfficheBook");
        }

        return $this->render('book/edit.html.twig', [
            'f' => $form->createView(),
        ]);
    }

/**
 *@Route("/deletebook/{ref}",name="app_deleteBook")
 */    
    public function delete($ref, BookRepository $repository)
    {
        $book = $repository->find($ref);


        $em = $this->getDoctrine()->getManager();
        $em->remove($book);
        $em->flush();


        return $this->redirectToRoute('app_AfficheBook');
    }
/**
 *@Route("/ShowBook/{ref}",name="app_detailBook")
 */ 
    public function showBook($ref, BookRepository $repository)
    {
        $book = $repository->find($ref);
        if (!$book) {
            return $this->redirectToRoute('app_AfficheBook');
        }

        return $this->render('book/show.html.twig', ['b' => $book]);

}

//recherche par ref(id)
 /**
     * @Route("/list/books", name="list_books")
     */
    public function listBooks(Request $request, BookRepository $bookRepository)
    {
        $searchForm = $this->createForm(SearchBookType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $ref = $searchForm->get('ref')->getData();
            $books = $bookRepository->searchBookByRef($ref);
        } else {
            $books = $bookRepository->findAll();
        }

        return $this->render('book/liste.html.twig', ['books' => $books, 'searchForm' => $searchForm->createView()]);
    }

    //Afficher la liste des livres triée par auteur
/**
     * @Route("/list/booksListByAuthors", name="list_books")
     */
    public function booksListByAuthors(BookRepository $bookRepository)
    {
        $books = $bookRepository->booksListByAuthors();

        // Utilisez $books comme vous le souhaitez, par exemple, passez-le à une vue Twig pour l'afficher.

        return $this->render('book/listeBooks.html.twig', ['books' => $books]);
    }


//Afficher la liste des livres publiés avant l’année 2023 dont l’auteur a plus de 10 livres
/**
     * @Route("/list/BooksBeforeYear", name="list_books")
     */
    public function publishedBooksBeforeYearWithAuthorBooks(BookRepository $bookRepository)
    {
        $year = 2023; 
        $minAuthorBooks = 2; 

        $books = $bookRepository->publishedBooksBeforeYearWithAuthorBooks($year, $minAuthorBooks);

        // Utilisez $books comme vous le souhaitez, par exemple, passez-le à une vue Twig pour l'afficher.

        return $this->render('book/listeavant2023.html.twig', ['books' => $books]);
    }

/**
     * @Route("/update-books-category", name="update_books_category")
     */
    public function updateBooksCategory(BookRepository $bookRepository): Response
    {
        $bookRepository->updateBooksCategoryFromScienceFictionToRomance();

        return $this->redirectToRoute('app_AfficheBook'); // Redirige vers la liste des livres ou une autre page.
    }

    /**
     * @Route("/count-books-by-category", name="count_books_by_category")
     */
    public function countBooksByCategory(BookRepository $bookRepository): Response
    {
        $category = "Romance"; // La catégorie que vous souhaitez compter
        $count = $bookRepository->countBooksByCategory($category);

        return $this->render('book/count_books_by_category.html.twig', [
            'count' => $count,
            'category' => $category,
        ]);
    }
        /**
     * @Route("/books-published-between-dates", name="books_published_between_dates")
     */
    public function booksPublishedBetweenDates(BookRepository $bookRepository): Response
    {
        $startDate = new \DateTime('2014-01-01');
        $endDate = new \DateTime('2018-12-31');
        $books = $bookRepository->findBooksPublishedBetweenDates($startDate, $endDate);

        return $this->render('book/books_published_between_dates.html.twig', [
            'books' => $books,
        ]);
    }
    











}


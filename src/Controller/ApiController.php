<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/book/create", methods={"POST"})
     */
    public function bookCreate(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        $errors = $validator->validate($book);

        if (0 === count($errors)) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return JsonResponse::fromJsonString(
                $serialized = $serializer->serialize($book, 'json'),
                JsonResponse::HTTP_CREATED
            );
        }

        return JsonResponse::fromJsonString(
            $serializer->serialize($errors, 'json'),
            JsonResponse::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/author/create", methods={"POST"})
     */
    public function authorCreate(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        $errors = $validator->validate($book);

        if (0 === count($errors)) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return JsonResponse::fromJsonString(
                $serialized = $serializer->serialize($book, 'json'),
                JsonResponse::HTTP_CREATED
            );
        }

        return JsonResponse::fromJsonString(
            $serializer->serialize($errors, 'json'),
            JsonResponse::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/book/search", methods={"GET"})
     */
    public function bookSearch(
        Request $request,
        BookRepository $bookRepository,
        SerializerInterface $serializer
    ) {
        $name = $request->query->get('name');

        $books = $bookRepository->findByName($name);

        if (!$books) {
            return $this->json(['message' => 'Книги не найдены'], Response::HTTP_NOT_FOUND);
        }

        $serializedBooks = $serializer->serialize($books, 'json',
             SerializationContext::create()
                ->enableMaxDepthChecks());

        return JsonResponse::fromJsonString($serializedBooks);
    }

    /**
     * @Route("/{_locale}/book/{id}", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function bookGet(
        BookRepository $bookRepository,
        SerializerInterface $serializer,
        $id
    ): JsonResponse {
        $book = $bookRepository->findOneBy([
            'id' => $id,
        ]);

        if (!$book) {
            return $this->json(['message' => 'Книга не найдена'], Response::HTTP_NOT_FOUND);
        }

        $serializedBook = $serializer->serialize($book, 'json',
            SerializationContext::create()->enableMaxDepthChecks()
        );

        return JsonResponse::fromJsonString($serializedBook);
    }
}

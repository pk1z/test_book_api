<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BookFixtures extends Fixture
{
    protected $ruFaker;

    protected $enFaker;


    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $this->ruFaker = Factory::create('ru_RU');
        $this->enFaker = Factory::create('en_EN');

        for ($i = 0; $i < 10000; $i++) {

            $author = new Author();

            $author
                ->setName(
                    $this->ruFaker->lastName .
                    ' ' .
                    $this->ruFaker->firstName .
                    ' | ' .
                    $this->enFaker->lastName .
                    ' ' .
                    $this->enFaker->firstName .
                    ' | '
                );


            for ($j = 3 ; $j > 0; $j--) {
                $book = new Book();
                $book->setName(
                    $this->enFaker->words(3) .
                    '|' .
                    $this->ruFaker->words(3)
                );

                $book->addAuthor($author);
                $author->addBook($book);

                $manager->persist($book);
            }

            $manager->persist($author);
        }

        $manager->flush();

    }
}

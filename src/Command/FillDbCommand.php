<?php

namespace App\Command;

use App\Entity\Author;
use App\Entity\AuthorTranslation;
use App\Entity\Book;
use App\Entity\BookTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FillDbCommand extends Command
{
    const AUTHORS_COUNT = 10000;
    const BOOKS_COUNT = 3;
    const FLUSH_STEP = 50;

    protected $ruFaker;
    protected $enFaker;

    protected static $defaultName = 'app:fill-db';
    private $em;

    public function __construct($name = null, EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Fill database with random authors and books')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $progressBar = new ProgressBar($output, self::AUTHORS_COUNT);
        $progressBar->start();

        $manager = $this->em;
        $manager->getConnection()->getConfiguration()->setSQLLogger(null);


        $this->ruFaker = Factory::create('ru_RU');
        $this->enFaker = Factory::create('en_EN');

        for ($i = 0; $i < self::AUTHORS_COUNT; $i++) {
            $progressBar->advance();


            $author = new Author();

            $authorRuTranslate = $author->translate('ru');
            /** @var AuthorTranslation $authorRuTranslate */
            $authorRuTranslate->setName(
                $this->ruFaker->lastName .
                ' ' .
                $this->ruFaker->firstName
            );

            $authorEnTranslate = $author->translate('en');
            /** @var AuthorTranslation $authorEnTranslate */
            $authorEnTranslate->setName(
                $this->enFaker->lastName .
                ' ' .
                $this->enFaker->firstName
            );

            $author->mergeNewTranslations();

            for ($j = self::BOOKS_COUNT ; $j >= 0; $j--) {
                $book = new Book();

                /** @var BookTranslation $bookRuTranslation */
                $bookRuTranslation = $book->translate('ru');
                $bookRuTranslation->setName(
                    $this->ruFaker->realText(50)
                );

                /** @var BookTranslation $bookEnTranslation */
                $bookEnTranslation = $book->translate('en');
                $bookEnTranslation->setName(
                    $this->enFaker->realText(50)
                );
                $book->mergeNewTranslations();

                $book->addAuthor($author);
                $author->addBook($book);


                $manager->persist($book);
            }

            $manager->persist($author);

            if ($i % self::FLUSH_STEP == 0) {
                $manager->flush();
                $manager->clear();
            }

        }

        $manager->flush();

        return Command::SUCCESS;
    }
}

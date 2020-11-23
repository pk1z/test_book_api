<?php

namespace App\Command;

use App\Entity\Author;
use App\Entity\AuthorTranslation;
use App\Entity\Book;
use App\Entity\BookTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ParseLitMirCommand extends Command
{
    const AUTHOR_LINK = 'https://www.litmir.me/a/?id=';
    const AUTHOR_NAME_XPATH = "(//div[@itemprop='name']/span)[1]";
    const BOOKS_XPATH = "//tr[contains(@class, 'lt127')]";

    protected static $defaultName = 'app:parse-litmir';
    private $manager;
    private $client;

    /**
     * ParseLivelibCommand constructor.
     */
    public function __construct(EntityManagerInterface $manager, HttpClientInterface $client)
    {
        $this->client = $client;
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startId = 100;
        $count = 10000;

        $progressBar = new ProgressBar($output, $count);
        $progressBar->start();

        for ($id = $startId; $id < $count; ++$id) {
            $progressBar->advance();

            try {
                $response = $this->client->request('GET', self::AUTHOR_LINK.$id);

                $content = $response->getContent();
            } catch (TransportExceptionInterface | ClientException | ClientExceptionInterface
            | RedirectionExceptionInterface | ServerExceptionInterface$e) {
                $output->writeln('Can\'t get page with id '.$id);

                continue;
            }
            $crawler = new Crawler($content);

            $authorNameRu = $crawler->filterXPath(self::AUTHOR_NAME_XPATH)->text();

            /** @var \Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface $category */
            $author = new Author();

            /** @var AuthorTranslation $authorTranslate */
            $authorTranslate = $author->translate('ru');
            $authorTranslate->setName($authorNameRu);

            $author->mergeNewTranslations();
            $this->manager->persist($author);

            $booksCrawler = $crawler;

            $thisPageBooks = $booksCrawler->filterXPath(self::BOOKS_XPATH);
            /** @var \DOMElement $thisPageBook */
            foreach ($thisPageBooks as $thisPageBook) {
                $book = new Book();

                /** @var BookTranslation $bookTranslation */
                $bookTranslation = $book->translate('ru');
                $bookTranslation->setName($thisPageBook->getAttribute('bookname'));

                $book->mergeNewTranslations();

                $book->addAuthor($author);
                $author->addBook($book);

                $this->manager->persist($book);
                $this->manager->persist($author);
                $this->manager->flush();
            }
        }

        $this->manager->flush();

        $progressBar->finish();

        return Command::SUCCESS;
    }
}

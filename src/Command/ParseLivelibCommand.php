<?php

namespace App\Command;

use App\Entity\Author;
use App\Entity\AuthorTranslation;
use App\Entity\Book;
use App\Entity\BookTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ParseLivelibCommand extends Command
{
    protected static $defaultName = 'app:parse-livelib';
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

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startId = 2;
        $count = 20;

        for ($id = $startId; $id < $count; ++$id) {
            try {
                $response = $this->client->request('GET', 'https://www.livelib.ru/author/'.$id);

                $content = $response->getContent();
            } catch (TransportExceptionInterface | ClientException $e) {
                $output->writeln('Can\'t get page with id '.$id);
                continue;
            }

            $crawler = new Crawler($content);

            $authorNameRu = $crawler->filterXPath("//span[@class='header-profile-login']")->text();
            $authorNameEn = $crawler->filterXPath("//span[@class='header-profile-status']")->text();

            /** @var \Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface $category */
            $author = new Author();

            /** @var AuthorTranslation $authorEnTranslation */
            $authorEnTranslation = $author->translate('en');
            $authorEnTranslation->setName($authorNameEn);

            /** @var AuthorTranslation $authorRuTranslation */
            $authorRuTranslation = $author->translate('ru');
            $authorRuTranslation->setName($authorNameRu);

            $author->mergeNewTranslations();

            $this->manager->persist($author);

            //get author books
            try {
                $response = $this->client->request('GET', 'https://www.livelib.ru/author/'.$id.'/latest');

                $booksHtml = $response->getContent();
            } catch (TransportExceptionInterface | ClientException $e) {
                $output->writeln('Can\'t get page with id '.$id);
                continue;
            }

            $booksCrawler = new Crawler($booksHtml);

            $thisPageBooks = $booksCrawler->filterXPath("//a[contains(@class, 'brow-book-name')]");
            /** @var \DOMElement $thisPageBook */
            foreach ($thisPageBooks as $thisPageBook) {
                $book = new Book();

                /** @var BookTranslation $bookTranslation */
                $bookTranslation = $book->translate('ru');
                $bookTranslation->setName($thisPageBook->textContent);

                $book->mergeNewTranslations();

                $book->addAuthor($author);
                $author->addBook($book);

                $this->manager->persist($book);
                $this->manager->persist($author);

                $this->manager->flush();
            }

            sleep(3);
        }

        $this->manager->flush();

        return Command::SUCCESS;
    }
}

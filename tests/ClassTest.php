<?php

namespace App\Tests;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use PHPUnit\Framework\TestCase;

class ClassTest extends TestCase
{
    public function testBookClass()
    {
        $book = new \App\Entity\Book();
        $this->assertInstanceOf(TranslatableInterface::class, $book);

        $bookTranslation = new \App\Entity\BookTranslation();
        $this->assertInstanceOf(TranslationInterface::class, $bookTranslation);
    }

    public function testAuthorClass()
    {
        $author = new \App\Entity\Author();
        $this->assertInstanceOf(TranslatableInterface::class, $author);

        $authorTranslation = new \App\Entity\AuthorTranslation();
        $this->assertInstanceOf(TranslationInterface::class, $authorTranslation);
    }
}

Тестовое задание "rest api" [подробнее](qualificationTest.md)
Установка:

```bash
git clone https://github.com/pk1z/test_book_api.git
cd test_book_api
cp .env.dist .env
docker-compose up -d
docker-compose exec php php composer.phar install // Установка зависимостей композера
docker-compose exec php php bin/console d:d:c // Создание пустой базы данных
docker-compose exec php php bin/console d:s:u --force // Создание необходимых таблиц в БД
```

- загруза автогерерированных данных в базу
```bash
docker-compose exec php php bin/console app:fill-db
```

- запуск тестов
```bash
docker-compose exec php ./vendor/bin/simple-phpunit tests/
```


- парсинг реальных данных с сайта litmir

```bash
docker-compose exec php php bin/console app:parse-litmir 
```

- парсинг реальных данных с сайта livelib
```bash
docker-compose exec php php bin/console app:parse-livelib
```


##Запросы к API

```bash
curl --location --request GET 'http://127.0.0.1:8000/ru/book/5'
```

```bash
curl --location --request POST 'http://127.0.0.1:8000/book/create' \
--header 'Content-Type: text/plain' \
--data-raw '{
    "translations": [
        {
            "name": "мастер и маргарита",
            "locale": "ru"
        },
        {
            "name": "master and margarita",
            "locale": "en"
        }  
    ]
}'
```

```bash
curl --location --request GET 'http://127.0.0.1:8000/book/search?name=%D0%A1%D0%BB%D0%B5%D0%BF%D0%BE%D0%B9'
```

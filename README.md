Тестовое задание "rest api" [подробнее](qualificationTest.md)
Установка:

```bash
git clone https://github.com/pk1z/test_book_api.git
cd test_book_api
cp .env.dist .env
docker-compose up -d
```

- загруза автогерерированных данных в базу
```bash
docker-compose exec fpm php bin/console doctrine:fixtures:load
```

- запуск тестов
```bash
docker-compose exec php ./vendor/bin/simple-phpunit tests/
```

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


- doctrine:fixtures:load  - загрузка в базу 10000 автоматически сгенерированных авторов и 30000 автоматически сгенерированных книг
- app:parse-litmir  - парсинг данных из сайта litmir  
- app:parse-livelib - парсинг данных из сайта livelib 

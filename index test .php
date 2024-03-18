<?php

/**
    CREATE TABLE source (
        id int NOT NULL auto_increment,
        name varchar(255),
        PRIMARY KEY(id)
    );
    CREATE TABLE article (
        id int NOT NULL auto_increment,
        source_id int NOT NULL,
        name varchar(255),
        content BLOB,
        PRIMARY KEY(id)
    );

    INSERT INTO source VALUES (1, 'src-1');
    INSERT INTO source VALUES (2, 'src-2');

    INSERT INTO article VALUES (1, 1, 'Politique', 'Lorem ipsum dolor sit amet 1', '2024-03-11 00:00:00', 'author1');
    INSERT INTO article VALUES (2, 2, 'Economie', 'Lorem ipsum dolor sit amet 2', '2024-03-12 00:00:00', 'author2');
    INSERT INTO article VALUES (3, 2, 'Ecologie', 'Lorem ipsum dolor sit amet 3', '2024-03-17 00:00:00', 'author3');
    INSERT INTO article VALUES (4, 1, 'Jeu Video', 'Lorem ipsum dolor sit amet 4', '2024-03-19 00:00:00', 'author4');
*/

class ArticleAgregator implements IteratorAggregate
{
    private $articles = [];

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->articles);
    }

    public function appendDatabase($host, $username, $password, $database)
    {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $stmt = $pdo->query("
            SELECT t1.id AS article_id, t1.name AS article_name, t1.content, t2.name AS source_name
            FROM article t1 
            INNER JOIN source t2 
            ON t1.source_id = t2.id"
        );

        while ($row = $stmt->fetch()) {
            $this->articles[] = (object) [
                'name' => $row['article_name'],
                'sourceName' => $row['source_name'],
                'content' => $row['content']
            ];
        }
    }

    public function appendRss($sourceName, $feedUrl)
    {
        $rss = simplexml_load_file($feedUrl);
        foreach ($rss->channel->item as $item) {
            $this->articles[] = (object) [
                'name' => (string) $item->title,
                'sourceName' => $sourceName,
                'content' => (string) $item->description
            ];
        }
    }

    public function appendRestApi($url)
    {
        // Récupération des articles depuis une API REST
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        foreach ($data['articles'] as $article) {
            $this->articles[] = (object) [
                'name' => $article['title'],
                'sourceName' => 'API REST',
                'content' => $article['content']
            ];
        }
    }

    // the file must be json
    public function appendFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("file not found: $filePath");
        }

        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            throw new Exception("error contents : $filePath");
        }

        $articlesData = json_decode($fileContents, true);
        if ($articlesData === null) {
            throw new Exception("is not json valid file : $filePath");
        }

        foreach ($articlesData as $articleData) {
            $this->articles[] = (object) $articleData;
        }
    }


}

$a = new ArticleAgregator();

// $a->appendDatabase('localhost:3306', 'root', '', 'florajet_test');
$a->appendRss('Le Monde',    'http://www.lemonde.fr/rss/une.xml');
// $a->appendFile('example.json');
// $a->appendRestApi("https://127.0.0.1:8000/api/article");

foreach ($a as $article) {
    echo sprintf('<h2>%s</h2><em>%s</em><p>%s</p>',
        $article->name,
        $article->sourceName,
        $article->content
    );
}
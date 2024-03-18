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

    INSERT INTO article VALUES (1, 1, 'Article 1', 'Lorem ipsum dolor sit amet 1');
    INSERT INTO article VALUES (2, 2, 'Article 2', 'Lorem ipsum dolor sit amet 2');
    INSERT INTO article VALUES (3, 2, 'Article 3', 'Lorem ipsum dolor sit amet 3');
    INSERT INTO article VALUES (4, 1, 'Article 4', 'Lorem ipsum dolor sit amet 4');
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
        $stmt = $pdo->query("SELECT * FROM article");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->articles[] = (object) [
                'name' => $row['name'],
                'sourceName' => $row,
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

}

$a = new ArticleAgregator();

/**
 * Récupère les articles de la base de données, avec leur source.
 * host, username, password, database name
 */
$a->appendDatabase('localhost:3306', 'root', '', 'florajet_test');

/**
 * Récupère les articles d'un flux rss donné
 * source name, feed url
 */

//$a->appendRss('Le Monde',    'http://www.lemonde.fr/rss/une.xml');

foreach ($a as $article) {
//    var_dump($article);
    echo sprintf('<h2>%s</h2><em>%s</em><p>%s</p>',
        $article->name,
        $article->sourceName,
        $article->content
    );
}
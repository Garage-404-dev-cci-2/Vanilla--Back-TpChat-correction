<?php
require_once ('connexion.php');
include 'src/RandomColor.php';
use \Colors\RandomColor;
?>

<?php
$task = 'list';
if(array_key_exists('task',$_GET)) { 
    $task = $_GET['task']; 
}

if ($task == "write") { 
    postMessage();
} else {
    getMessages();
} 

function getMessages() {
    global $bdd;
    $resultats = $bdd -> query ("   SELECT * FROM messages 
                                    INNER JOIN author 
                                    ON author.id = messages.author_id 
                                    ORDER BY created_at 
                                    DESC LIMIT 20");
    $messages = $resultats -> fetchAll();
    echo json_encode($messages);
}

function postMessage() {
    global $bdd; 
    if(!array_key_exists('author', $_POST) || !array_key_exists('content', $_POST)) {
        return;
    }

    $author = $_POST['author'];
    
    $resultats = $bdd -> prepare ("SELECT * FROM author WHERE author.name = :author ");
    $resultats -> execute([
        "author" => $author
    ]);
    $authorFromBdd = $resultats -> fetch();

    if(!$authorFromBdd){
        $query = $bdd -> prepare("  INSERT INTO author
                                    SET  author.name = :author, author.color = :color");
        $query -> execute([ 
            "author" => $author,
            "color" => RandomColor::one()
        ]);
    }
    
    $resultats = $bdd -> prepare ("SELECT * FROM author WHERE author.name = :author ");
    $resultats -> execute([
        "author" => $author
    ]);
    $authorFromBdd = $resultats -> fetch();

    $content = $_POST['content'];
    $requette = $bdd -> prepare("INSERT INTO messages SET author_id = :authorId, content = :content, created_at = NOW()");
    $requette -> execute([
        "authorId" => $authorFromBdd['id'],
        "content" => $content
    ]);
}

?>




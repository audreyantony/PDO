<?php
// Load all articles with author
function articlesLoadAll($cdb){
    $req = "SELECT * FROM articles a 
	INNER JOIN users u 
		ON a.users_idusers = u.idusers
ORDER BY a.thedate DESC;";
    $recup =  $cdb->query($req);
    // si au moins 1 résultat
    if($recup->rowCount()){
        // on utilise le fetch all car il peut y avoir plus d'un résultat
        return $recup->fetchAll(PDO::FETCH_ASSOC);
    }
    // no result
    return false;
}

// Load all articles with author but with 300 caracters from "texte"
function articlesLoadAllResume($cdb){
    $req = "SELECT a.idarticles, a.titre, LEFT(a.texte,300) AS texte, a.thedate, u.idusers, u.thename 
FROM articles a 
	INNER JOIN users u 
		ON a.users_idusers = u.idusers
ORDER BY a.thedate DESC;";
    $recup = $cdb->query($req);
    // si au moins 1 résultat
    if($recup->rowCount()){
        // on utilise le fetch all car il peut y avoir plus d'un résultat
        return $recup->fetchAll(PDO::FETCH_ASSOC);
    }
    // no result
    return false;
}

// Count number of articles
function countAllArticles($c){
    // le count renvoie une ligne de résultat avec le nombre d'articles, utiliser la clef primaire permet d'éviter qu'il compte réellement le nombre d'articles: c'est un résultat se trouvant en début du code de la table (dans l'index)
    $req = "SELECT COUNT(idarticles) AS nb
FROM articles";
    $recup = $c->query($req);
    $out = $recup->fetch(PDO::FETCH_ASSOC);
    return $out["nb"];
}

// Load all articles with author but with 300 caracters from "texte" with pagination LIMIT
function articlesLoadResumePagination($cdb,$begin,$nbperpage=10){
    $begin = (int) $begin;
    $nbperpage = (int) $nbperpage;
    $req = "SELECT a.idarticles, a.titre, LEFT(a.texte,300) AS texte, a.thedate, u.idusers, u.thename 
FROM articles a 
	INNER JOIN users u 
		ON a.users_idusers = u.idusers
ORDER BY a.thedate DESC 
LIMIT $begin, $nbperpage;";
    $recup = $cdb->query($req);
    // si au moins 1 résultat
    if($recup->rowCount()){
        // on utilise le fetch all car il peut y avoir plus d'un résultat
        return $recup->fetchAll(PDO::FETCH_ASSOC);
    }
    // no result
    return false;
}

// LOAD full article with ID
function articleLoadFull($connect,$id){
    $id = (int) $id;
    $req = "SELECT * FROM articles a 
	INNER JOIN users u 
		ON a.users_idusers = u.idusers
    WHERE a.idarticles=$id";
    $recup = $connect->query($req);
    // si on a 1 résultat
    if($recup->rowCount()){
        // on utilise le fetch all car il peut y avoir plus d'un résultat
        return $recup->fetch(PDO::FETCH_ASSOC);
    }
    // no result
    return false;
}

// insertion d'un nouvel article
function insertArticle($c,$title,$text,$id){

    $sql="INSERT INTO articles (titre,texte,users_idusers) VALUES (?,?,?);";

    $prepareInsert = $c->prepare($sql);

    $prepareInsert->bindValue(1,$title,PDO::PARAM_STR);
    $prepareInsert->bindValue(2,$text,PDO::PARAM_STR);
    $prepareInsert->bindValue(3,$id,PDO::PARAM_INT);
    $insert = $prepareInsert->execute();

    if($insert){
        return true;
    }else{
        return false;
    }
}

// suppression d'un article via son ID

function deleteArticle($connect,$id){
    $id = (int) $id;
    $sql="DELETE FROM articles WHERE idarticles= ?";

    $prepareDelete = $connect->prepare($sql);
    $prepareDelete->bindValue(1,$id,PDO::PARAM_INT);

    $delete = $prepareDelete->execute();

    if($delete){
        return true;
    }else{
        return false;
    }
}

/*
 * mise à jour de l'article
 * $db -> connexion mysqli
 * $datas -> array de $_POST
 * $id -> variable GET idarticles
 */

function updateArticle($db,$datas,$id){
    // traîtement des variables
    // $_GET
    $id = (int) $id;
    // $_POST => on pourrait utiliser extract(), plus rapide mais dangereux et non sécurisé sans mettre les mêmes lignes que celles ci-dessous
    $idarticles = (int) $datas['idarticles'];
    $titre = htmlspecialchars(strip_tags(trim($datas['titre'])),ENT_QUOTES);
    // exception pour le strip_tags qui va accepter les balises html entre allowable_tags
    $texte= htmlspecialchars(strip_tags(trim($datas['texte']),'<p><br><a><img><h4><h5><b><strong><i><ul><li>'),ENT_QUOTES);
    $thedate = htmlspecialchars(strip_tags(trim($datas['thedate'])),ENT_QUOTES);

    // on vérifie si la date valide existe dans la chaîne, si oui elle est mise dans $tab et séparée du reste
    $tab = preg_grep("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/",[$thedate]);
    // si on ne la trouve pas, on met la date du jour
    if(empty($tab)) $thedate = date("Y-m-d H:i:s");


    $users_idusers = (int) $datas['users_idusers'];

    // quelqu'un essaie de modifier un autre article que celui affiché
    if($id!=$idarticles) return "Inutile d'essayer de supprimer un article de quelqu'un d'autre";

    if(empty($id)||empty($idarticles)||empty($titre)||
        empty($texte)||empty($thedate)||empty($users_idusers)) return "Vos champs ne sont pas correctement remplis";

    $sql ="UPDATE articles SET titre = ?, texte = ? ,thedate= ? , users_idusers= ? WHERE idarticles = ?";

    $prepareUpdate = $db->prepare($sql);

    $prepareUpdate->bindValue( 1, $titre,PDO::PARAM_STR);
    $prepareUpdate->bindValue( 2, $texte,PDO::PARAM_STR);
    $prepareUpdate->bindValue( 3, $thedate,PDO::PARAM_STR);
    $prepareUpdate->bindValue( 4, $users_idusers,PDO::PARAM_INT);
    $prepareUpdate->bindValue( 5, $idarticles,PDO::PARAM_STR);

    $update = $prepareUpdate->execute();

    if ($update){
        return true;
    }else{
        return "Erreur inconnue lors de la modification, Veuillez recommencer";
    }
}